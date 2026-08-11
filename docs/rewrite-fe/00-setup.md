# Task 00 — Setup Vue 3 + Inertia.js

**Tujuan**: Install Inertia.js + Vue 3, setup Vite, middleware, layout dasar.

**Waktu estimasi**: 30-45 menit

---

## Step 1: Install Dependencies

```bash
composer require inertiajs/inertia-laravel
npm install @inertiajs/vue3 vue@latest
```

## Step 2: Create Inertia Middleware

Buat file `app/Http/Middleware/HandleInertiaRequests.php`:

```php
<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ]);
    }
}
```

## Step 3: Register Middleware

Di `bootstrap/app.php`, tambahkan di bagian `->withMiddleware()`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \App\Http\Middleware\HandleInertiaRequests::class,
    ]);
})
```

File lengkap `bootstrap/app.php` setelah edit:

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

PENTING: JANGAN hapus `redirectGuestsTo()` kalau sudah ada. Lihat file `bootstrap/app.php` saat ini sebelum edit.

## Step 4: Create App Root Blade

Buat `resources/views/app.blade.php`:

```html
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title inertia>{{ config('app.name', 'PHPNuxBill') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>
<body class="bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100">
    @inertia
</body>
</html>
```

## Step 5: Edit resources/js/app.js

Ganti isi `resources/js/app.js`:

```js
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

createInertiaApp({
    title: (title) => title ? `${title} - PHPNuxBill` : 'PHPNuxBill',
    resolve: (name) => resolvePageComponent(
        `./Pages/${name}.vue`,
        import.meta.glob('./Pages/**/*.vue')
    ),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
});
```

## Step 6: Update vite.config.js

Ganti `vite.config.js`:

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        tailwindcss(),
    ],
});
```

## Step 7: Install Vue Vite Plugin

```bash
npm install -D @vitejs/plugin-vue
```

## Step 8: Remove Old Welcome Route

Di `routes/web.php`, ganti route `/` yang lama:

```php
// GANTI: Route::get('/', function () { return view('welcome'); });
// DENGAN:
Route::get('/', function () {
    return Inertia\Inertia::render('Public/Welcome');
});
```

## Step 9: Create Test Page

Buat `resources/js/Pages/Public/Welcome.vue`:

```vue
<script setup>
</script>

<template>
    <div class="flex min-h-screen items-center justify-center">
        <h1 class="text-3xl font-bold">PHPNuxBill - Vue + Inertia Ready</h1>
    </div>
</template>
```

## Step 10: Build & Test

```bash
npm run build
# atau untuk development:
npm run dev
```

Buka browser ke URL aplikasi—harus tampil "PHPNuxBill - Vue + Inertia Ready".

## Step 11: Create Layout Components

### AdminLayout

Buat `resources/js/Layouts/AdminLayout.vue`:

```vue
<script setup>
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
</script>

<template>
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-800 text-white p-4">
            <h2 class="text-xl font-bold mb-6">PHPNuxBill</h2>
            <nav class="space-y-1">
                <Link href="/admin" class="block px-3 py-2 rounded hover:bg-gray-700"
                      :class="{ 'bg-gray-700': page.url === '/admin' }">
                    Dashboard
                </Link>
                <Link href="/admin/customers" class="block px-3 py-2 rounded hover:bg-gray-700"
                      :class="{ 'bg-gray-700': page.url.startsWith('/admin/customers') }">
                    Customers
                </Link>
                <Link href="/admin/plans" class="block px-3 py-2 rounded hover:bg-gray-700"
                      :class="{ 'bg-gray-700': page.url.startsWith('/admin/plans') }">
                    Plans
                </Link>
                <Link href="/admin/orders" class="block px-3 py-2 rounded hover:bg-gray-700"
                      :class="{ 'bg-gray-700': page.url.startsWith('/admin/orders') }">
                    Orders
                </Link>
                <Link href="/admin/vouchers" class="block px-3 py-2 rounded hover:bg-gray-700"
                      :class="{ 'bg-gray-700': page.url.startsWith('/admin/vouchers') }">
                    Vouchers
                </Link>
                <Link href="/admin/routers" class="block px-3 py-2 rounded hover:bg-gray-700"
                      :class="{ 'bg-gray-700': page.url.startsWith('/admin/routers') }">
                    Routers
                </Link>
                <Link href="/admin/bandwidths" class="block px-3 py-2 rounded hover:bg-gray-700"
                      :class="{ 'bg-gray-700': page.url.startsWith('/admin/bandwidths') }">
                    Bandwidth
                </Link>
                <Link href="/admin/payment-settings" class="block px-3 py-2 rounded hover:bg-gray-700"
                      :class="{ 'bg-gray-700': page.url.startsWith('/admin/payment-settings') }">
                    Payment Settings
                </Link>
                <Link href="/admin/notification-settings" class="block px-3 py-2 rounded hover:bg-gray-700"
                      :class="{ 'bg-gray-700': page.url.startsWith('/admin/notification-settings') }">
                    Notification Settings
                </Link>
                <Link href="/admin/income-report" class="block px-3 py-2 rounded hover:bg-gray-700"
                      :class="{ 'bg-gray-700': page.url.startsWith('/admin/income-report') }">
                    Income Report
                </Link>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6">
            <header class="mb-6 flex justify-between items-center">
                <h1 class="text-2xl font-semibold">
                    <slot name="title">Dashboard</slot>
                </h1>
                <div class="flex items-center gap-4">
                    <span>{{ page.props.auth?.user?.fullname }}</span>
                    <Link href="/admin/logout" method="post" as="button"
                          class="text-red-600 hover:underline">Logout</Link>
                </div>
            </header>
            <slot />
        </main>
    </div>
</template>
```

### GuestLayout

Buat `resources/js/Layouts/GuestLayout.vue`:

```vue
<script setup>
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-gray-100">
        <div class="w-full max-w-md bg-white rounded-lg shadow p-6">
            <slot />
        </div>
    </div>
</template>
```

## Verifikasi

1. `npm run build` sukses tanpa error
2. Buka `/` di browser → tampil "PHPNuxBill - Vue + Inertia Ready"
3. `resources/js/Pages/Public/Welcome.vue` ada
4. `resources/js/Layouts/AdminLayout.vue` ada
5. `resources/js/Layouts/GuestLayout.vue` ada
6. `resources/views/app.blade.php` ada
7. `app/Http/Middleware/HandleInertiaRequests.php` ada

## Catatan

- **JANGAN hapus Blade view yang sudah ada dulu** — mereka masih dipakai sampai semua task selesai
- **JANGAN ubah `routes/api.php`** — endpoint RADIUS harus tetap jalan
- **JANGAN ubah `config/auth.php`** — guard sudah benar
- Tailwind v4 sudah jalan, tidak perlu setup tambahan
