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
            'device_type' => 'required|in:ios,android',
            'device_token' => 'required',
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
            $user->device_type = $data['device_type'];
            $user->device_token = $data['device_token'];
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
}
