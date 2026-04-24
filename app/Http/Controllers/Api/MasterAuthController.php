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
use App\Models\CategoryStitch;
use Carbon\Carbon;



class MasterAuthController extends Controller
{

    public function login(Request $request)
    {
        $data = $request->all();

        // -------------------------------
        // Validation
        // -------------------------------
        $validator = Validator::make($data, [
            'phone' => 'required|numeric',
            'password' => 'required',
            'device_type' => 'nullable|in:ios,android',
            'device_token' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 200);
        }

        try {

            // -------------------------------
            // Check User Exists
            // -------------------------------
            $user = User::where('phone', $data['phone'])
                        ->where('role', 'stitch')
                        ->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Phone number not exists',
                ], 200);
            }

            // -------------------------------
            // Check User Status
            // -------------------------------
            if ($user->status == 'inactive') {
                return response()->json([
                    'status' => false,
                    'message' => 'Your account is not activated yet.',
                ], 200);
            }

            // -------------------------------
            // Check Password Manually
            // -------------------------------
            if (!Hash::check($data['password'], $user->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid phone or password. Please try again 1',
                ], 200);
            }

            // -------------------------------
            // Generate Token (IMPORTANT FIX)
            // -------------------------------
            $token = JWTAuth::fromUser($user);

            // -------------------------------
            // Update Device Info
            // -------------------------------
            $user->device_type = $data['device_type'] ?? 'ios';
            $user->device_token = $data['device_token'] ?? null;
            $user->save();

            // -------------------------------
            // Response
            // -------------------------------
            return response()->json([
                'status' => true,
                'message' => 'Loggedin successfully.',
                'access_token' => $token,
                'token_type' => 'bearer',
                'user' => $this->getUserDetail($user->id),
            ], 200);

        } catch (Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 200);
        }
    }

    public function getMaster() 
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
             * Pagination Setup
             * ---------------------------------------------------------
             */
            $perPage = $request->get('per_page', 10);

            /**
             * ---------------------------------------------------------
             * Filters
             * ---------------------------------------------------------
             */
            $search = $request->get('search');   // search text
            $status = $request->get('status');   // pending, trial-ready, complete

            /**
             * ---------------------------------------------------------
             * Query with Joins
             * ---------------------------------------------------------
             */
            $query = CategoryStitch::select(
                    'category_stitch.*',
                    'orders.id as order_id',
                    'orders.order_no',
                    'orders.user_name',
                    'orders.mobile',
                    'orders.email',
                    'categories.id as category_id',
                    'categories.name as category_name'
                )
                ->join('orders', 'orders.id', '=', 'category_stitch.order_id')
                ->join('categories', 'categories.id', '=', 'category_stitch.category_id')
                ->where('category_stitch.stitch_id', $user->id);

            /**
             * ---------------------------------------------------------
             * Status Filter
             * ---------------------------------------------------------
             */
            if (!empty($status)) {
                $query->where('category_stitch.status', $status);
            }

            /**
             * ---------------------------------------------------------
             * Search Filter
             * ---------------------------------------------------------
             */
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('orders.order_no', 'LIKE', "%$search%")
                    ->orWhere('orders.user_name', 'LIKE', "%$search%")
                    ->orWhere('orders.mobile', 'LIKE', "%$search%")
                    ->orWhere('categories.name', 'LIKE', "%$search%");
                });
            }

            /**
             * ---------------------------------------------------------
             * Order + Pagination
             * ---------------------------------------------------------
             */
            $orders = $query->orderBy('category_stitch.id', 'desc')
                            ->paginate($perPage);

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

    public function orderstatus(Request $request)
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
             * Validation
             * ---------------------------------------------------------
             */
            $request->validate([
                'id' => 'required|exists:category_stitch,id',
                'status'   => 'required|string'
            ]);

            /**
             * ---------------------------------------------------------
             * Find Order
             * ---------------------------------------------------------
             */
            $order = CategoryStitch::where('id', $request->id)
                        ->first();

            if (!$order) {
                return response()->json([
                    'status' => false,
                    'message' => 'Order not found.'
                ], 200);
            }

            /**
             * ---------------------------------------------------------
             * Update Status
             * ---------------------------------------------------------
             */
            $order->status = $request->status;
            $order->save();

            /**
             * ---------------------------------------------------------
             * Response
             * ---------------------------------------------------------
             */
            return response()->json([
                'status' => true,
                'message' => 'Order status updated successfully.',
                'data' => $order
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 200);
        }
    }

    public function orderdetail($id)
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
             * Find Order
             * ---------------------------------------------------------
             */
           /**
             * ---------------------------------------------------------
             * Query with Joins
             * ---------------------------------------------------------
             */
            $query = CategoryStitch::select(
                    'category_stitch.*',
                    'orders.id as order_id',
                    'orders.*',
                    'categories.id as category_id',
                    'categories.name as category_name'
                )
                ->join('orders', 'orders.id', '=', 'category_stitch.order_id')
                ->join('categories', 'categories.id', '=', 'category_stitch.category_id')
                ->where('category_stitch.id', $id);

            /**
             * ---------------------------------------------------------
             * Order + Pagination
             * ---------------------------------------------------------
             */
            $orders = $query->orderBy('category_stitch.id', 'desc')
                            ->first();

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
}
