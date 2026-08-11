# Task 01 — Admin Authentication (Login + Logout + Forgot Password)

**Tujuan**: Ganti Filament login page ke Inertia Vue SFC. Admin login menggunakan guard `web`, model `User`, password **hashed**.

**Waktu estimasi**: 1 jam

**Dependensi**: `00-setup.md` selesai

---

## Skema DB

Tabel `tbl_users`:
```
id, root, photo, username (unique), fullname, password (HASHED),
phone, email, city, subdistrict, ward, user_type (enum),
status (enum), data, last_login, login_token, remember_token, creationdate
```

Admin access gate: `User->status === 'Active'`

---

## Step 1: Admin Login Controller

Buat `app/Http/Controllers/Admin/AuthController.php`:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLogin(): \Inertia\Response
    {
        return Inertia::render('Admin/Auth/Login');
    }

    public function login(Request $request): \Illuminate\Http\RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::guard('web')->attempt($credentials)) {
            return back()->withErrors(['username' => 'Username atau password salah.'])->onlyInput('username');
        }

        $user = Auth::guard('web')->user();

        if ($user->status !== 'Active') {
            Auth::guard('web')->logout();
            return back()->withErrors(['username' => 'Akun tidak aktif. Hubungi administrator.'])->onlyInput('username');
        }

        $request->session()->regenerate();

        return redirect()->intended('/admin');
    }

    public function logout(Request $request): \Illuminate\Http\RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}
```

## Step 2: Admin Forgot Password Controller

**PENTING**: Admin forgot-password sudah ada di `app/Http/Controllers/Admin/ForgotPasswordController.php`. TIDAK perlu buat ulang service-nya. Cukup ganti return view jadi Inertia.

Edit file `app/Http/Controllers/Admin/ForgotPasswordController.php`:

```php
// GANTI semua:
return view('admin.forgot-password', [...]);
// DENGAN:
return Inertia::render('Admin/Auth/ForgotPassword', [...]);
```

JANGAN ubah logic-nya. Cuma ganti return.

## Step 3: Routes

Di `routes/web.php`, tambahkan route admin di section admin auth. **JANGAN konflik dengan routing Filament yang masih ada** — untuk sekarang, daftarkan di `/admin/auth/*` (beda prefix) supaya tidak bentrok:

```php
use App\Http\Controllers\Admin\AuthController as AdminAuthController;

// Admin auth (Vue+Inertia — di luar prefix /admin milik Filament untuk sekarang)
Route::prefix('admin/auth')->name('admin.auth.')->group(function () {
    Route::middleware('guest:web')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    });
    Route::middleware('auth:web')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
    });
});
```

> **Catatan**: Nanti di task `99-cleanup.md`, route admin akan dipindahkan ke `/admin` (menggantikan routing Filament sepenuhnya).

Admin forgot-password SUDAH ada di web.php (prefix `/admin-forgot-password`). TIDAK perlu tambah route baru.

## Step 4: Vue Pages

### Login Page

Buat `resources/js/Pages/Admin/Auth/Login.vue`:

```vue
<script setup>
import { useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const form = useForm({
    username: '',
    password: '',
});

function submit() {
    form.post(route('admin.auth.login.submit'));
}
</script>

<template>
    <GuestLayout>
        <h1 class="text-xl font-bold mb-4 text-center">Admin Login</h1>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label class="block text-sm font-medium">Username</label>
                <input v-model="form.username" type="text" required autofocus
                       class="mt-1 block w-full rounded border border-gray-300 px-3 py-2" />
                <p v-if="form.errors.username" class="text-red-500 text-sm mt-1">{{ form.errors.username }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium">Password</label>
                <input v-model="form.password" type="password" required
                       class="mt-1 block w-full rounded border border-gray-300 px-3 py-2" />
            </div>

            <button type="submit" :disabled="form.processing"
                    class="w-full bg-amber-600 text-white py-2 rounded hover:bg-amber-700 disabled:opacity-50">
                Login
            </button>
        </form>

        <p class="mt-4 text-center text-sm">
            <a :href="route('admin.forgot-password.show')" class="text-amber-600 hover:underline">
                Lupa password?
            </a>
        </p>
    </GuestLayout>
</template>
```

### Forgot Password Page

Buat `resources/js/Pages/Admin/Auth/ForgotPassword.vue`:

```vue
<script setup>
import { useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const requestForm = useForm({ username: '' });
const resetForm = useForm({ username: '', otp: '' });

const props = defineProps({
    status: String,
    new_password: String,
    username: String,
});

function requestCode() {
    requestForm.post(route('admin.forgot-password.request'));
}

function resetPassword() {
    resetForm.post(route('admin.forgot-password.reset'));
}
</script>

<template>
    <GuestLayout>
        <h1 class="text-xl font-bold mb-4 text-center">Lupa Password - Admin</h1>

        <!-- Status messages -->
        <div v-if="props.status" class="bg-green-100 text-green-700 p-3 rounded mb-4">
            {{ props.status }}
        </div>
        <div v-if="props.new_password" class="bg-green-100 text-green-700 p-3 rounded mb-4">
            Password baru Anda: <strong>{{ props.new_password }}</strong>
            <br />Silakan login dengan password ini.
        </div>

        <!-- Step 1: Request OTP -->
        <form @submit.prevent="requestCode" class="space-y-4 mb-6">
            <h2 class="font-semibold">1. Minta Kode Verifikasi</h2>
            <div>
                <label class="block text-sm font-medium">Username</label>
                <input v-model="requestForm.username" type="text" required
                       class="mt-1 block w-full rounded border border-gray-300 px-3 py-2" />
            </div>
            <button type="submit" :disabled="requestForm.processing"
                    class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 disabled:opacity-50">
                Kirim Kode via WhatsApp
            </button>
        </form>

        <!-- Step 2: Reset Password -->
        <form @submit.prevent="resetPassword" class="space-y-4">
            <h2 class="font-semibold">2. Reset Password</h2>
            <div>
                <label class="block text-sm font-medium">Username</label>
                <input v-model="resetForm.username" type="text" required
                       class="mt-1 block w-full rounded border border-gray-300 px-3 py-2" />
            </div>
            <div>
                <label class="block text-sm font-medium">Kode Verifikasi</label>
                <input v-model="resetForm.otp" type="text" required
                       class="mt-1 block w-full rounded border border-gray-300 px-3 py-2" />
                <p v-if="resetForm.errors.otp" class="text-red-500 text-sm mt-1">{{ resetForm.errors.otp }}</p>
            </div>
            <button type="submit" :disabled="resetForm.processing"
                    class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700 disabled:opacity-50">
                Reset Password
            </button>
        </form>

        <p class="mt-4 text-center text-sm">
            <a href="/admin/auth/login" class="text-amber-600 hover:underline">Kembali ke login</a>
        </p>
    </GuestLayout>
</template>
```

## Verifikasi

1. Buka `/admin/auth/login` → tampil login form Vue
2. Login dengan username `admin` + password yang benar → redirect ke `/admin`
3. Login dengan password salah → error "Username atau password salah."
4. Buka `/admin-forgot-password` → tampil forgot password form Vue
5. `admin.forgot-password.request` → kirim OTP, tidak crash
6. `admin.forgot-password.reset` → reset password, dapat password baru

## Catatan

- Password admin di-hash (`User.password` cast `hashed`), jadi `Auth::attempt()` berfungsi normal
- Filament masih jalan di `/admin` — jangan bentrok
- Redirect ke `/admin` setelah login saat ini akan masuk ke Filament (belum masalah, nanti di task `99-cleanup.md` diganti ke dashboard Inertia)
