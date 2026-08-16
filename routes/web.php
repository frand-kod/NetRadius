<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\BandwidthController as AdminBandwidthController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ForgotPasswordController as AdminForgotPasswordController;
use App\Http\Controllers\Admin\GeneralSettingsController as AdminGeneralSettingsController;
use App\Http\Controllers\Admin\HelpController as AdminHelpController;
use App\Http\Controllers\Admin\IncomeReportController as AdminIncomeReportController;
use App\Http\Controllers\Admin\LogController as AdminLogController;
use App\Http\Controllers\Admin\NotificationSettingsController as AdminNotificationSettingsController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PaymentSettingsController as AdminPaymentSettingsController;
use App\Http\Controllers\Admin\PlanController as AdminPlanController;
use App\Http\Controllers\Admin\VoucherController as AdminVoucherController;
use App\Http\Controllers\Customer\AuthController as CustomerAuthController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\ForgotPasswordController as CustomerForgotPasswordController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\VoucherPrintController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Inertia\Inertia::render('Landing');
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

Route::middleware('auth:web')->get('/admin/income-report', [AdminIncomeReportController::class, 'show'])
    ->name('admin.income-report');

Route::middleware('auth:web')->get('/admin/logs', [AdminLogController::class, 'index'])
    ->name('admin.logs');

Route::middleware('auth:web')->get('/admin/help/{doc?}', [AdminHelpController::class, 'show'])
    ->where('doc', 'how-to-use|freeradius|gowa-wa')
    ->name('admin.help');

Route::middleware('auth:web')->prefix('admin/settings/general')->name('admin.settings.general.')->group(function () {
    Route::get('/', [AdminGeneralSettingsController::class, 'edit'])->name('edit');
    Route::post('/', [AdminGeneralSettingsController::class, 'update'])->name('update');
});

Route::middleware('auth:web')->prefix('admin/settings/payment')->name('admin.settings.payment.')->group(function () {
    Route::get('/', [AdminPaymentSettingsController::class, 'edit'])->name('edit');
    Route::post('/', [AdminPaymentSettingsController::class, 'update'])->name('update');
});

Route::middleware('auth:web')->prefix('admin/settings/notification')->name('admin.settings.notification.')->group(function () {
    Route::get('/', [AdminNotificationSettingsController::class, 'edit'])->name('edit');
    Route::post('/', [AdminNotificationSettingsController::class, 'update'])->name('update');
});

Route::middleware('auth:web')->prefix('admin/vouchers')->name('admin.vouchers.')->group(function () {
    Route::get('/', [AdminVoucherController::class, 'index'])->name('index');
    Route::get('/{voucher}/edit', [AdminVoucherController::class, 'edit'])->name('edit');
    Route::put('/{voucher}', [AdminVoucherController::class, 'update'])->name('update');
    Route::delete('/{voucher}', [AdminVoucherController::class, 'destroy'])->name('destroy');
    Route::post('/generate', [AdminVoucherController::class, 'generate'])->name('generate');
});

Route::middleware('auth:web')->prefix('admin/orders')->name('admin.orders.')->group(function () {
    Route::get('/', [AdminOrderController::class, 'index'])->name('index');
    Route::get('/create', [AdminOrderController::class, 'create'])->name('create');
    Route::post('/', [AdminOrderController::class, 'store'])->name('store');
    Route::post('/{order:id}/mark-as-paid', [AdminOrderController::class, 'markAsPaid'])->name('mark-as-paid');
    Route::post('/{order:id}/cancel', [AdminOrderController::class, 'cancel'])->name('cancel');
});

Route::middleware('auth:web')->prefix('admin/plans')->name('admin.plans.')->group(function () {
    Route::get('/', [AdminPlanController::class, 'index'])->name('index');
    Route::get('/create', [AdminPlanController::class, 'create'])->name('create');
    Route::post('/', [AdminPlanController::class, 'store'])->name('store');
    Route::get('/{plan}/edit', [AdminPlanController::class, 'edit'])->name('edit');
    Route::put('/{plan}', [AdminPlanController::class, 'update'])->name('update');
    Route::delete('/{plan}', [AdminPlanController::class, 'destroy'])->name('destroy');
});

Route::middleware('auth:web')->prefix('admin/bandwidths')->name('admin.bandwidths.')->group(function () {
    Route::get('/', [AdminBandwidthController::class, 'index'])->name('index');
    Route::get('/create', [AdminBandwidthController::class, 'create'])->name('create');
    Route::post('/', [AdminBandwidthController::class, 'store'])->name('store');
    Route::get('/{bandwidth}/edit', [AdminBandwidthController::class, 'edit'])->name('edit');
    Route::put('/{bandwidth}', [AdminBandwidthController::class, 'update'])->name('update');
    Route::delete('/{bandwidth}', [AdminBandwidthController::class, 'destroy'])->name('destroy');
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
