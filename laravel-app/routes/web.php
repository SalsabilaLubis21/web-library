<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FaceController;
use App\Http\Controllers\RecommendationController;

/*
|--------------------------------------------------------------------------
| WEB ROUTES
|--------------------------------------------------------------------------
*/

// =========================
//  HOME / DASHBOARD
// =========================
Route::get('/', function () {
    return redirect()->route('login.page');
});

// =========================
//  AUTH PAGES (VIEW ONLY)
// =========================
Route::get('/login', [AuthController::class, 'loginPage'])->name('login.page');
Route::get('/register', [AuthController::class, 'registerPage'])->name('register.page');


// =========================
//  FACE AUTH USING PYTHON
// =========================

// Register face
Route::post('/register-face', [AuthController::class, 'registerFace'])->name('register.face');

// Login via face
Route::post('/login-face', [AuthController::class, 'loginFace'])->name('login.face');

// Endpoint dari Laravel → Python insightface
Route::post('/detect-face', [FaceController::class, 'detectFace'])->name('detect.face');


// =========================
//  RECOMMENDATION SYSTEM (DSS)
// =========================
Route::middleware('auth')->group(function () {

    // Show result to UI
    Route::get('/recommendations', [RecommendationController::class, 'show'])
        ->name('recommendations.show');

    // Get JSON recommendations
    Route::get('/api/recommendations', [RecommendationController::class, 'api'])
        ->name('recommendations.api');

    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});