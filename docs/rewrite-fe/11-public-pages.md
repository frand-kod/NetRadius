# Task 11 — Public Pages (Invoice + Voucher Print + Welcome)

**Tujuan**: Migrasi halaman publik ke Vue (kecuali voucher print dan welcome — tetap Blade).

**Dependensi**: `00-setup.md`

**Waktu estimasi**: 30 menit

---

## Halaman Invoice Publik

Halaman ini diakses via token tanpa login. Ganti controller return ke Inertia.

### Edit Controller

Edit `app/Http/Controllers/InvoiceController.php`:

```php
use Inertia\Inertia;

public function show(Order $order): \Inertia\Response
{
    $order->load('customer', 'plan');
    $qrPath = \App\Models\AppConfig::get('payment_qr_path');

    return Inertia::render('Public/Invoice', [
        'order' => $order,
        'qrPath' => $qrPath,
    ]);
}
```

### Vue Page — `resources/js/Pages/Public/Invoice.vue`

```vue
<script setup>
defineProps({
    order: Object,
    qrPath: String,
});
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center p-6">
        <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6">
            <h1 class="text-xl font-bold mb-4">Invoice #{{ order.id }}</h1>

            <div class="space-y-2 text-sm mb-4">
                <div class="flex justify-between">
                    <span class="text-gray-600">Customer</span>
                    <span class="font-medium">{{ order.customer?.fullname }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Paket</span>
                    <span class="font-medium">{{ order.plan?.name_plan }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Harga</span>
                    <span class="font-medium">Rp {{ Number(order.price).toLocaleString('id-ID') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Status</span>
                    <span :class="{
                        'text-amber-600': order.status === 'pending',
                        'text-green-600': order.status === 'paid',
                        'text-red-600': order.status === 'cancelled',
                    }" class="font-medium capitalize">{{ order.status }}</span>
                </div>
            </div>

            <div v-if="order.status === 'pending'" class="border-t pt-4">
                <img v-if="qrPath" :src="`/storage/${qrPath}`" alt="QR Pembayaran"
                     class="mx-auto w-64 rounded border mb-3" />
                <p v-else class="text-center text-gray-500">QR pembayaran belum diatur oleh admin.</p>
                <p class="text-center text-sm text-gray-500 mt-2">
                    Silakan lakukan pembayaran, admin akan konfirmasi secara manual.
                </p>
            </div>

            <div v-else-if="order.status === 'paid'" class="border-t pt-4 text-center text-green-600 font-medium">
                ✓ Pembayaran sudah dikonfirmasi
            </div>
        </div>
    </div>
</template>
```

### Update Test

Edit `tests/Feature/OrderFlowTest.php`:

```php
// GANTI: $response->assertSee('Budi Santoso');
// DENGAN:
$response->assertInertia(fn (Assert $page) =>
    $page->component('Public/Invoice')
         ->where('order.customer.fullname', 'Budi Santoso')
);
```

---

## Voucher Print Page — TETAP BLADE

**TIDAK diubah.** Halaman `/admin/vouchers/print` adalah new-tab standalone untuk print. Blade lebih cocok untuk ini (layout print, `@media print`, `window.print()`). Biarkan apa adanya.

File: `resources/views/voucher/print.blade.php` — **JANGAN disentuh.**

Route: `GET /admin/vouchers/print` sudah ada — **JANGAN diubah.**

---

## Welcome Page — TETAP BLADE

**TIDAK diubah.** Halaman `/` sudah diganti ke Inertia di task `00-setup.md` (`Inertia::render('Public/Welcome')`). Kalau belum:

```php
// Di routes/web.php:
Route::get('/', function () {
    return Inertia\Inertia::render('Public/Welcome');
});
```

Buat `resources/js/Pages/Public/Welcome.vue` (halaman statis sederhana):

```vue
<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100">
        <h1 class="text-3xl font-bold mb-4">PHPNuxBill</h1>
        <p class="text-gray-600 mb-6">Billing Hotspot Mikrotik</p>
        <div class="flex gap-4">
            <Link href="/admin" class="bg-amber-600 text-white px-6 py-2 rounded hover:bg-amber-700">
                Admin Panel
            </Link>
            <Link href="/customer/login" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                Customer Login
            </Link>
        </div>
    </div>
</template>
```

## Verifikasi

1. `/invoice/{token}` → tampil invoice data + QR (kalau pending) tanpa login
2. `/admin/vouchers/print?ids=1,2,3` → tampil print page (Blade, bukan Inertia)
3. `/` → tampil Welcome page Inertia
4. `php artisan test --filter=OrderFlowTest` → pass
