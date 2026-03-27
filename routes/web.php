<?php

use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Admin\{
        AdminAuthController,
        UserController,
        StitchController,
        CategoryController
    };

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

        // User Controller
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/getall', [UserController::class, 'getAll'])->name('getall');
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::post('/store', [UserController::class, 'store'])->name('store');
            Route::get('/edit/{id}', [UserController::class, 'edit'])->name('edit');
            Route::post('/update/{id}', [UserController::class, 'update'])->name('update');
            Route::post('/status', [UserController::class, 'changeStatus'])->name('status');
            Route::delete('/delete/{id}', [UserController::class, 'delete'])->name('delete');
        });

        // StitchController
        Route::prefix('stitch')->name('stitch.')->group(function () {
            Route::get('/', [StitchController::class, 'index'])->name('index');
            Route::get('/getall', [StitchController::class, 'getAll'])->name('getall');
            Route::post('/store', [StitchController::class, 'store'])->name('store');
            Route::get('/edit/{id}', [StitchController::class, 'edit'])->name('edit');
            Route::post('/update/{id}', [StitchController::class, 'update'])->name('update');
            Route::post('/status', [StitchController::class, 'changeStatus'])->name('status');
            Route::delete('/delete/{id}', [StitchController::class, 'delete'])->name('delete');
        });

        // Measurement Categories
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [CategoryController::class, 'index'])->name('index');
            Route::get('/getall', [CategoryController::class, 'getAll'])->name('getall');
            Route::post('/store', [CategoryController::class, 'store'])->name('store');
            Route::get('/edit/{id}', [CategoryController::class, 'edit'])->name('edit');
            Route::post('/update/{id}', [CategoryController::class, 'update'])->name('update');
            Route::post('/status', [CategoryController::class, 'changeStatus'])->name('status');
            Route::delete('/delete/{id}', [CategoryController::class, 'delete'])->name('delete');

            // Select2 data
            Route::get('/select2', [CategoryController::class, 'select2'])->name('select2');
        });

    });
});


// =================== AUTH USER (FUTURE USE) =================== //
Route::middleware(['auth'])->group(function () {
    // Add user routes here
});