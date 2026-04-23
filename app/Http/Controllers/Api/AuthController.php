<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Mail,Hash,File,DB,Helper,Auth;
use App\Models\PhoneOtp;
use App\Models\AppUser;
use App\Models\Category;
use App\Models\Order;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;



class AuthController extends Controller
{

    public function sendPhoneOtp(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'phone' => 'required|digits_between:4,13',
            'country_code' => 'required|max:5'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' =>  $validator->errors()->first(),
            ], 200);
        }

        // For testing: fixed OTP (change to rand(1000,9999) in production)
        $code = rand(1000,9999);
        //$code = '1234';

        $date = date('Y-m-d H:i:s');
        $currentDate = strtotime($date);
        $futureDate = $currentDate + (60 * 120);

        $phone_user = PhoneOtp::where('country_code', $data['country_code'])
                            ->where('phone', $data['phone'])
                            ->first();

        if (!$phone_user) {
            $phone_user = new PhoneOtp();
        }

        $phone_user->phone = $data['phone'];
        $phone_user->country_code = $data['country_code'];
        $phone_user->otp = $code;
        $phone_user->otp_expire_time = $futureDate;
        $phone_user->save();

        return response()->json([
            'status' => true,
            'message' => 'A one-time password has been sent to your phone, please check.',
            'otp' => $code,  // ✅ OTP also returned in response
        ], 200);
    }

    public function verifyPhoneOtp(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'phone' => 'required|digits_between:4,13',
            'country_code' => "required|max:5",
            'otp' => "required|max:4",
            'device_token' => 'nullable|string',
            'device_type' => 'nullable|in:android,ios',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' =>  $validator->errors()->first(),
            ],200);
        }
        
        $phone_user = PhoneOtp::where('country_code',$data['country_code'])
                            ->where('phone',$data['phone'])
                            ->first();

        if(!$phone_user){
            return response()->json([
                'status' => false,
                'message'=>'Invalid phone number. Please check and try again'
            ],200);
        }

        $currentTime = strtotime(now());

        if($phone_user->otp != $data['otp']){
            return response()->json([
                'status' => false,
                'message' =>  'Invalid verification code. Please try again',
            ],200);
        }

        if($currentTime > $phone_user->otp_expire_time){
            return response()->json([
                'status' => false,
                'message' =>  'Verification code is expired.',
            ],200);
        }

        // OTP verified → delete it
        PhoneOtp::where('country_code',$data['country_code'])
                ->where('phone',$data['phone'])
                ->delete();

        // Find user
        $user = User::where('phone',$data['phone'])
                    ->where('country_code',$data['country_code'])
                    ->where('role','user')
                    ->first();

        if(!$user){
            return response()->json([
                'status' => false,
                'message' => 'Phone number not exists',
            ],200);
        }

        if($user->status == 'inactive'){
            return response()->json([
                'status' => false,
                'message' => 'Your account is not activated yet.',
            ],200);
        }

        // Prepare JWT login
        $credentials = [
            'phone' => $user->phone,
            'country_code' => $user->country_code,
            'password' => $user->full_name, // same logic you used in login()
        ];

        try {
            if(!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'status' => false,
                    'message'=>'Something went wrong. Please try again'
                ],200);
            }


            if (!empty($data['device_token'])) {
                $user->device_token = $data['device_token'];
            }

            if (!empty($data['device_type'])) {
                $user->device_type = $data['device_type'];
            }

            $user->save();

            return response()->json([
                'status' => true,
                'message'=>'Verified & logged in successfully.',
                'access_token' => $token,
                'token_type' => 'bearer',
                'user' => $this->getUserDetail($user->id),
            ],200);

        } catch (JWTException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ],200);
        }
    }

    public function register(Request $request)
    {
        /**
         * ---------------------------------------------------------
         * Step 1: Validate Request Data
         * ---------------------------------------------------------
         */
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:100',
            'email'        => 'required|email|unique:users,email',
            'phone'        => 'required|numeric|digits_between:4,12|unique:users,phone',
            'location'     => 'required|string|max:150',
            'country_code' => 'required|string|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 200);
        }

        /**
         * ---------------------------------------------------------
         * Step 2: Use DB Transaction (Safe Insert)
         * ---------------------------------------------------------
         */
        DB::beginTransaction();

        try {

            /**
             * ---------------------------------------------------------
             * Step 3: Create New User
             * ---------------------------------------------------------
             */
            $appUser = AppUser::create([
                'full_name' => $request->name,
                'email'     => $request->email,
                'phone'     => $request->phone,
                'city'      => $request->location,
                'country_code' => $request->country_code
            ]);

            /**
             * ---------------------------------------------------------
             * Step 4: Generate OTP
             * ---------------------------------------------------------
             */
            $otp = rand(1000, 9999);

            /**
             * ---------------------------------------------------------
             * Step 5: Set OTP Expiry (2 Hours)
             * Laravel Way using Carbon
             * ---------------------------------------------------------
             */
            $expireTime = Carbon::now()->addHours(2);

            /**
             * ---------------------------------------------------------
             * Step 6: Update or Create OTP Record
             * ---------------------------------------------------------
             */
            PhoneOtp::updateOrCreate(
                [
                    'phone'        => $request->phone,
                    'country_code' => $request->country_code,
                ],
                [
                    'otp'              => $otp,
                    'otp_expire_time'  => $expireTime,
                ]
            );

            /**
             * ---------------------------------------------------------
             * Step 7: Commit Transaction
             * ---------------------------------------------------------
             */
            DB::commit();

            /**
             * ---------------------------------------------------------
             * Step 8: Return Response
             * NOTE: Do NOT send OTP in production
             * ---------------------------------------------------------
             */
            return response()->json([
                'status'  => true,
                'message' => 'OTP sent successfully. Please verify to complete registration.',
                'otp'     => $otp // ❌ Remove this in production
            ], 200);

        } catch (\Exception $e) {

            /**
             * ---------------------------------------------------------
             * Step 9: Rollback if Error
             * ---------------------------------------------------------
             */
            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong!',
                'error'   => $e->getMessage(), // remove in production
            ], 200);
        }
    }

    public function verifyRegister(Request $request){
        $data = $request->all();
        $validator = Validator::make($data, [
            'phone' => "required|numeric|exists:app_users,phone|unique:users,phone",
            'country_code' => "required|numeric|exists:app_users,country_code",
            'otp' => "required|max:4",
            'device_token' => 'nullable|string',
            'device_type' => 'nullable|in:android,ios',
        ]);
        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' =>  $validator->errors()->first(),
            ],200);
        }

        
        $date = date('Y-m-d H:i:s');
        $currentTime = strtotime($date);
        $phone_user = PhoneOtp::where('phone',$data['phone'])->where('country_code',$data['country_code'])->where('otp',$data['otp'])->first();
        $app_user = AppUser::where('phone',$data['phone'])->where('country_code',$data['country_code'])->first();
        
        if(!$phone_user){
            return response()->json([
                'status' => false,
                'message'=>'Please enter valid otp.'
            ],200);
            
        }
        if($currentTime > $phone_user->otp_expire_time){
            return response()->json([
                'status' => true,
                'message' =>  'Otp time is expired.',
            ],200);
        }

        try{
            DB::beginTransaction();
            $user = new User();
            $user->first_name = $app_user->first_name;
            $user->last_name = $app_user->last_name;
            $user->full_name = $app_user->full_name;
            $user->email = $app_user->email;
            $user->slug = $app_user->slug;
            $user->phone = $app_user->phone;
            $user->password = bcrypt($app_user->full_name);
            $user->address = $app_user->address;
            $user->area = $app_user->area ?? '';
            $user->city = $app_user->city ?? '';
            $user->state = $app_user->state ?? '';
            $user->country = $app_user->country ?? '';
            $user->country_code = $app_user->country_code;
            $user->zipcode = $app_user->zipcode ?? '';
            $user->latitude = $app_user->latitude ?? '';
            $user->longitude = $app_user->longitude ?? '';
            $user->device_type = $data['device_type'] ?? $app_user->device_type ?? '';
            $user->device_token = $data['device_token'] ?? $app_user->device_token ?? '';
            $user->bio = $app_user->bio ?? '';
            $user->phone_verified_at = $date;
            $user->avatar = $app_user->avatar;
            $user->role = 'user';
            $user->status = 'active';
            $user->save();
            DB::commit();
            

            // Mail::to($user->email)->send(new UserRegisterVerifyMail($user));
            //============ Make User Login ==========//
            $input['phone'] = $app_user->phone;
            $input['country_code'] = $app_user->country_code;
            $input['password'] = $app_user->full_name;
            $token = JWTAuth::attempt($input);
            $app_user->delete();
            
            return response()->json([
                'status' => true,
                'message'=>'Account created successfully!',
                'access_token' => $token,
                'token_type' => 'bearer',
                'user' => $this->getUserDetail($user->id),
            ],200);
            
        }catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ],200);
        }

    }

    public function getUser() 
    {
        try{
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user) {
                return response()->json([
                    'status' => false,
                    'message'=>'User not found.'
                ],200);
            }
            else{
                return response()->json([
                    'status' => true,
                    'message' => 'User found successfully.',
                    'user' => $this->getUserDetail($user->id),
                ],200);
            } 
        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ],200);
        }
    }
    
    public function updateProfile(Request $request)
    {
        $id = auth()->user()->id;

        $validator = Validator::make($request->all(), [
            'name'     => 'sometimes|string',
            'email'         => 'sometimes|email|unique:users,email,' . $id,
            'phone'         => 'sometimes|numeric|digits_between:4,12|unique:users,phone,' . $id,
            'location'       => 'sometimes|string',
            'country_code'  => 'sometimes|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 200);
        }

        try {
            $user = User::find($id);

            if ($request->filled('name')) {
                $user->full_name = $request->name;
                $user->password = bcrypt($request->name);
            }

            if ($request->filled('email')) {
                $user->email = $request->email;
            }

            if ($request->filled('phone')) {
                $user->phone = $request->phone;
            }

            if ($request->filled('location')) {
                $user->city = $request->location;
            }

            if ($request->filled('country_code')) {
                $user->country_code = $request->country_code;
            }

            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'Profile updated successfully!',
                'user' => $this->getUserDetail($user->id),
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 200);
        }
    }

    
    public function getUserDetail($user_id){
        $user = User::where('id',$user_id)->first();
        return $user;
    }

    public function logout() {
        JWTAuth::parseToken()->invalidate(true);
        return response()->json(array(
            'status' => true,
            'message' => 'User successfully signed out.'
        ),200);
    }

    public function deleteAccount()
    {
        try{
            DB::beginTransaction();
            $user = Auth::user();
            $user->email = uniqid().'_delete_'.$user->email;
            $user->phone = uniqid().'_delete_'.$user->phone;
            $user->status = 'inactive';
            $user->save();
            DB::commit();
            Auth::logout();
            return response()->json(array(
                'status' => true,
                'message' => 'Account deleted successfully.'
            ),200);
            
        }
        catch(Exception $e){
            DB::rollback();
            return response()->json(array(
                'status' => false,
                'message' => $e->getMessage()
            ),200);
        }   


    }

    public function getCategory()
    {
        try {
            // Authenticate user via JWT
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found.'
                ], 200);
            }

            // Fetch categories where status = active with pagination (10 per page)
            $categories = Category::where('status', 'active')
                ->orderBy('id', 'desc')
                ->paginate(10);

            return response()->json([
                'status' => true,
                'message' => 'Category found successfully.',
                'data' => $categories
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 200);
        }
    }


    public function getCategoryDetail(Request $request)
    {
        try {
            /**
             * ---------------------------------------------------------
             * Authenticate User via JWT Token
             * ---------------------------------------------------------
             */
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found.'
                ], 200);
            }

            /**
             * ---------------------------------------------------------
             * Validate Incoming Request
             * - category: required (comma-separated IDs like "1,2,3")
             * ---------------------------------------------------------
             */
            $request->validate([
                'category' => 'required|string'
            ]);

            /**
             * ---------------------------------------------------------
             * Convert Comma-Separated IDs to Clean Integer Array
             * Steps:
             * 1. explode() → split string into array
             * 2. trim() → remove extra spaces
             * 3. cast to int → ensure numeric values
             * 4. filter() → remove invalid/empty values
             * ---------------------------------------------------------
             */
            $ids = collect(explode(',', $request->category))
                ->map(fn($id) => (int) trim($id))
                ->filter()
                ->values();

            /**
             * ---------------------------------------------------------
             * Fetch Categories from Database
             * - Only required columns are selected
             * - Sorted by latest ID (descending)
             * ---------------------------------------------------------
             */
            $categories = Category::whereIn('id', $ids)
                ->latest('id')
                ->get(['id', 'name', 'youtube_url', 'measurements']);

            /**
             * ---------------------------------------------------------
             * Prepare Category Response Data
             * - Only return required fields
             * ---------------------------------------------------------
             */
            $categoryData = $categories->map->only([
                'id',
                'name',
                'youtube_url'
            ]);

            /**
             * ---------------------------------------------------------
             * Combine Measurements from All Categories
             * Steps:
             * 1. pluck() → get all measurement strings
             * 2. filter() → remove null/empty values
             * 3. flatMap() → split comma-separated values into array
             * 4. trim() → clean spaces
             * 5. unique() → remove duplicates
             * 6. values() → reset array index
             * ---------------------------------------------------------
             */
            $measurements = $categories
                ->pluck('measurements')
                ->filter()
                ->flatMap(fn($m) => explode(',', $m))
                ->map(fn($m) => trim($m))
                ->filter()
                ->unique()
                ->values();

            /**
             * ---------------------------------------------------------
             * Final JSON Response
             * ---------------------------------------------------------
             */
            return response()->json([
                'status' => true,
                'message' => 'Categories found successfully.',
                'data' => [
                    'categories' => $categoryData,
                    'measurements' => $measurements
                ]
            ], 200);

        } catch (\Exception $e) {

            /**
             * ---------------------------------------------------------
             * Exception Handling
             * ---------------------------------------------------------
             */
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 200);
        }
    }

    public function orderCreate(Request $request)
    {
        try {
            /**
             * ---------------------------------------------------------
             * Authenticate User
             * ---------------------------------------------------------
             */
            $user = JWTAuth::parseToken()->authenticate();

            //dd($user);

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found.'
                ], 200);
            }

            /**
             * ---------------------------------------------------------
             * Generate Order Number
             * ---------------------------------------------------------
             */
            $lastOrder = Order::latest()->first();

            $orderNo = 'ORD-00001';
            if ($lastOrder) {
                $number = (int) str_replace('ORD-', '', $lastOrder->order_no);
                $orderNo = 'ORD-' . str_pad($number + 1, 5, '0', STR_PAD_LEFT);
            }

            /**
             * ---------------------------------------------------------
             * Upload Images (public/uploads/order)
             * ---------------------------------------------------------
             */
            $uploadPath = public_path('uploads/order');

            // Create folder if not exists
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            // FRONT PHOTO
            $frontPhoto = null;
            if ($request->hasFile('front_photo')) {
                $file = $request->file('front_photo');
                $name = time() . '_front_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($uploadPath, $name);

                $frontPhoto = url('uploads/order/' . $name);
            }

            // SIDE PHOTO
            $sidePhoto = null;
            if ($request->hasFile('side_photo')) {
                $file = $request->file('side_photo');
                $name = time() . '_side_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($uploadPath, $name);

                $sidePhoto = url('uploads/order/' . $name);
            }

            // BACK PHOTO
            $backPhoto = null;
            if ($request->hasFile('back_photo')) {
                $file = $request->file('back_photo');
                $name = time() . '_back_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($uploadPath, $name);

                $backPhoto = url('uploads/order/' . $name);
            }

            /**
             * ---------------------------------------------------------
             * Convert JSON Fields
             * ---------------------------------------------------------
             */
            $measurementJson = $request->measurement_json ;
            
            $additionalRequirement = $request->additional_requirement;

            /**
             * ---------------------------------------------------------
             * Category Handling
             * ---------------------------------------------------------
             */
            $categoryIds = $request->category_id;

            $commaSeparated = implode(',', $categoryIds);

            /**
             * ---------------------------------------------------------
             * Create Order
             * ---------------------------------------------------------
             */
            $order = Order::create([
                'order_no' => $orderNo,
                'user_id' => $user->id,
                'user_name' => $user->full_name ?? null,
                'mobile' => $user->phone ?? null,
                'email' => $user->email ?? null,
                'stitch_for_name' => $request->stitch_for_name,
                'phone_no' => $request->phone_no,
                'height' => $request->height,
                'body_weight' => $request->body_weight,
                'shoes_size' => $request->shoes_size,
                'front_photo' => $frontPhoto,
                'side_photo' => $sidePhoto,
                'back_photo' => $backPhoto,
                'mesurment_json' => $measurementJson,
                'additional_requirement' => $additionalRequirement,
                'category_id' => $commaSeparated,
            ]);

            /**
             * ---------------------------------------------------------
             * Response
             * ---------------------------------------------------------
             */
            return response()->json([
                'status' => true,
                'message' => 'Order created successfully.',
                'data' => $order
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 200);
        }
    }

    public function orderlist(Request $request)
    {
        try {
            /**
             * ---------------------------------------------------------
             * Authenticate User via JWT Token
             * ---------------------------------------------------------
             */
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found.'
                ], 200);
            }

            /**
             * ---------------------------------------------------------
             * Initialize Query
             * ---------------------------------------------------------
             */
            $query = Order::query();

            $query->where('user_id',$user->id);

            /**
             * ---------------------------------------------------------
             * Filter by Status (optional)
             * Example: ?status=completed
             * ---------------------------------------------------------
             */
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            /**
             * ---------------------------------------------------------
             * Filter by Date Range (optional)
             * Example:
             * ?from_date=2026-04-01&to_date=2026-04-10
             * ---------------------------------------------------------
             */
            if ($request->filled('from_date') && $request->filled('to_date')) {
                $query->whereBetween('created_at', [
                    $request->from_date . ' 00:00:00',
                    $request->to_date . ' 23:59:59'
                ]);
            }

            /**
             * ---------------------------------------------------------
             * Filter by Single Date (optional)
             * Example: ?date=2026-04-12
             * ---------------------------------------------------------
             */
            if ($request->filled('date')) {
                $query->whereDate('created_at', $request->date);
            }

            /**
             * ---------------------------------------------------------
             * Order & Pagination
             * ---------------------------------------------------------
             */
            $orders = $query->orderBy('id', 'desc')->paginate(10);

            /**
             * ---------------------------------------------------------
             * Response
             * ---------------------------------------------------------
             */
            return response()->json([
                'status' => true,
                'message' => 'Order found successfully.',
                'data' => $orders
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 200);
        }
    }

    public function orderDetail($id)
    {
        try {
            /**
             * ---------------------------------------------------------
             * Authenticate User via JWT Token
             * ---------------------------------------------------------
             */
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found.'
                ], 200);
            }

            /**
             * ---------------------------------------------------------
             * Fetch Order with Relations
             * ---------------------------------------------------------
             */
            $order = Order::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

            /**
             * ---------------------------------------------------------
             * Check Order Exists
             * ---------------------------------------------------------
             */
            if (!$order) {
                return response()->json([
                    'status' => false,
                    'message' => 'Order not found.'
                ], 200);
            }

            /**
             * ---------------------------------------------------------
             * Response
             * ---------------------------------------------------------
             */
            return response()->json([
                'status' => true,
                'message' => 'Order detail fetched successfully.',
                'data' => $order
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 200);
        }
    }

    public function downloadPdf($id)
    {
        try {

            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found.'
                ], 200);
            }

            $order = Order::where('id', $id)
                ->where('user_id', $user->id)
                ->first();


            if (!$order) {
                return response()->json([
                    'status' => false,
                    'message' => 'Order not found.'
                ], 200);
            }

            /**
             * ---------------------------------
             * Generate PDF URL (NOT download)
             * ---------------------------------
             */
            $pdfUrl = route('order.pdf.stream', ['id' => $order->id]);

            return response()->json([
                'status' => true,
                'message' => 'PDF URL generated successfully.',
                'pdf_url' => $pdfUrl
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 200);
        }
    }


    public function streamPdf($id)
    {
        try {

            $order = Order::where('id', $id)
                ->first();

            if (!$order) {
                return response()->json([
                    'status' => false,
                    'message' => 'Order not found.'
                ], 200);
            }

            // ✅ Decode JSON
            $order->measurements = json_decode($order->mesurment_json, true) ?? [];
            $order->additional = json_decode($order->additional_requirement, true) ?? [];
            
            // ✅ Important for images
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true
            ])->loadView('pdf.order', compact('order'));

            return $pdf->stream('order_'.$order->id.'.pdf');

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 200);
        }
    }
}
