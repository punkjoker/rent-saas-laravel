
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\TenancyController;

// Public homepage
Route::get('/', [AuthController::class, 'home'])
    ->name('home');

// Landlord registration
Route::post('/register', [AuthController::class, 'register'])
    ->middleware('guest')
    ->name('register');

// Display the login form embedded on the homepage.
Route::get('/login', [AuthController::class, 'home'])
    ->middleware('guest')
    ->name('login');

// Process landlord login.
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('guest')
    ->name('login.submit');

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

    // Tenant management
    Route::get('/tenants', [TenantController::class, 'index'])
        ->name('tenants.index');

    Route::get('/tenants/create', [TenantController::class, 'create'])
        ->name('tenants.create');

    Route::post('/tenants', [TenantController::class, 'store'])
        ->name('tenants.store');

    Route::get('/tenants/{tenant}/edit', [TenantController::class, 'edit'])
        ->whereNumber('tenant')
        ->name('tenants.edit');

    Route::put('/tenants/{tenant}', [TenantController::class, 'update'])
        ->whereNumber('tenant')
        ->name('tenants.update');

    Route::get('/tenants/{tenant}', [TenantController::class, 'show'])
        ->whereNumber('tenant')
        ->name('tenants.show');

    // Tenancy management
    Route::get('/tenancies', [TenancyController::class, 'index'])
        ->name('tenancies.index');

    Route::get('/tenancies/create', [TenancyController::class, 'create'])
        ->name('tenancies.create');

    Route::post('/tenancies', [TenancyController::class, 'store'])
        ->name('tenancies.store');

    Route::get('/tenancies/{tenancy}', [TenancyController::class, 'show'])
        ->whereNumber('tenancy')
        ->name('tenancies.show');

    Route::patch('/tenancies/{tenancy}/end', [TenancyController::class, 'end'])
        ->whereNumber('tenancy')
        ->name('tenancies.end');
});

// Protected landlord dashboard
Route::get('/dashboard', [AuthController::class, 'dashboard'])
    ->middleware('auth')
    ->name('dashboard');
