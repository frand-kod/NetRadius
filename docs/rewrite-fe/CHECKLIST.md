# CHECKLIST.md — Progress Tracker

## Setup
- [x] 00-setup — Install Inertia+Vue, Vite config, middleware, layout, app.js

## Admin Panel
- [x] 01-admin-auth — Login, logout, forgot password
- [x] 06-router-resource — Router CRUD (sederhana, pemanasan)
- [x] 07-bandwidth-resource — Bandwidth CRUD
- [x] 02-customer-resource — Customer CRUD
- [x] 03-plan-resource — Plan CRUD
- [x] 04-order-resource — Order list + create + Mark as Paid + Cancel
- [x] 05-voucher-resource — Voucher CRUD + Generate + Print
- [x] 08-settings-pages — PaymentSettings + NotificationSettings
- [x] 09-income-report — IncomeReport page

## Customer Portal
- [x] 10-customer-portal — Login, dashboard, forgot password

## Public Pages
- [x] 11-public-pages — Invoice, voucher print, welcome

## Styling (disiapkan untuk Gemini Flash Lite)
- [ ] 12-styling-system — Kamus desain & recipe kelas (sudah dibuat)
- [ ] 13-styling-pages — Terapkan find-replace global + checklist per file (sudah dibuat)
- [ ] 14-dashboard — Dashboard modern: KPI + BarChart/LineChart/DonutChart SVG + user online (sudah dibuat)

## Settings Backend (SUDAH SELESAI — hanya tampilan yang di-delegasikan)
- [x] Backend: GeneralSettingsController + PaymentSettingsController (instruksi + QR opsional) + shared setting global + route general + seeder default
- [ ] 15-settings-pages — Tampilan 3 halaman Settings (General/Payment/Notification) + link sidebar (sudah dibuat, untuk Flash Lite)

## Cleanup
- [x] 99-cleanup — Remove Filament (dilakukan lebih awal karena konflik route), run full test suite
- [x] npm run build — sukses
- [x] 52 PHPUnit test pass

## Verifikasi Final
- [x] `php artisan test --compact` → **52 tests, 52 passed**
- [x] `npm run build` → clean
- [x] 3 commit: setup+CRUD dasar, CRUD lanjutan, portal+publik+cleanup
