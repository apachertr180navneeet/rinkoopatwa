<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Mail,Hash,File,DB,Helper,Auth;
use App\Mail\UserRegisterVerifyMail;
use App\Models\EmailOtp;
use App\Models\PhoneOtp;
use App\Models\AppUser;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\Filesystem;
use App\Models\SplashScreen;



class AuthController extends Controller
{
    
    public function splashScreens(){
        $base_url = asset('/');
        $splash_screens = SplashScreen::select('type','heading','content','image')->get();
        foreach ($splash_screens as $key => $screen) {
            if($screen['image']){
                $screen['image'] = $base_url.$screen['image'];
            }
        }
        return response()->json([
            'status' => true,
            'data' => $splash_screens,
        ],200);

    }

    public function sendPhoneOtp(Request $request){
        $data = $request->all();
        $validator = Validator::make($data, [
            'phone' => 'required|digits_between:4,13',
            'country_code' => "required|max:5",
        ]);
        
        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' =>  $validator->errors()->first(),
            ],200);
        }

        // $code = rand(1000,9999);
        $code = '1234';
        $date = date('Y-m-d H:i:s');
        $currentDate = strtotime($date);
        $futureDate = $currentDate+(60*120);
        $phone_user = PhoneOtp::where('country_code',$data['country_code'])->where('phone',$data['phone'])->first();
        if(!$phone_user){
            $phone_user = new PhoneOtp();
        }
        $phone_user->phone = $data['phone'];
        $phone_user->country_code = $data['country_code'];
        $phone_user->otp = $code;
        $phone_user->otp_expire_time = $futureDate;
        $phone_user->save();
        return response()->json([
            'status' => true,
            'message' =>  'A one-time password has been sent to your phone, please check.',
        ],200);
            
        
    }

    public function verifyPhoneOtp(Request $request){
        $data = $request->all();
        $validator = Validator::make($data, [
            'phone' => 'required|digits_between:4,13',
            'country_code' => "required|max:5",
            'otp' => "required|max:4",
        ]);
        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' =>  $validator->errors()->first(),
            ],200);
        }
        
        $phone_user = PhoneOtp::where('country_code',$data['country_code'])->where('phone',$data['phone'])->first();
        if($phone_user){
            $date = date('Y-m-d H:i:s');
            $currentTime = strtotime($date);
            if($phone_user->otp == $data['otp']){
                if($currentTime < $phone_user->otp_expire_time){
                    PhoneOtp::where('country_code',$data['country_code'])->where('phone',$data['phone'])->delete();
                    return response()->json([
                        'status' => true,
                        'message' =>  'Verified successfully.',
                    ],200);
                }else{
                    return response()->json([
                        'status' => true,
                        'message' =>  'Verification code is expired.',
                    ],200);
                }
            }else{
                return response()->json([
                    'status' => false,
                    'message' =>  'Invalid verification code. Please try again',
                ],200);
            }

        }else{
            return response()->json([
                'status' => false,
                'message'=>'Invalid phone number. Please check and try again'
            ],200);
        }
        
    }

    public function register(Request $request) 
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:users',
            'phone' => 'required|numeric|digits_between:4,12|unique:users',
            'password' => 'required|min:6',
            'address' => 'required',
            'area' => 'sometimes',
            'city' => 'sometimes',
            'state' => 'sometimes',
            'country' => 'sometimes',
            'country_code' => 'required|numeric',
            'zipcode' => 'sometimes',
            'latitude' => 'required',
            'longitude' => 'required',
            'bio' => 'sometimes',
            'device_type' => 'sometimes',
            'device_token' => 'sometimes',
            'avatar'  =>  'required|image|mimes:jpeg,jpg,png|max:5000',
        ],[
            'email.email' => 'Invalid email format. Please try again',
            'email.unique' => 'This email is already registered. Please sign in or use a different email',
            'password.min' => 'Password must be at least 6 characters long',
            'dob.before_or_equal' => 'The date of birth must be greater than or equal to 16 years ago.',
        ]);
        
        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' =>  $validator->errors()->first(),
            ],200);
        }
        try
        {
            AppUser::where('phone',$request->phone)->where('country_code',$request->country_code)->delete();
            $app_user = new AppUser();
            $app_user->first_name = $request->first_name;
            $app_user->last_name = $request->last_name;
            $app_user->full_name = $request->first_name.' '.$request->last_name;
            $app_user->slug = Helper::slug('users',$app_user->full_name);
            $app_user->email = $request->email;
            $app_user->phone = $request->phone;
            $app_user->password = $request->password;
            $app_user->address = $request->address;
            $app_user->area = $request->area ?? '';
            $app_user->city = $request->city ?? '';
            $app_user->state = $request->state ?? '';
            $app_user->country = $request->country ?? '';
            $app_user->country_code = $request->country_code;
            $app_user->zipcode = $request->zipcode ?? '';
            $app_user->latitude = $request->latitude;
            $app_user->longitude = $request->longitude;
            $app_user->bio = $request->bio ?? '';
            $app_user->device_type = $request->device_type ?? '';
            $app_user->device_token = $request->device_token ?? '';
            $app_user->avatar = $request->avatar;
            $app_user->save();
            
            return response()->json([
                'status' => true,
                'message' => 'Otp is sent on your phone! Please verify otp to complete your registration',
            ],200);

        }
        catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ],200);
        }
        
    }

    public function verifyRegister(Request $request){
        $data = $request->all();
        $validator = Validator::make($data, [
            'phone' => "required|numeric|exists:app_users,phone|unique:users,phone",
            'country_code' => "required|numeric|exists:app_users,country_code",
            'otp' => "required|max:4",
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
            $user->password = bcrypt($app_user->password);
            $user->address = $app_user->address;
            $user->area = $app_user->area ?? '';
            $user->city = $app_user->city ?? '';
            $user->state = $app_user->state ?? '';
            $user->country = $app_user->country ?? '';
            $user->country_code = $app_user->country_code;
            $user->zipcode = $app_user->zipcode ?? '';
            $user->latitude = $app_user->latitude ?? '';
            $user->longitude = $app_user->longitude ?? '';
            $user->device_type = $app_user->device_type ?? '';
            $user->device_token = $app_user->device_token ?? '';
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
            $input['password'] = $app_user->password;
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

    
    public function login(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'phone' => 'required|numeric',
            'country_code' => 'required|numeric',
            'password' => 'required',
            'device_type'=>'required|in:ios,android',
            'device_token'=>'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ],200);
        }
        
        try
        {
            $user = User::where('phone',$data['phone'])->where('country_code',$data['country_code'])->where('role','user')->first();
           
            if(!$user){
                return response()->json([
                    'status' => false,
                    'message' => 'Phone number not exists',
                ]);      
            }

            if($user->status == 'inactive'){
                return response()->json([
                    'status' => false,
                    'message' => 'Your account is not activacted yet.',
                ]);      
            }
            
            $input['phone'] = $data['phone'];
            $input['country_code'] = $data['country_code'];
            $input['password'] = $data['password'];

            if(!$token = JWTAuth::attempt($input)) {
                return response()->json([
                    'status' => false,
                    'message'=>'Invalid phone or password. Please try again'
                ],200);
            }

            $user = User::find(auth()->user()->id);
            $user->device_type = $data['device_type'];
            $user->device_token = $data['device_token'];
            $user->save();
            
            return response()->json([
                'status' => true,
                'message'=>'Loggedin successfully.',
                'access_token' => $token,
                'token_type' => 'bearer',
                'user' => $this->getUserDetail(auth()->user()->id),
            ],200);
        }
        catch (JWTException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ],200);
        }
    }

    public function refresh() {
        return $this->createNewToken(JWTAuth::refresh());
    }

    protected function createNewToken($token){
        return response()->json([
            'status' => true,
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => auth()->user(),
            'message'=>'Token refresh successfully.'
        ],200);
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

    public function setForgotPassword(Request $request){
        $data = $request->all();
        $validator = Validator::make($data, [
            'phone' => 'required|exists:users,phone|digits_between:4,13',
            'country_code' => "required|exists:users,country_code|max:5",
            'password' => 'required|string|confirmed|min:6',
        ]);
        
        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' =>  $validator->errors()->first(),
            ],200);
        }
        
        $user = User::where('phone',$data['phone'])->where('country_code',$data['country_code'])->first();
        if($user){
            if(Hash::check($request->password,$user->password)){
                return response()->json([
                    'status' => false,
                    'message' =>  'Cannot use your old password as new password.',
                ],200);
            }else{
                $user->password = Hash::make($request->password);
                $user->save();
                return response()->json([
                    'status' => true,
                    'message' =>  'New Password set successfully.Please Login'    
                ],200);
            } 
        } 
        else{
            return response()->json([
                'status' => false,
                'message' =>  'Phone number user not exists'
            ],200);
        }
        
    }

    public function changePassword(Request $request){
        $data = $request->all();
        $user = auth()->user();  
        $validator = Validator::make($data, [
            'old_password' => 'required',
            'new_password' => 'confirmed|required|string|min:6',
        ]);
        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' =>  $validator->errors()->first()
            ],200);
        }
        
        $user = auth()->user();  
        if(Hash::check($request->new_password,$user->password)) {
            return response()->json([
                'status' => false,
                'message' =>  'Cannot use your old password as new password.',
            ],200);
        }
        
        if(!Hash::check($request->old_password,$user->password)) {
            return response()->json([
                'status' => false,
                'message' =>  'Old Password did not matched!',


            ],200);
        }
     
        $user->password = Hash::make($request->new_password);
        $user->save();
        // JWTAuth::parseToken()->invalidate(true);
        return response()->json([
            'status' => true,
            'message' =>  'Password changed successfully',
            'user' => $this->getUserDetail($user->id),
        ],200);
         
    }
    
    public function updateProfile(Request $request){
        $data   =   $request->all();
        $id = auth()->user()->id;

        $validator = Validator::make($data, [
            'first_name' => 'sometimes|string',
            'last_name' => 'sometimes|string',
            'email'     =>  'sometimes|email|unique:users,email,'.$id,
            'phone'     =>  'sometimes|numeric|digits_between:4,12|unique:users,phone,'.$id,
            'password' => 'sometimes|min:6',
            'avatar'  =>  'sometimes|mimes:jpeg,jpg,png|max:5000',
            'address' => 'sometimes',
            'area' => 'sometimes',
            'city' => 'sometimes',
            'state' => 'sometimes',
            'country' => 'sometimes',
            'country_code' => 'sometimes|numeric',
            'zipcode' => 'sometimes',
            'latitude' => 'sometimes',
            'longitude' => 'sometimes',
            'bio' => 'sometimes',
            'device_type' => 'sometimes',
            'device_token' => 'sometimes',
        ],[
            'dob.after_or_equal' => 'The date of birth must be greater than or equal to 16 years ago.',
        ]);

       
        if($validator->fails()) {
            return response()->json(array(
                'status' => false,
                'message' =>  $validator->errors()->first()
            ),200);
        }

        try{
            
            $user = User::find($id);
            foreach($data as $key => $value){
                if(!empty($data[$key])){
                    if($key == 'first_name'){
                        $user->first_name = $value;
                        $user->full_name = $user->first_name.' '.$user->last_name;
                    }
                    elseif($key == 'last_name'){
                        $user->last_name = $value;
                        $user->full_name = $user->first_name.' '.$user->last_name;
                        
                    }
                    elseif($key == 'avatar'){
                        $file = $request->file('avatar');
                        if($file){
                            $filename   = time().$file->getClientOriginalName();
                            $filename   =  Helper::cleanImage($filename);
                            $folder = 'uploads/user/';
                            $path = public_path($folder);
                            if(!File::exists($path)) {
                                File::makeDirectory($path, $mode = 0777, true, true);
                            }
                            $file->move($path, $filename);
                            $user->avatar   = $folder.$filename;
                        }
                    }
                    else{
                        $user->$key = $value;
                    }
                }
            }
            $user->save();
            
            return response()->json(array(
                'status' => true,
                'message' => 'Profile updated successfully!',
                'user' => $this->getUserDetail($user->id),
            ),200);
        }
        catch(Exception $e){
            return response()->json(array(
                'status' => false,
                'message' => $e->getMessage()
            ),200);
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
}
