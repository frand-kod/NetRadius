# RULES.md — Aturan Wajib yang HARUS Dipatuhi

Baca ini SEBELUM menulis kode apapun. Setiap aturan yang dilanggar = rework.

---

## GOLDEN RULES

### 1. JANGAN Lupa Plaintext Password Customer

Password Customer (`tbl_customers.password`) adalah **PLAINTEXT**. Login customer **jangan** pakai `Hash::check()` atau `Auth::attempt()`.

```php
// BENAR:
$customer = Customer::where('username', $input['username'])
    ->where('password', $input['password'])
    ->where('status', 'Active')
    ->first();

// SALAH:
Auth::guard('customer')->attempt(['username' => ..., 'password' => ...]);
```

### 2. Admin Password Tetap Hashed

Password `User` (`tbl_users.password`) tetap hashed. Login admin bisa pakai `Auth::attempt()`.

### 3. Dua Guard Berbeda

- Admin → guard `web`, model `User`
- Customer → guard `customer`, model `Customer`

Jangan campur. Middleware `auth:web` ≠ `auth:customer`.

### 4. Jangan Sentuh Backend Service

File-file ini **panggil saja, jangan diubah**:
- `app/Services/RechargeService.php`
- `app/Services/OrderService.php`
- `app/Services/VoucherService.php`
- `app/Services/ReportService.php`
- `app/Services/PasswordResetOtpService.php`
- `app/Services/NotificationService.php`
- `app/Services/ActivityLogger.php`
- `app/Services/Hotspot/*.php`

Kalau perlu fungsionalitas baru yang tidak ada → tanya. Jangan modifikasi.

### 5. Route Inertia PASTI Return Inertia Response

```php
// BENAR:
use Inertia\Inertia;
return Inertia::render('Admin/Customer/Index', ['customers' => $customers]);

// SALAH:
return view('customer.login');  // masih Blade
```

### 6. CSRF Token untuk POST

Semua form POST harus ada CSRF token. Inertia otomatis handle ini, tapi kalau pakai plain fetch/axios, tambahkan:
```js
// di app.js
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
```

### 7. Tailwind v4

Sudah terinstall Tailwind v4. Tidak perlu install ulang. Gunakan class utility Tailwind langsung. Tidak ada `tailwind.config.js` (Tailwind v4 pakai CSS-based config).

### 8. Tidak Ada Library JS Tambahan Tanpa Izin

Gunakan **hanya** Vue 3 + Inertia.js + Tailwind. JANGAN install Vuetify, PrimeVue, TanStack Table, dsb kecuali disebutkan di task file.

### 9. Nama Props Inertia = camelCase

```php
// Controller:
return Inertia::render('Admin/Customer/Index', [
    'customers' => Customer::paginate(10),
    'totalActive' => Customer::where('status', 'Active')->count(),
]);

// Vue:
defineProps({ customers: Object, totalActive: Number });
```

### 10. Flash Messages via Session

```php
// Controller:
return redirect()->back()->with('success', 'Customer berhasil dibuat');

// Vue (tampilkan dari $page.props.flash):
const { props } = usePage();
watch(() => props.flash?.success, (msg) => { if (msg) showToast(msg); });
```

### 11. Filter Query String via Inertia Preserve

Saat filter/search, gunakan `router.get(..., {}, { preserveState: true, replace: true })` supaya state tidak di-reset.

## DOs ✅

- [ ] Baca skema migration untuk field lengkap SEBELUM buat form
- [ ] Gunakan `<script setup>` Composition API
- [ ] Pisahkan halaman per resource ke folder terpisah
- [ ] Komponen tabel dibuat reusable (`Components/DataTable.vue`)
- [ ] Tes manual setelah selesai per task: buka halaman, isi form, submit, lihat hasil
- [ ] Perhatikan label dalam Bahasa Indonesia (sesuai kode asli)
- [ ] Gunakan `defineProps` untuk menerima data dari Inertia
- [ ] Gunakan `router.visit()` untuk navigasi Inertia (bukan `window.location`)

## DON'Ts ❌

- [ ] JANGAN hash password Customer
- [ ] JANGAN ubah backend services
- [ ] JANGAN install npm package baru
- [ ] JANGAN hapus file sebelum semua task selesai (kecuali task `99-cleanup.md`)
- [ ] JANGAN buat API endpoint JSON baru untuk data yang sudah bisa via Inertia props
- [ ] JANGAN pakai `wire:submit` atau Livewire directive (itu Filament)
- [ ] JANGAN pakai `$this->form` atau Filament form component
- [ ] JANGAN render Blade view dari controller yang seharusnya Inertia

## Referensi Cepat

### Model names
| Logical | Model | Table | Guard |
|---------|-------|-------|-------|
| Admin | `User` | `tbl_users` | `web` |
| Customer | `Customer` | `tbl_customers` | `customer` |
| Plan | `Plan` | `tbl_plans` | — |
| Order | `Order` | `tbl_orders` | — |
| Voucher | `Voucher` | `tbl_voucher` | — |
| Router | `Router` | `tbl_routers` | — |
| Bandwidth | `Bandwidth` | `tbl_bandwidth` | — |
| Transaction | `Transaction` | `tbl_transactions` | — |
| AppConfig | `AppConfig` | `tbl_appconfig` | — |

### Service names (panggil via DI atau app())
| Service | Purpose |
|---------|---------|
| `RechargeService::recharge()` | Activate plan for customer |
| `OrderService::create()` | Create pending order |
| `OrderService::markAsPaid()` | Approve order → call RechargeService |
| `OrderService::cancel()` | Cancel order |
| `VoucherService::generate()` | Generate N voucher codes |
| `ReportService::incomeByDay()` | Aggregated income per day |
| `PasswordResetOtpService::requestOtp()` | Send OTP via WhatsApp |
| `PasswordResetOtpService::verifyAndReset()` | Verify OTP + reset password |
| `NotificationService::sendTelegram()` | Send Telegram message |
| `NotificationService::sendWhatsapp()` | Send WhatsApp message via GOWA |
| `ActivityLogger::log()` | Write activity log to tbl_logs |
