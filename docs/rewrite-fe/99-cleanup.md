# Task 99 — Cleanup & Final Verification

**Tujuan**: Hapus semua kode Filament, ganti routing admin ke Inertia, pastikan 49 tests pass.

**Dependensi**: Semua task 00-11 selesai

**Waktu estimasi**: 45 menit

---

## ⚠️ Sebelum mulai: pastikan semua task 00-11 sudah selesai dan terverifikasi

---

## Step 1: Hapus Dependency Filament dari Composer

```bash
composer remove filament/filament
```

Ini akan menghapus:
- `filament/filament` dan semua dependency-nya
- Reference di `composer.json` dan `composer.lock`

## Step 2: Bersihkan `composer.json` Scripts

Di `composer.json`, hapus baris ini dari `scripts.post-autoload-dump`:

```json
"@php artisan filament:upgrade"
```

## Step 3: Hapus Folder Filament

```bash
rm -rf app/Filament
rm -rf app/Providers/Filament
rm -rf resources/views/filament
```

## Step 4: Bersihkan `bootstrap/providers.php`

Cek `bootstrap/providers.php` — hapus baris yang mereferensi Filament:

```php
// HAPUS:
App\Providers\Filament\AdminPanelProvider::class,
```

## Step 5: Update Routing Admin

Sekarang Filament sudah tidak ada, `/admin` kosong. Tambahkan route admin dashboard di `routes/web.php`:

```php
use App\Http\Controllers\Admin\DashboardController;

// Admin auth routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:web')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    });

    Route::middleware('auth:web')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/', [DashboardController::class, 'show'])->name('dashboard');
        Route::get('/dashboard', fn () => redirect()->route('admin.dashboard'));
    });
});

// Pindahkan forgot-password dari /admin-forgot-password ke /admin/forgot-password
// ATAU biarkan di /admin-forgot-password (lebih aman — tidak perlu ubah route)
// REKOMENDASI: biarkan di /admin-forgot-password, sudah working + tested.
// Update link di Login.vue supaya mengarah ke route admin.forgot-password.show
```

## Step 6: Buat Admin Dashboard Controller

Buat `app/Http/Controllers/Admin/DashboardController.php`:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\Voucher;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function show(): \Inertia\Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'totalCustomers' => Customer::count(),
                'activeCustomers' => Customer::where('status', 'Active')->count(),
                'pendingOrders' => Order::where('status', 'pending')->count(),
                'unusedVouchers' => Voucher::where('status', '0')->count(),
                'totalIncome' => Transaction::sum('price'),
            ],
        ]);
    }
}
```

### Dashboard Vue Page — `resources/js/Pages/Admin/Dashboard.vue`

```vue
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({ stats: Object });
</script>

<template>
    <AdminLayout>
        <template #title>Dashboard</template>
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-white rounded shadow p-4">
                <p class="text-sm text-gray-500">Total Customer</p>
                <p class="text-2xl font-bold">{{ stats.totalCustomers }}</p>
            </div>
            <div class="bg-white rounded shadow p-4">
                <p class="text-sm text-gray-500">Customer Aktif</p>
                <p class="text-2xl font-bold text-green-600">{{ stats.activeCustomers }}</p>
            </div>
            <div class="bg-white rounded shadow p-4">
                <p class="text-sm text-gray-500">Order Pending</p>
                <p class="text-2xl font-bold text-amber-600">{{ stats.pendingOrders }}</p>
            </div>
            <div class="bg-white rounded shadow p-4">
                <p class="text-sm text-gray-500">Voucher Tersedia</p>
                <p class="text-2xl font-bold text-blue-600">{{ stats.unusedVouchers }}</p>
            </div>
            <div class="bg-white rounded shadow p-4">
                <p class="text-sm text-gray-500">Total Income</p>
                <p class="text-2xl font-bold">Rp {{ Number(stats.totalIncome).toLocaleString('id-ID') }}</p>
            </div>
        </div>
    </AdminLayout>
</template>
```

## Step 7: Update AdminLoginTest

Edit `tests/Feature/AdminLoginTest.php`. Ganti dari Filament Livewire test ke HTTP test:

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'username' => 'admin',
            'password' => bcrypt('password'),
            'status' => 'Active',
        ]);

        $response = $this->post('/admin/login', [
            'username' => 'admin',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user, 'web');
    }

    public function test_admin_cannot_login_with_invalid_password(): void
    {
        User::factory()->create([
            'username' => 'admin',
            'password' => bcrypt('password'),
            'status' => 'Active',
        ]);

        $response = $this->post('/admin/login', [
            'username' => 'admin',
            'password' => 'wrong',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest('web');
    }
}
```

## Step 8: Update `bootstrap/app.php` redirectGuestsTo

Karena `/admin` sekarang milik Inertia (bukan Filament), update guest redirect:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->redirectGuestsTo(function (Request $request) {
        if ($request->is('admin/*') || $request->is('admin')) {
            return route('admin.login');
        }
        return route('customer.login');
    });
})
```

## Step 9: Run Full Test Suite

```bash
php artisan test --compact
```

**Target: 49 tests, 49 passed.**

Kalau ada yang gagal:
1. Baca error message
2. Cek apakah ada reference ke Filament class yang masih tersisa
3. Perbaiki, jangan hapus test

## Step 10: Build Frontend

```bash
npm run build
```

Harus sukses tanpa error.

## Step 11: Manual Smoke Test

1. Buka `/` → Welcome page Vue
2. Buka `/admin/login` → login form Vue → login → redirect ke `/admin` dashboard
3. CRUD customer, plan, voucher, router, bandwidth — semua form Vue berfungsi
4. Order: buat → list → approve → status paid
5. Voucher: generate 5 → muncul di list → print → new tab dengan kartu voucher
6. Settings: payment upload QR, notification isi field
7. Income report: filter date, tabel muncul
8. `/customer/login` → login customer → dashboard dengan order history
9. `/invoice/{token}` → tampil invoice publik dengan QR
10. `/admin-forgot-password` → flow OTP bekerja

## Step 12: git status

```bash
git status
```

Pastikan tidak ada file yang tertinggal/terhapus tidak sengaja.

---

## Setelah Semua Selesai

Update `CHECKLIST.md` — centang semua item.

Commit dengan pesan:
```
Migrate admin panel from Filament to Vue 3 + Inertia.js

- Replace Filament resources with Vue 3 SFCs via Inertia.js
- Keep all backend services, models, and tests unchanged
- 49 tests passing
- Remove filament/filament dependency

Co-Authored-By: Claude <noreply@anthropic.com>
```
