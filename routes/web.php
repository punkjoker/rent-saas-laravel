
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Public homepage
Route::get('/', [AuthController::class, 'home'])
    ->name('home');

// Landlord registration
Route::post('/register', [AuthController::class, 'register'])
    ->middleware('guest')
    ->name('register');

// Landlord login
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('guest')
    ->name('login');

// Landlord logout
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Protected landlord dashboard
Route::get('/dashboard', [AuthController::class, 'dashboard'])
    ->middleware('auth')
    ->name('dashboard');
