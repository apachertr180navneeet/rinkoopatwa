<?php

use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Admin\AdminAuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// =================== FRONTEND =================== //
Route::controller(HomeController::class)->group(function () {
    Route::get('/', 'index')->name('home');
    Route::get('/home', 'index');
});


// =================== ADMIN =================== //
Route::prefix('admin')->name('admin.')->group(function () {

    // ---------- AUTH ROUTES ---------- //
    Route::controller(AdminAuthController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('login', 'login')->name('login');
        Route::post('login', 'postLogin')->name('login.post');

        Route::get('forget-password', 'showForgetPasswordForm')->name('forget.password.get');
        Route::post('forget-password', 'submitForgetPasswordForm')->name('forget.password.post');

        Route::get('reset-password/{token}', 'showResetPasswordForm')->name('reset.password.get');
        Route::post('reset-password', 'submitResetPasswordForm')->name('reset.password.post');
    });

    // ---------- PROTECTED ROUTES ---------- //
    Route::middleware('admin')->group(function () {

        // Dashboard & Profile
        Route::controller(AdminAuthController::class)->group(function () {
            Route::get('dashboard', 'adminDashboard')->name('dashboard');
            Route::get('change-password', 'changePassword')->name('change.password');
            Route::post('update-password', 'updatePassword')->name('update.password');
            Route::get('logout', 'logout')->name('logout');

            Route::get('profile', 'adminProfile')->name('profile');
            Route::post('profile', 'updateAdminProfile')->name('update.profile');
        });

    });
});


// =================== AUTH USER (FUTURE USE) =================== //
Route::middleware(['auth'])->group(function () {
    // Add user routes here
});