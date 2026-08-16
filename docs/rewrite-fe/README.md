
# Panduan Migrasi Filament → Vue 3 + Inertia.js

## Overview

Migrasi layer presentasi aplikasi billing hotspot dari **Filament v3** (admin panel) + **Blade** (customer/public) ke **Vue 3 + Inertia.js**. Backend (models, services, API, tests) **tidak berubah**. 49 PHPUnit tests harus tetap pass.

## Tech Stack Target

- **Backend**: Laravel 13.24 (no change)
- **Frontend**: Vue 3 (Composition API, `<script setup>`) + Inertia.js
- **CSS**: Tailwind CSS v4 (sudah terinstall)
- **Build**: Vite 8 (sudah terinstall)

## Struktur Baru yang Akan Dibangun

```
resources/js/
├── app.js                          # Inertia + Vue bootstrap
├── Layouts/
│   ├── AdminLayout.vue             # Sidebar + topbar untuk /admin/*
│   └── GuestLayout.vue             # Layout minimal untuk customer/public
├── Pages/
│   ├── Admin/
│   │   ├── Dashboard.vue
│   │   ├── Auth/
│   │   │   ├── Login.vue
│   │   │   └── ForgotPassword.vue
│   │   ├── Customer/
│   │   │   ├── Index.vue           # List + table
│   │   │   ├── Create.vue          # Form create
│   │   │   └── Edit.vue            # Form edit
│   │   ├── Plan/
│   │   │   ├── Index.vue
│   │   │   ├── Create.vue
│   │   │   └── Edit.vue
│   │   ├── Order/
│   │   │   ├── Index.vue
│   │   │   └── Create.vue          # No Edit (read-only after created)
│   │   ├── Voucher/
│   │   │   ├── Index.vue
│   │   │   ├── Create.vue
│   │   │   └── Edit.vue
│   │   ├── Router/
│   │   │   ├── Index.vue
│   │   │   ├── Create.vue
│   │   │   └── Edit.vue
│   │   ├── Bandwidth/
│   │   │   ├── Index.vue
│   │   │   ├── Create.vue
│   │   │   └── Edit.vue
│   │   ├── Settings/
│   │   │   ├── Payment.vue
│   │   │   └── Notification.vue
│   │   ├── IncomeReport.vue
│   │   └── VoucherPrint.vue
│   ├── Customer/
│   │   ├── Login.vue
│   │   ├── Dashboard.vue
│   │   └── ForgotPassword.vue
│   └── Public/
│       ├── Invoice.vue
│       └── Welcome.vue
├── Components/
│   ├── DataTable.vue               # Komponen tabel reusable
│   ├── StatusBadge.vue
│   ├── ConfirmModal.vue
│   └── Pagination.vue
└── Composables/
    ├── useAuth.js
    └── useToast.js
```

## Cara Membaca File Task

Setiap file task (`01-admin-auth.md`, `02-customer-resource.md`, dst.) berisi:

1. **Skema DB**: kolom tabel yang relevan
2. **Routes**: definisi Laravel route
3. **Controller**: kode PHP controller method
4. **Vue Pages**: template Vue SFC lengkap
5. **Verifikasi**: apa yang harus dicek setelah selesai

## Urutan Pengerjaan

Kerjakan **berurutan** karena ada dependensi:

1. `00-setup.md` — Install Inertia+Vue, setup layout, middleware
2. `01-admin-auth.md` — Admin login, logout, forgot password
3. `06-router-resource.md` — Router CRUD (paling simpel, pemanasan)
4. `07-bandwidth-resource.md` — Bandwidth CRUD
5. `02-customer-resource.md` — Customer CRUD
6. `03-plan-resource.md` — Plan CRUD
7. `04-order-resource.md` — Order (hanya list + create, action Mark as Paid/Cancel)
8. `05-voucher-resource.md` — Voucher (CRUD + generate + print)
9. `08-settings-pages.md` — PaymentSettings + NotificationSettings
10. `09-income-report.md` — IncomeReport
11. `10-customer-portal.md` — Customer login/dashboard/forgot-password
12. `11-public-pages.md` — Invoice publik, voucher print, welcome
13. `99-cleanup.md` — Remove Filament, final verification
14. `12-styling-system.md` — Kamus desain & recipe kelas (BACA DULU)
15. `13-styling-pages.md` — Terapkan styling ke semua halaman (find-replace + checklist)
16. `14-dashboard.md` — Dashboard modern: KPI + chart SVG (customer, pendapatan, user online, voucher)
17. `15-settings-pages.md` — Tampilan halaman Settings (General/Payment/Notification) + link sidebar

## Konvensi Kode

- Vue: Composition API dengan `<script setup>`, `<template>`, `<style scoped>`
- Nama route Inertia: `Admin/Customer/Index`, `Admin/Plan/Create`, `Customer/Dashboard`, dll.
- Props Inertia di-camelCase: `customers`, `plans`, `filters`
- Form submission via Inertia `useForm()` atau direct POST (untuk logout)
- Notifikasi: Laravel flash session (`session()->flash('success', '...')`) → ditangkap di frontend via `$page.props.flash`
- Validasi: server-side di Laravel (via FormRequest atau direct validate), error ditampilkan via `$page.props.errors`

## Konstanta Penting

- **Admin guard**: `web`, provider `users`, model `User` (table `tbl_users`)
- **Customer guard**: `customer`, provider `customers`, model `Customer` (table `tbl_customers`)
- **Password Customer**: PLAINTEXT — jangan pakai Hash::check()
- **Password Admin**: hashed — pakai Hash::check() atau Auth::attempt()
- **Filament guard**: Filament menggunakan guard `web` (default Laravel)
- **Route prefix admin**: `/admin`
- **Route prefix customer**: `/customer`
- **Admin path di laravel**: Filament auto-gen routes di `/admin`
