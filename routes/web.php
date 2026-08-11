<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ForgotPasswordController as AdminForgotPasswordController;
use App\Http\Controllers\Admin\BandwidthController as AdminBandwidthController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\RouterController as AdminRouterController;
use App\Http\Controllers\Customer\AuthController as CustomerAuthController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\ForgotPasswordController as CustomerForgotPasswordController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\VoucherPrintController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Inertia\Inertia::render('Public/Welcome');
});

Route::get('/invoice/{order:invoice_token}', [InvoiceController::class, 'show'])->name('invoice.show');

Route::get('/admin/vouchers/print', [VoucherPrintController::class, 'show'])
    ->middleware('auth:web')
    ->name('voucher.print');

// Admin auth + dashboard (Vue + Inertia)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:web')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    });

    Route::middleware('auth:web')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/', [AdminDashboardController::class, 'show'])->name('dashboard');
        Route::get('/dashboard', fn () => redirect()->route('admin.dashboard'));
    });
});

Route::middleware('auth:web')->prefix('admin/customers')->name('admin.customers.')->group(function () {
    Route::get('/', [AdminCustomerController::class, 'index'])->name('index');
    Route::get('/create', [AdminCustomerController::class, 'create'])->name('create');
    Route::post('/', [AdminCustomerController::class, 'store'])->name('store');
    Route::get('/{customer}/edit', [AdminCustomerController::class, 'edit'])->name('edit');
    Route::put('/{customer}', [AdminCustomerController::class, 'update'])->name('update');
    Route::delete('/{customer}', [AdminCustomerController::class, 'destroy'])->name('destroy');
});

Route::middleware('auth:web')->prefix('admin/bandwidths')->name('admin.bandwidths.')->group(function () {
    Route::get('/', [AdminBandwidthController::class, 'index'])->name('index');
    Route::get('/create', [AdminBandwidthController::class, 'create'])->name('create');
    Route::post('/', [AdminBandwidthController::class, 'store'])->name('store');
    Route::get('/{bandwidth}/edit', [AdminBandwidthController::class, 'edit'])->name('edit');
    Route::put('/{bandwidth}', [AdminBandwidthController::class, 'update'])->name('update');
    Route::delete('/{bandwidth}', [AdminBandwidthController::class, 'destroy'])->name('destroy');
});

Route::middleware('auth:web')->prefix('admin/routers')->name('admin.routers.')->group(function () {
    Route::get('/', [AdminRouterController::class, 'index'])->name('index');
    Route::get('/create', [AdminRouterController::class, 'create'])->name('create');
    Route::post('/', [AdminRouterController::class, 'store'])->name('store');
    Route::get('/{router}/edit', [AdminRouterController::class, 'edit'])->name('edit');
    Route::put('/{router}', [AdminRouterController::class, 'update'])->name('update');
    Route::delete('/{router}', [AdminRouterController::class, 'destroy'])->name('destroy');
});

// Admin forgot-password — guest-accessible flow outside the authed group.
Route::prefix('admin-forgot-password')->name('admin.forgot-password.')->group(function () {
    Route::get('/', [AdminForgotPasswordController::class, 'show'])->name('show');
    Route::post('/request', [AdminForgotPasswordController::class, 'requestCode'])->name('request');
    Route::post('/reset', [AdminForgotPasswordController::class, 'reset'])->name('reset');
});

Route::prefix('customer')->name('customer.')->group(function () {
    Route::middleware('guest:customer')->group(function () {
        Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [CustomerAuthController::class, 'login'])->name('login.submit');

        Route::prefix('forgot-password')->name('forgot-password.')->group(function () {
            Route::get('/', [CustomerForgotPasswordController::class, 'show'])->name('show');
            Route::post('/request', [CustomerForgotPasswordController::class, 'requestCode'])->name('request');
            Route::post('/reset', [CustomerForgotPasswordController::class, 'reset'])->name('reset');
        });
    });

    Route::middleware('auth:customer')->group(function () {
        Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [CustomerDashboardController::class, 'show'])->name('dashboard');
    });
});
