# Task 08 — Settings Pages (Payment + Notification)

**Tujuan**: Dua halaman settings yang simpan ke `tbl_appconfig`. Ganti dari Filament custom pages ke Vue.

**Dependensi**: `00-setup.md`, `01-admin-auth.md`

**Waktu estimasi**: 45 menit

---

## Payment Settings

Halaman upload satu gambar QR statis.

### Controller

Buat `app/Http/Controllers/Admin/PaymentSettingsController.php`:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppConfig;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentSettingsController extends Controller
{
    public function edit(): \Inertia\Response
    {
        return Inertia::render('Admin/Settings/Payment', [
            'qrPath' => AppConfig::get('payment_qr_path'),
        ]);
    }

    public function update(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'payment_qr' => ['required', 'image', 'max:2048'],
        ]);

        $path = $request->file('payment_qr')->store('payment-qr', 'public');
        AppConfig::set('payment_qr_path', $path);

        return back()->with('success', 'QR pembayaran disimpan.');
    }
}
```

### Route

```php
use App\Http\Controllers\Admin\PaymentSettingsController;

Route::middleware('auth:web')->prefix('admin/settings/payment')->name('admin.settings.payment.')->group(function () {
    Route::get('/', [PaymentSettingsController::class, 'edit'])->name('edit');
    Route::post('/', [PaymentSettingsController::class, 'update'])->name('update');
});
```

### Vue Page — `resources/js/Pages/Admin/Settings/Payment.vue`

```vue
<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ qrPath: String });

const form = useForm({
    payment_qr: null,
});

function submit() {
    form.post('/admin/settings/payment', {
        forceFormData: true, // PENTING untuk file upload via Inertia
    });
}
</script>

<template>
    <AdminLayout>
        <template #title>Payment Settings</template>

        <div class="bg-white rounded shadow p-6 max-w-lg space-y-4">
            <!-- Current QR -->
            <div>
                <h3 class="text-sm font-medium mb-2">QR Saat Ini</h3>
                <img v-if="qrPath" :src="`/storage/${qrPath}`" alt="QR" class="w-48 rounded border" />
                <p v-else class="text-gray-500">Belum ada QR yang diupload.</p>
            </div>

            <!-- Upload Form -->
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium">Upload QR Baru</label>
                    <input type="file" accept="image/*" @input="form.payment_qr = $event.target.files[0]"
                           class="mt-1 block w-full" />
                    <p v-if="form.errors.payment_qr" class="text-red-500 text-sm mt-1">{{ form.errors.payment_qr }}</p>
                </div>
                <button type="submit" :disabled="form.processing"
                        class="bg-amber-600 text-white px-6 py-2 rounded hover:bg-amber-700 disabled:opacity-50">
                    Simpan
                </button>
            </form>
        </div>
    </AdminLayout>
</template>
```

---

## Notification Settings

Halaman 7 field untuk Telegram + GOWA WhatsApp.

### Controller

Buat `app/Http/Controllers/Admin/NotificationSettingsController.php`:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppConfig;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationSettingsController extends Controller
{
    private const KEYS = [
        'telegram_bot',
        'telegram_target_id',
        'alt_wga_server_url',
        'alt_wga_device_id',
        'alt_wga_username',
        'alt_wga_password',
        'country_code_phone',
    ];

    public function edit(): \Inertia\Response
    {
        $settings = [];
        foreach (self::KEYS as $key) {
            $settings[$key] = AppConfig::get($key);
        }

        return Inertia::render('Admin/Settings/Notification', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'telegram_bot' => ['nullable', 'string'],
            'telegram_target_id' => ['nullable', 'string'],
            'alt_wga_server_url' => ['nullable', 'string'],
            'alt_wga_device_id' => ['nullable', 'string'],
            'alt_wga_username' => ['nullable', 'string'],
            'alt_wga_password' => ['nullable', 'string'],
            'country_code_phone' => ['nullable', 'string'],
        ]);

        foreach (self::KEYS as $key) {
            AppConfig::set($key, $data[$key] ?? '');
        }

        return back()->with('success', 'Pengaturan notifikasi disimpan.');
    }
}
```

### Route

```php
use App\Http\Controllers\Admin\NotificationSettingsController;

Route::middleware('auth:web')->prefix('admin/settings/notification')->name('admin.settings.notification.')->group(function () {
    Route::get('/', [NotificationSettingsController::class, 'edit'])->name('edit');
    Route::post('/', [NotificationSettingsController::class, 'update'])->name('update');
});
```

### Vue Page — `resources/js/Pages/Admin/Settings/Notification.vue`

```vue
<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ settings: Object });

const form = useForm({
    telegram_bot: props.settings.telegram_bot || '',
    telegram_target_id: props.settings.telegram_target_id || '',
    alt_wga_server_url: props.settings.alt_wga_server_url || '',
    alt_wga_device_id: props.settings.alt_wga_device_id || '',
    alt_wga_username: props.settings.alt_wga_username || '',
    alt_wga_password: '',
    country_code_phone: props.settings.country_code_phone || '62',
});

function submit() { form.post('/admin/settings/notification'); }
</script>

<template>
    <AdminLayout>
        <template #title>Notification Settings</template>

        <form @submit.prevent="submit" class="bg-white rounded shadow p-6 max-w-lg space-y-4">
            <div>
                <label class="block text-sm font-medium">Telegram Bot Token</label>
                <input v-model="form.telegram_bot" class="mt-1 block w-full rounded border px-3 py-2" />
                <p class="text-xs text-gray-500 mt-1">Token dari @BotFather.</p>
            </div>
            <div>
                <label class="block text-sm font-medium">Telegram Chat ID (admin)</label>
                <input v-model="form.telegram_target_id" class="mt-1 block w-full rounded border px-3 py-2" />
            </div>
            <div>
                <label class="block text-sm font-medium">GOWA Server URL</label>
                <input v-model="form.alt_wga_server_url" class="mt-1 block w-full rounded border px-3 py-2" />
                <p class="text-xs text-gray-500 mt-1">Contoh: http://127.0.0.1:3030</p>
            </div>
            <div>
                <label class="block text-sm font-medium">GOWA Device ID</label>
                <input v-model="form.alt_wga_device_id" class="mt-1 block w-full rounded border px-3 py-2" />
            </div>
            <div>
                <label class="block text-sm font-medium">GOWA Basic Auth Username</label>
                <input v-model="form.alt_wga_username" class="mt-1 block w-full rounded border px-3 py-2" />
            </div>
            <div>
                <label class="block text-sm font-medium">GOWA Basic Auth Password</label>
                <input v-model="form.alt_wga_password" type="password" class="mt-1 block w-full rounded border px-3 py-2" />
            </div>
            <div>
                <label class="block text-sm font-medium">Kode Negara Telepon</label>
                <input v-model="form.country_code_phone" class="mt-1 block w-full rounded border px-3 py-2" />
                <p class="text-xs text-gray-500 mt-1">Dipakai mengganti 0 pada nomor pelanggan. Default: 62.</p>
            </div>
            <button type="submit" :disabled="form.processing"
                    class="bg-amber-600 text-white px-6 py-2 rounded hover:bg-amber-700 disabled:opacity-50">
                Simpan
            </button>
        </form>
    </AdminLayout>
</template>
```

## Verifikasi

1. `/admin/settings/payment` → upload QR → gambar tersimpan, tampil di halaman
2. `/admin/settings/notification` → isi semua field → submit → redirect, data terisi
3. Refresh → data tetap tampil (AppConfig persisten)
