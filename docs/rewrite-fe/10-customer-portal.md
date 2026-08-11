# Task 10 — Customer Portal (Login, Dashboard, Forgot Password)

**Tujuan**: Ganti Blade views customer portal ke Vue Inertia. Controller SUDAH ADA — hanya ganti return type dari `view()` ke `Inertia::render()`.

**Dependensi**: `00-setup.md`

**Waktu estimasi**: 45 menit

---

## ⚠️ File yang SUDAH ADA — JANGAN buat ulang controller

Controller di bawah ini SUDAH bekerja. HANYA ganti method yang return view:

- `app/Http/Controllers/Customer/AuthController.php` — method `showLogin()`: ganti `view('customer.login')` ke `Inertia::render('Customer/Login')`
- `app/Http/Controllers/Customer/DashboardController.php` — method `show()`: ganti `view('customer.dashboard', ...)` ke `Inertia::render('Customer/Dashboard', ...)`
- `app/Http/Controllers/Customer/ForgotPasswordController.php` — method `show()`: ganti `view('customer.forgot-password')` ke `Inertia::render('Customer/ForgotPassword')`

**JANGAN ubah logic POST/PUT handler** — login, logout, forgot-password request/reset TETAP return RedirectResponse.

---

## Step 1: Edit AuthController@showLogin

Di `app/Http/Controllers/Customer/AuthController.php`:

```php
use Inertia\Inertia;

public function showLogin(): \Inertia\Response
{
    return Inertia::render('Customer/Login');
}
```

Method `login()` dan `logout()` TIDAK DIUBAH.

## Step 2: Edit DashboardController@show

Di `app/Http/Controllers/Customer/DashboardController.php`:

```php
use Inertia\Inertia;

public function show(): \Inertia\Response
{
    $customer = Auth::guard('customer')->user();
    $orders = Order::where('customer_id', $customer->id)->latest('id')->get();
    $transactions = Transaction::where('user_id', $customer->id)->latest('id')->get();

    return Inertia::render('Customer/Dashboard', [
        'customer' => $customer,
        'orders' => $orders->load('plan'),
        'transactions' => $transactions,
    ]);
}
```

## Step 3: Edit ForgotPasswordController@show

Di `app/Http/Controllers/Customer/ForgotPasswordController.php`:

```php
use Inertia\Inertia;

public function show(): \Inertia\Response
{
    return Inertia::render('Customer/ForgotPassword');
}
```

Method `requestCode()` dan `reset()` TIDAK DIUBAH.

---

## Step 4: Vue Pages

### Login — `resources/js/Pages/Customer/Login.vue`

```vue
<script setup>
import { useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const form = useForm({
    username: '',
    password: '',
});

function submit() {
    form.post('/customer/login');
}
</script>

<template>
    <GuestLayout>
        <h1 class="text-xl font-bold mb-4 text-center">Login Pelanggan</h1>

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
                    class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 disabled:opacity-50">
                Login
            </button>
        </form>

        <p class="mt-4 text-center text-sm">
            <a :href="route('customer.forgot-password.show')" class="text-blue-600 hover:underline">
                Lupa password?
            </a>
        </p>
    </GuestLayout>
</template>
```

### Dashboard — `resources/js/Pages/Customer/Dashboard.vue`

```vue
<script setup>
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    customer: Object,
    orders: Array,
    transactions: Array,
});

const logoutForm = useForm({});

function logout() {
    logoutForm.post('/customer/logout');
}
</script>

<template>
    <div class="min-h-screen bg-gray-100 p-6">
        <div class="max-w-2xl mx-auto">
            <!-- Header -->
            <div class="bg-white rounded shadow p-4 mb-4 flex justify-between items-center">
                <div>
                    <h1 class="text-xl font-bold">Halo, {{ customer.fullname }}</h1>
                    <p class="text-sm text-gray-500">Status: {{ customer.status }}</p>
                </div>
                <button @click="logout" :disabled="logoutForm.processing"
                        class="text-red-600 hover:underline text-sm">Logout</button>
            </div>

            <!-- Orders -->
            <div class="bg-white rounded shadow p-4 mb-4">
                <h2 class="font-semibold mb-2">Riwayat Order</h2>
                <ul class="space-y-2">
                    <li v-for="order in orders" :key="order.id" class="text-sm border-b pb-2">
                        <span class="font-medium">{{ order.plan?.name_plan }}</span>
                        — Rp {{ Number(order.price).toLocaleString('id-ID') }}
                        — <span :class="{
                            'text-amber-600': order.status === 'pending',
                            'text-green-600': order.status === 'paid',
                            'text-red-600': order.status === 'cancelled',
                        }">{{ order.status }}</span>
                        <a :href="`/invoice/${order.invoice_token}`"
                           class="text-blue-600 hover:underline ml-2 text-xs">Lihat Invoice</a>
                    </li>
                    <li v-if="orders.length === 0" class="text-gray-500 text-sm">Belum ada order.</li>
                </ul>
            </div>

            <!-- Transactions -->
            <div class="bg-white rounded shadow p-4">
                <h2 class="font-semibold mb-2">Riwayat Transaksi</h2>
                <ul class="space-y-2">
                    <li v-for="tx in transactions" :key="tx.id" class="text-sm border-b pb-2">
                        {{ tx.plan_name }}
                        — Rp {{ Number(tx.price).toLocaleString('id-ID') }}
                        — {{ tx.recharged_on }}
                    </li>
                    <li v-if="transactions.length === 0" class="text-gray-500 text-sm">Belum ada transaksi.</li>
                </ul>
            </div>
        </div>
    </div>
</template>
```

### Forgot Password — `resources/js/Pages/Customer/ForgotPassword.vue`

```vue
<script setup>
import { useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const requestForm = useForm({ username: '' });
const resetForm = useForm({ username: '', otp: '' });
</script>

<template>
    <GuestLayout>
        <h1 class="text-xl font-bold mb-4 text-center">Lupa Password</h1>

        <!-- Status -->
        <div v-if="$page.props.flash?.status" class="bg-green-100 text-green-700 p-3 rounded mb-4">
            {{ $page.props.flash.status }}
        </div>
        <div v-if="$page.props.flash?.new_password" class="bg-green-100 text-green-700 p-3 rounded mb-4">
            Password baru Anda: <strong>{{ $page.props.flash.new_password }}</strong>
            <br />Silakan login dengan password ini.
        </div>

        <!-- Step 1: Request Code -->
        <form @submit.prevent="() => requestForm.post('/customer/forgot-password/request')" class="space-y-4 mb-6">
            <h2 class="font-semibold">1. Minta Kode Verifikasi</h2>
            <div>
                <label class="block text-sm font-medium">Username</label>
                <input v-model="requestForm.username" type="text" required
                       class="mt-1 block w-full rounded border px-3 py-2" />
            </div>
            <button type="submit" :disabled="requestForm.processing"
                    class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 disabled:opacity-50">
                Kirim Kode via WhatsApp
            </button>
        </form>

        <!-- Step 2: Reset Password -->
        <form @submit.prevent="() => resetForm.post('/customer/forgot-password/reset')" class="space-y-4">
            <h2 class="font-semibold">2. Reset Password</h2>
            <div>
                <label class="block text-sm font-medium">Username</label>
                <input v-model="resetForm.username" type="text" required
                       class="mt-1 block w-full rounded border px-3 py-2" />
            </div>
            <div>
                <label class="block text-sm font-medium">Kode Verifikasi</label>
                <input v-model="resetForm.otp" type="text" required
                       class="mt-1 block w-full rounded border px-3 py-2" />
                <p v-if="resetForm.errors.otp" class="text-red-500 text-sm mt-1">{{ resetForm.errors.otp }}</p>
            </div>
            <button type="submit" :disabled="resetForm.processing"
                    class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700 disabled:opacity-50">
                Reset Password
            </button>
        </form>

        <p class="mt-4 text-center text-sm">
            <a :href="route('customer.login')" class="text-blue-600 hover:underline">Kembali ke login</a>
        </p>
    </GuestLayout>
</template>
```

## Step 5: Update Test

Edit `tests/Feature/CustomerAuthTest.php`:

```php
// GANTI: $response->assertSee($customer->fullname);
// DENGAN:
$response->assertInertia(fn (Assert $page) =>
    $page->component('Customer/Dashboard')
         ->where('customer.fullname', $customer->fullname)
);
```

## Verifikasi

1. `/customer/login` → tampil login form Vue
2. Login → redirect ke `/customer/dashboard` dengan data customer + orders + transactions
3. `/customer/forgot-password` → flow OTP bekerja
4. `php artisan test --filter=CustomerAuthTest` → pass
