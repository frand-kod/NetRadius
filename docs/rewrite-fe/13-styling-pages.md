# 13-styling-pages.md — Terapkan Sistem Desain ke SEMUA Halaman

Kerjakan SETELAH membaca `12-styling-system.md`. Tujuan: semua halaman memakai kelas yang konsisten.

**Cara kerja:** ada 2 langkah.
1. **LANGKAH A — Find & Replace Global** (§2): string "lama" yang sama muncul di banyak file. Ganti persis ke string "baru".
2. **LANGKAH B — Checklist per file** (§3): buka tiap file, pastikan semua elemen sudah benar, perbaiki yang masih menyimpang.

> Ganti SEMUA kemunculan, tidak hanya satu. Gunakan "Replace All" di editor. Jangan sentuh `<script setup>` atau logika — hanya `class=`, `:class=`, dan struktur `<template>`.

---

## 1. Rule Peran Elemen (kalau ragu, ikuti ini)

| Elemen | Recipe |
|--------|--------|
| Kartu putih / wrapper tabel | R1 `rounded-xl border border-gray-200 bg-white shadow-sm` |
| Tombol aksi utama | R2 (amber) |
| Tombol batal/kembali | R3 (outline) |
| Tombol hapus/logout | R4 (merah) |
| Input/select/textarea | R6 |
| Label form | R7 |
| Error validasi | R8 |
| Tabel: wrapper/table/thead/tbody/td | R9–R12 |
| Badge status | R13 + warna §3 |
| Bilah aksi (tombol + search) | R14 + R15 |
| Pagination | R18 |

---

## 2. LANGKAH A — Tabel Find & Replace Global

Buka **setiap file** di `resources/js/Pages/` (Admin, Customer, Public) + kedua Layout. Ganti string kiri → kanan.

### 2a. Wrapper tabel & kartu
| OLD (persis) | NEW |
|--------------|-----|
| `class="bg-white rounded shadow overflow-hidden"` | `class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"` |
| `class="bg-white rounded shadow overflow-x-auto"` | `class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm"` |
| `class="bg-white rounded shadow p-4"` | `class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm"` |
| `class="bg-white rounded shadow p-6 max-w-3xl space-y-4"` | `class="max-w-3xl space-y-5 rounded-xl border border-gray-200 bg-white p-6 shadow-sm"` |
| `class="bg-white rounded shadow p-6 max-w-lg space-y-4"` | `class="max-w-lg space-y-5 rounded-xl border border-gray-200 bg-white p-6 shadow-sm"` |
| `class="bg-white rounded shadow p-4 mb-6 flex gap-4 items-end"` | `class="mb-6 flex items-end gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm"` |
| `class="bg-white rounded shadow"` (lainnya) | `class="rounded-xl border border-gray-200 bg-white shadow-sm"` |

### 2b. Tombol tambah/aksi utama (amber, besar)
| OLD | NEW |
|-----|-----|
| `class="bg-amber-600 text-white px-4 py-2 rounded hover:bg-amber-700"` | `class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500/40"` |
| `class="bg-amber-600 text-white px-6 py-2 rounded hover:bg-amber-700 disabled:opacity-50"` | `class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500/40 disabled:cursor-not-allowed disabled:opacity-60"` |

### 2c. Tombol biru income-report → ganti ke amber (inkonsisten!)
| OLD | NEW |
|-----|-----|
| `class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700"` | `class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500/40"` |

### 2d. Input / select polos → R6
| OLD | NEW |
|-----|-----|
| `class="mt-1 block w-full rounded border px-3 py-2"` | `class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25"` |
| `class="mt-1 block w-full rounded border px-3 py-2"` (dengan type apa pun) | (sama seperti di atas) |
| `class="flex-1 max-w-md rounded border px-3 py-2"` | `class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25 sm:w-72"` |
| `class="rounded border px-3 py-2"` (select) | `class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25"` |
| `class="mt-1 rounded border px-3 py-2"` (input date) | `class="mt-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25"` |

### 2e. Tabel: thead & cell → R11/R12
| OLD | NEW |
|-----|-----|
| `<table class="w-full text-sm">` | `<table class="w-full text-left text-sm">` |
| `<thead class="bg-gray-50">` | `<thead class="border-b border-gray-200 bg-gray-50">` |
| `<th class="px-3 py-2 text-left">` | `<th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">` |
| `<th class="px-3 py-2 text-right">` | `<th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 text-right">` |
| `<th class="px-4 py-2 text-left">` (income) | `<th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">` |
| `<th class="px-4 py-2 text-right">` (income) | `<th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 text-right">` |
| `<tr v-for="..." class="border-t hover:bg-gray-50">` | `<tr v-for="..." class="border-b border-gray-100 transition hover:bg-amber-50/40">` |
| `<td class="px-3 py-2">` | `<td class="px-4 py-3 align-top text-gray-700">` |
| `<td class="px-3 py-2 text-xs">` | `<td class="px-4 py-3 align-top text-xs text-gray-700">` |
| `<td class="px-3 py-2 font-mono">` | `<td class="px-4 py-3 align-top font-mono text-gray-700">` |
| `<td class="px-3 py-2 text-right space-x-1">` | `<td class="px-4 py-3 align-top text-right text-gray-700 space-x-1">` |
| `<td class="px-4 py-2">` | `<td class="px-4 py-3 align-top text-gray-700">` |
| `<td class="px-4 py-2 text-right">` | `<td class="px-4 py-3 align-top text-right text-gray-700">` |
| `<td class="px-4 py-2 text-right font-medium">` | `<td class="px-4 py-3 align-top text-right font-medium text-gray-700">` |
| `<td class="px-4 py-3 text-left">` (tfoot) | `<td class="px-4 py-3 text-gray-700">` |

> CATATAN baris terakhir: pada `<tr>` **terakhir di tbody**, hapus `border-b` dari kelas tr (biarkan `transition hover:bg-amber-50/40`).

### 2f. Aksi inline tabel (Edit/Delete/Approve) → R5
| OLD | NEW |
|-----|-----|
| `class="text-blue-600 hover:underline text-xs"` | `class="text-xs font-medium text-blue-600 transition hover:underline"` |
| `class="text-red-600 hover:underline text-xs"` | `class="text-xs font-medium text-red-600 transition hover:underline"` |
| `class="text-green-600 hover:underline text-xs"` | `class="text-xs font-medium text-green-600 transition hover:underline"` |
| `class="text-gray-600 hover:underline text-xs"` | `class="text-xs font-medium text-gray-600 transition hover:underline"` |

### 2g. Badge status → R13
Ubah `class="px-2 py-0.5 rounded-full text-xs font-medium ..."` menjadi
`class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ..."`
(pertahankan warna `bg-...-100 text-...-700` yang sudah ada — lihat §3 file 12 untuk penyeragaman nama status.)

### 2h. Pagination → R18
| OLD | NEW |
|-----|-----|
| `class="mt-4 flex justify-center gap-2"` | `class="mt-6 flex items-center justify-center gap-2"` |
| `class="px-3 py-1 rounded border text-sm"` | `class="rounded-lg border border-gray-200 px-3 py-1.5 text-sm text-gray-700 transition hover:bg-gray-50"` |
| `:class="{ 'bg-gray-200': link.active, 'opacity-50 pointer-events-none': !link.url }"` | `:class="link.active ? 'border-amber-600 bg-amber-600 text-white' : 'border-gray-200 text-gray-700', !link.url && 'pointer-events-none opacity-40'"` |

---

## 3. LANGKAH B — Checklist Per File

Setelah Replace All di §2, buka setiap file dan perbaiki hal khusus di bawah.

### 3.1 Layout (WAJIB DULUAN)
- [ ] `resources/js/Layouts/GuestLayout.vue` → timpa penuh dengan §5a file 12.
- [ ] `resources/js/Layouts/AdminLayout.vue` → timpa penuh dengan §5b file 12.
  > Setelah ini semua halaman otomatis dapat sidebar modern + flash banner konsisten.

### 3.2 Halaman Admin
- [ ] `Pages/Admin/Auth/Login.vue` — input sudah fokus amber (`focus:border-amber-500`). Sesuaikan ukuran: ganti `px-4 py-2.5` → biarkan, pastikan `rounded-lg border border-gray-300` bukan `gray-200`. Tombol login → R2. Judul `text-2xl font-extrabold` → `text-2xl font-bold text-gray-900`.
- [ ] `Pages/Admin/Auth/ForgotPassword.vue` — samakan pola dengan Login (R2/R6/R7).
- [ ] `Pages/Admin/Dashboard.vue` — stat card `bg-white rounded shadow p-4` → R19. Label `text-sm text-gray-500` → `text-sm font-medium text-gray-500`. Nilai `text-2xl font-bold` → `text-2xl font-bold text-gray-900`. Nilai income yang hijau (`text-green-600`) biarkan.
- [ ] `Pages/Admin/Customer/Index.vue` — setelah find-replace selesai. Kosong-kosong status center pakai `text-gray-500` → `text-sm text-gray-500` (biarkan).
- [ ] `Pages/Admin/Customer/Create.vue` & `Edit.vue` — form pakai R16. Label `text-sm font-medium` → R7. Tombol Simpan → R2 (dengan `disabled:` state). Tambahkan tombol Batal (R3) `href="/admin/customers"` di samping Simpan bila belum ada.
- [ ] `Pages/Admin/Plan/Index.vue`, `Create.vue`, `Edit.vue` — ikuti pola yang sama dengan Customer.
- [ ] `Pages/Admin/Order/Index.vue` — tombol "+ Buat Order" → R2. Bilah aksi ada 3 elemen (tombol + input + select) → R14. Badge status pakai R13.
- [ ] `Pages/Admin/Order/Create.vue` — sama pola form (R16/R7/R2).
- [ ] `Pages/Admin/Voucher/Index.vue`, `Create.vue`, `Edit.vue` — sama pola.
- [ ] `Pages/Admin/Router/Index.vue`, `Create.vue`, `Edit.vue` — sama pola.
- [ ] `Pages/Admin/Bandwidth/Index.vue`, `Create.vue`, `Edit.vue` — sama pola.
- [ ] `Pages/Admin/Settings/Payment.vue` — kartu → R1. Label → R7. Tombol Simpan → R2. Input file biarkan (pakai class `mt-1 block w-full`).
- [ ] `Pages/Admin/Settings/Notification.vue` — sama pola Payment. (Buka file & terapkan R16/R7/R2.)
- [ ] `Pages/Admin/IncomeReport.vue` — tombol biru sudah diganti di §2c. `tfoot` biarkan `bg-gray-100` (jadi `bg-gray-100` + `font-bold`). Row tfoot pakai R12 td.
- [ ] `Pages/Admin/Logs/Index.vue` — tab → R20. Filter: input → R15, select → R6. Badge jenis/status → R13. Tabel → R9–R12. Pagination → R18.

### 3.3 Halaman Customer Portal & Public
- [ ] `Pages/Customer/*` (Login, Dashboard, ForgotPassword) — login pakai GuestLayout (sudah ditimpa). Dashboard customer pakai pola kartu + stat (R19). Tombol → R2.
- [ ] `Pages/Public/Welcome.vue` — halaman publik. Terapkan R1 untuk kartu konten, tombol CTA → R2. Jaga branding amber tetap.

### 3.4 Blank row ("Tidak ada data")
- [ ] Semua `<tr v-if="...length === 0">` → `class="border-b-0"` atau hapus border; isi `td` center: `class="px-4 py-8 text-center text-sm text-gray-500"` (bukan `py-4`).

---

## 4. Verifikasi (JANGAN LEWATKAN)

1. **Build** berhasil:
   ```
   npm run build
   ```
   Jika ada error, perbaiki sebelum lanjut.

2. **Cek visual** (browser, login admin `admin`/`admin123`):
   - [ ] Sidebar putih modern, item aktif berwarna amber.
   - [ ] Semua kartu `rounded-xl` + border abu + shadow.
   - [ ] Semua input fokus jadi amber.
   - [ ] Semua tombol utama amber, tombol hapus merah.
   - [ ] Semua badge status bulat berwarna sesuai status.
   - [ ] Tidak ada lagi `bg-blue-600` (tombol biru harusnya sudah amber).

3. **Jangan ubah logika**, jangan hapus route/controller, jangan tambah dependency baru.

---

## 5. Ringkasan "Jangan Sentuh"
- `<script setup>` — semua JS/Vue logic.
- `routes/web.php`, controller, model, service.
- Struktur prop Inertia.
- Hanya ubah **class & struktur `<template>`** untuk visual.
