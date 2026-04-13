<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MasterAuthController;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::group(['prefix'=>'auth'], function(){
    Route::post('/send-phone-otp', [AuthController::class, 'sendPhoneOtp']);
    Route::post('/verify-phone-otp', [AuthController::class, 'verifyPhoneOtp']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/verify-register', [AuthController::class, 'verifyRegister']);
});

Route::middleware('jwt.verify')->group(function() {
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::post('/update-profile', [AuthController::class, 'updateProfile']);     
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::delete('/delete-account', [AuthController::class, 'deleteAccount']);
    Route::get('/category', [AuthController::class, 'getCategory']);
    Route::post('/categorydetail', [AuthController::class, 'getCategoryDetail']);
    Route::post('/createorder', [AuthController::class, 'orderCreate']);
    Route::get('/orderlist', [AuthController::class, 'orderlist']);
    
});



Route::group(['prefix'=>'master-auth'], function(){
    Route::post('/login', [MasterAuthController::class, 'login']);
});

Route::middleware('jwt.verify')->group(function() {
    Route::get('master/user', [MasterAuthController::class, 'getMaster']);    
    Route::post('/logout', [MasterAuthController::class, 'logout']);
});
