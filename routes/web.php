
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\UnitController;

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

Route::middleware('auth')->group(function () {
    Route::get('/billing/plans', [BillingController::class, 'index'])
        ->name('billing.plans');

    Route::get('/properties', [PropertyController::class, 'index'])
        ->name('properties.index');

    Route::get('/properties/create', [PropertyController::class, 'create'])
        ->name('properties.create');

    Route::post('/properties', [PropertyController::class, 'store'])
        ->name('properties.store');

    Route::get('/properties/{property}', [PropertyController::class, 'show'])
        ->name('properties.show');

    Route::get('/properties/{property}/edit', [PropertyController::class, 'edit'])
        ->name('properties.edit');

    Route::put('/properties/{property}', [PropertyController::class, 'update'])
        ->name('properties.update');

    Route::patch('/properties/{property}/toggle-status', [PropertyController::class, 'toggleStatus'])
        ->name('properties.toggle-status');

    Route::get('/units/{unit}/edit', [UnitController::class, 'edit'])
        ->name('units.edit');

    Route::put('/units/{unit}', [UnitController::class, 'update'])
        ->name('units.update');
});

// Protected landlord dashboard
Route::get('/dashboard', [AuthController::class, 'dashboard'])
    ->middleware('auth')
    ->name('dashboard');
