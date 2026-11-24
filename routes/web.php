
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\TenancyController;
use App\Http\Controllers\RentChargeController;
use App\Http\Controllers\RentPaymentController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PropertyListingController;
use App\Http\Controllers\PublicPropertyListingController;

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

Route::get('/tenant-payments/{token}', [RentPaymentController::class, 'publicHistory'])
    ->where('token', '[A-Za-z0-9]{64}')
    ->middleware('throttle:30,1')
    ->name('tenant-payments.show');

Route::post('/tenant-payments/{token}', [RentPaymentController::class, 'submitPublic'])
    ->where('token', '[A-Za-z0-9]{64}')
    ->middleware('throttle:10,1')
    ->name('tenant-payments.submit');

Route::get('/rentals/{listing:slug}', [PublicPropertyListingController::class, 'show'])
    ->name('rentals.show');

Route::get('/tenant-payments/{token}/receipts/{payment}', [RentPaymentController::class, 'publicReceipt'])
    ->where('token', '[A-Za-z0-9]{64}')
    ->whereNumber('payment')
    ->middleware('throttle:30,1')
    ->name('tenant-payments.receipt');

Route::prefix('manage')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'create'])
        ->name('login');
    Route::post('/login', [AdminAuthController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('login.submit');

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/', [AdminController::class, 'index'])
            ->name('dashboard');
        Route::post('/logout', [AdminAuthController::class, 'destroy'])
            ->name('logout');
        Route::get('/accounts', [AdminController::class, 'accounts'])
            ->name('accounts');
        Route::patch('/accounts/{landlord}/approval', [AdminController::class, 'updateAccount'])
            ->whereNumber('landlord')
            ->name('accounts.update');
        Route::get('/subscriptions', [AdminController::class, 'subscriptions'])
            ->name('subscriptions');
        Route::post('/subscriptions/{landlord}/extend', [AdminController::class, 'extendSubscription'])
            ->whereNumber('landlord')
            ->name('subscriptions.extend');
        Route::get('/properties', [AdminController::class, 'properties'])
            ->name('properties');
        Route::patch('/properties/{property}/availability', [AdminController::class, 'updateProperty'])
            ->whereNumber('property')
            ->name('properties.update');
        Route::get('/listings', [AdminController::class, 'listings'])
            ->name('listings');
        Route::patch('/listings/{listing}/review', [AdminController::class, 'reviewListing'])
            ->whereNumber('listing')
            ->name('listings.review');
    });
});

// Landlord logout
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'landlord.approved'])->group(function () {
    Route::get('/billing/plans', [BillingController::class, 'index'])
        ->name('billing.plans');

    Route::get('/properties', [PropertyController::class, 'index'])
        ->name('properties.index');

    Route::get('/property-listings', [PropertyListingController::class, 'index'])
        ->name('property-listings.index');
    Route::get('/property-listings/create', [PropertyListingController::class, 'create'])
        ->name('property-listings.create');
    Route::post('/property-listings', [PropertyListingController::class, 'store'])
        ->name('property-listings.store');
    Route::get('/property-listings/{listing}/edit', [PropertyListingController::class, 'edit'])
        ->whereNumber('listing')
        ->name('property-listings.edit');
    Route::put('/property-listings/{listing}', [PropertyListingController::class, 'update'])
        ->whereNumber('listing')
        ->name('property-listings.update');
    Route::delete('/property-listings/{listing}/images/{image}', [PropertyListingController::class, 'destroyImage'])
        ->whereNumber('listing')
        ->whereNumber('image')
        ->name('property-listings.images.destroy');

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

    Route::get('/tenants/{tenant}/payment-history', [RentPaymentController::class, 'tenantHistory'])
        ->whereNumber('tenant')
        ->name('payments.tenant-history');

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

    Route::get('/tenancies/vacated', [TenancyController::class, 'vacated'])
        ->name('tenancies.vacated');

    Route::get('/tenancies/{tenancy}/reconciliation/final', [TenancyController::class, 'finalReconciliation'])
        ->whereNumber('tenancy')
        ->name('tenancies.reconciliation.final');

    Route::get('/tenancies/{tenancy}/reconciliation', [TenancyController::class, 'reconciliation'])
        ->whereNumber('tenancy')
        ->name('tenancies.reconciliation');

    Route::put('/tenancies/{tenancy}/reconciliation', [TenancyController::class, 'saveReconciliation'])
        ->whereNumber('tenancy')
        ->name('tenancies.reconciliation.save');

    Route::patch('/tenancies/{tenancy}/reconciliation/finalize', [TenancyController::class, 'finalizeReconciliation'])
        ->whereNumber('tenancy')
        ->name('tenancies.reconciliation.finalize');

    Route::post('/tenancies/{tenancy}/reconciliation/refund', [TenancyController::class, 'markRefundPaid'])
        ->whereNumber('tenancy')
        ->name('tenancies.reconciliation.refund');

    Route::get('/tenancies/{tenancy}', [TenancyController::class, 'show'])
        ->whereNumber('tenancy')
        ->name('tenancies.show');

    Route::patch('/tenancies/{tenancy}/end', [TenancyController::class, 'end'])
        ->whereNumber('tenancy')
        ->name('tenancies.end');

    Route::get('/rent-charges/export/excel', [RentChargeController::class, 'exportExcel'])
        ->name('rent-charges.export.excel');

    Route::get('/rent-charges/export/pdf', [RentChargeController::class, 'exportPdf'])
        ->name('rent-charges.export.pdf');

    Route::get('/rent-charges', [RentChargeController::class, 'index'])
        ->name('rent-charges.index');

    Route::post('/rent-charges', [RentChargeController::class, 'store'])
        ->name('rent-charges.store');

    Route::get('/monthly-bills', [RentChargeController::class, 'monthlyReport'])
        ->name('monthly-bills.index');

    Route::get('/payments', [RentPaymentController::class, 'index'])
        ->name('payments.index');

    Route::get('/tenancies/{tenancy}/payments/create', [RentPaymentController::class, 'create'])
        ->whereNumber('tenancy')
        ->name('payments.create');

    Route::post('/tenancies/{tenancy}/payments', [RentPaymentController::class, 'store'])
        ->whereNumber('tenancy')
        ->name('payments.store');

    Route::get('/tenancies/{tenancy}/payments/history', [RentPaymentController::class, 'history'])
        ->whereNumber('tenancy')
        ->name('payments.history');

    Route::post('/payment-submissions/{submission}/approve', [RentPaymentController::class, 'approve'])
        ->whereNumber('submission')
        ->name('payments.submissions.approve');

    Route::post('/payment-submissions/{submission}/reject', [RentPaymentController::class, 'reject'])
        ->whereNumber('submission')
        ->name('payments.submissions.reject');

    Route::post('/tenancies/{tenancy}/payment-share', [RentPaymentController::class, 'share'])
        ->whereNumber('tenancy')
        ->name('payments.share');

    Route::delete('/tenancies/{tenancy}/payment-share', [RentPaymentController::class, 'revokeShare'])
        ->whereNumber('tenancy')
        ->name('payments.share.revoke');

    Route::get('/deposits', [DepositController::class, 'index'])
        ->name('deposits.index');

    Route::get('/reports', [ReportController::class, 'index'])
        ->name('reports.index');

    Route::get('/expenses', [ExpenseController::class, 'index'])
        ->name('expenses.index');

    Route::get('/expenses/create', [ExpenseController::class, 'create'])
        ->name('expenses.create');

    Route::post('/expenses', [ExpenseController::class, 'store'])
        ->name('expenses.store');

    Route::get('/tenancies/{tenancy}/deposits/create', [DepositController::class, 'create'])
        ->whereNumber('tenancy')
        ->name('deposits.create');

    Route::post('/tenancies/{tenancy}/deposits', [DepositController::class, 'store'])
        ->whereNumber('tenancy')
        ->name('deposits.store');

    Route::get('/tenancies/{tenancy}/deposits/history', [DepositController::class, 'history'])
        ->whereNumber('tenancy')
        ->name('deposits.history');
});

// Protected landlord dashboard
Route::get('/dashboard', [AuthController::class, 'dashboard'])
    ->middleware(['auth', 'landlord.approved'])
    ->name('dashboard');
