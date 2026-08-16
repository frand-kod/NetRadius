# 12-styling-system.md — Sistem Desain & Recipe Kelas (WAJIB DIBACA SEBELUM TASK 13)

Task ini **tidak menulis kode baru**. Tujuan: membangun satu "kamus kelas" (class recipe) yang konsisten.
Semua halaman Vue harus memakai recipe ini. **Jangan membuat variasi sendiri** — salin persis string kelas di bawah.

Baca ini lengkap, lalu kerjakan `13-styling-pages.md`.

---

## 1. Palet Warna

Hanya gunakan warna-warna ini. Jangan pakai warna lain.

| Peran | Nilai Tailwind |
|-------|----------------|
| Aksen utama (brand) | `amber-600` (hover `amber-700`) |
| Fokus input | `amber-500` |
| Background halaman | `gray-100` |
| Background kartu | `white` |
| Border kartu/table | `gray-200` |
| Border input | `gray-300` |
| Teks judul | `gray-900` |
| Teks body | `gray-700` |
| Teks sekunder/label | `gray-500` |
| Teks placeholder | `gray-400` |
| Sukses | `green-600` / bg `green-100` teks `green-700` |
| Gagal/error | `red-600` / bg `red-100` teks `red-700` |
| Peringatan | `yellow` / `amber` |

---

## 2. RECIPE KELAS (salon persis, jangan ubah)

Ini satu-satunya sumber kebenaran. Setiap kali butuh komponen UI, tempel string ini.

### R1. Kartu (card) — kontainer putih
```
bg-white rounded-xl border border-gray-200 shadow-sm
```

### R2. Tombol Utama (Primary) — aksi utama: Simpan, Tambah, Login, Approve
```
inline-flex items-center justify-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500/40 disabled:cursor-not-allowed disabled:opacity-60
```

### R3. Tombol Sekunder (Secondary) — Batal, Kembali
```
inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400/30
```

### R4. Tombol Bahaya (Danger) — Hapus, Cancel, Logout
```
inline-flex items-center justify-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500/40 disabled:cursor-not-allowed disabled:opacity-60
```

### R5. Tombol Aksi Inline (dalam tabel: Edit/Delete kecil)
Kecil, tanpa box penuh.
```
text-xs font-medium transition hover:underline
```
- Edit → tambah `text-blue-600`
- Delete → tambah `text-red-600`

### R6. Input / Select / Textarea
```
w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25
```

### R7. Label form
```
block text-sm font-medium text-gray-700
```

### R8. Teks error validasi (di bawah input)
```
mt-1 text-xs text-red-600
```

### R9. Wrapper Tabel
```
overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm
```

### R10. `<table>` dalam wrapper
```
w-full text-left text-sm
```

### R11. `<thead>` + `<tr>` header
- thead: `border-b border-gray-200 bg-gray-50`
- th: `px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500`

### R12. `<tbody>` + `<tr>` data
- tr: `border-b border-gray-100 transition hover:bg-amber-50/40` (baris TERAKHIR: hapus `border-b`)
- td: `px-4 py-3 text-gray-700 align-top`
- kolom Actions → tambah `text-right`

### R13. Badge Status (chip bulat)
```
inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
```
Warna sesuai status (lihat §3).

### R14. Bilah Aksi halaman (tombol + search sejajar)
```
mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between
```

### R15. Input pencarian (dalam bilah aksi)
```
w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25 sm:w-72
```

### R16. Form Create/Edit — kartu berisi form
```
space-y-5 rounded-xl border border-gray-200 bg-white p-6 shadow-sm
```
Grid field dalam form: `grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3`

### R17. Flash sukses / error (banner di atas konten)
- Sukses: `mb-4 flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700`
- Error: `mb-4 flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700`

### R18. Pagination container
```
mt-6 flex items-center justify-center gap-2
```
Setiap item link pagination: `rounded-lg border border-gray-200 px-3 py-1.5 text-sm text-gray-700 transition hover:bg-gray-50`
- aktif: ganti `border-gray-200` → `border-amber-600 bg-amber-600 text-white`
- non-aktif (`!link.url`): `pointer-events-none opacity-40`

### R19. Stat Card (Dashboard)
```
rounded-xl border border-gray-200 bg-white p-5 shadow-sm
```
- label: `text-sm font-medium text-gray-500`
- nilai: `mt-1 text-2xl font-bold text-gray-900`

### R20. Tab (Logs)
```
rounded-lg px-4 py-2 text-sm font-medium transition
```
- aktif: `bg-amber-600 text-white shadow-sm`
- non-aktif: `bg-white text-gray-600 hover:bg-gray-100 border border-gray-200`

---

## 3. Warna Badge per Status

Gunakan `:class` binding berikut. **Badge base = R13.**

| Status | `bg-...-100 text-...-700` |
|--------|---------------------------|
| Active / Success / Paid / Connected | `bg-green-100 text-green-700` |
| Banned / Disabled / Cancelled / Failed | `bg-red-100 text-red-700` |
| Inactive / Suspended / Pending / Waiting | `bg-amber-100 text-amber-700` |
| Limited / Expired / Others | `bg-gray-100 text-gray-700` |
| Aktif (customer) | `bg-green-100 text-green-700` |

Contoh binding lengkap untuk status kolom yang variatif:
```html
<span :class="{
    'bg-green-100 text-green-700':  c.status === 'Active',
    'bg-red-100 text-red-700':      c.status === 'Banned' || c.status === 'Disabled' || c.status === 'Cancelled',
    'bg-amber-100 text-amber-700':  c.status === 'Inactive' || c.status === 'Suspended' || c.status === 'Pending',
    'bg-gray-100 text-gray-700':    c.status === 'Limited' || c.status === 'Expired',
}" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold">
    {{ c.status }}
</span>
```

---

## 4. Format Uang & Tanggal

- Rupiah: `Rp {{ Number(x).toLocaleString('id-ID') }}`
- Tanggal mentah (dari DB): tampilkan apa adanya jika sudah string, atau `.slice(0, 16).replace('T', ' ')` untuk timestamp ISO.

---

## 5. Layout (GuestLayout & AdminLayout) — GANTI KESELURUHAN FILE

Dua file layout ini dipakai semua halaman. **Timpa isinya dengan versi di bawah** (struktur sama, kelas dirapikan + sidebar modern). Jangan ubah nama slot/prop.

### 5a. `resources/js/Layouts/GuestLayout.vue` — timpa penuh:

```vue
<script setup>
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-gray-100 p-4">
        <div class="w-full max-w-sm">
            <div class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
                <slot />
            </div>
        </div>
    </div>
</template>
```

### 5b. `resources/js/Layouts/AdminLayout.vue` — timpa penuh:

```vue
<script setup>
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
</script>

<template>
    <div class="flex min-h-screen bg-gray-100">
        <!-- Sidebar -->
        <aside class="flex w-64 shrink-0 flex-col border-r border-gray-200 bg-white">
            <div class="flex h-16 items-center gap-2 border-b border-gray-200 px-5">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-600 text-sm font-bold text-white">B</span>
                <span class="text-lg font-bold text-gray-900">PHPNuxBill</span>
            </div>
            <nav class="flex-1 space-y-1 overflow-y-auto p-3">
                <Link href="/admin"
                      class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition"
                      :class="page.url === '/admin' || page.url.startsWith('/admin/') && !page.url.startsWith('/admin/customers') && !page.url.startsWith('/admin/plans') && !page.url.startsWith('/admin/orders') && !page.url.startsWith('/admin/vouchers') && !page.url.startsWith('/admin/routers') && !page.url.startsWith('/admin/bandwidths') && !page.url.startsWith('/admin/settings') && !page.url.startsWith('/admin/income-report') && !page.url.startsWith('/admin/logs')
                          ? 'bg-amber-50 text-amber-700'
                          : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'">
                    Dashboard
                </Link>
                <Link href="/admin/customers"
                      class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition"
                      :class="page.url.startsWith('/admin/customers') ? 'bg-amber-50 text-amber-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'">
                    Customers
                </Link>
                <Link href="/admin/plans"
                      class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition"
                      :class="page.url.startsWith('/admin/plans') ? 'bg-amber-50 text-amber-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'">
                    Plans
                </Link>
                <Link href="/admin/orders"
                      class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition"
                      :class="page.url.startsWith('/admin/orders') ? 'bg-amber-50 text-amber-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'">
                    Orders
                </Link>
                <Link href="/admin/vouchers"
                      class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition"
                      :class="page.url.startsWith('/admin/vouchers') ? 'bg-amber-50 text-amber-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'">
                    Vouchers
                </Link>
                <Link href="/admin/routers"
                      class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition"
                      :class="page.url.startsWith('/admin/routers') ? 'bg-amber-50 text-amber-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'">
                    Routers
                </Link>
                <Link href="/admin/bandwidths"
                      class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition"
                      :class="page.url.startsWith('/admin/bandwidths') ? 'bg-amber-50 text-amber-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'">
                    Bandwidth
                </Link>
                <Link href="/admin/settings/payment"
                      class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition"
                      :class="page.url.startsWith('/admin/settings/payment') ? 'bg-amber-50 text-amber-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'">
                    Payment Settings
                </Link>
                <Link href="/admin/settings/notification"
                      class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition"
                      :class="page.url.startsWith('/admin/settings/notification') ? 'bg-amber-50 text-amber-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'">
                    Notification Settings
                </Link>
                <Link href="/admin/income-report"
                      class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition"
                      :class="page.url.startsWith('/admin/income-report') ? 'bg-amber-50 text-amber-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'">
                    Income Report
                </Link>
                <Link href="/admin/logs"
                      class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition"
                      :class="page.url.startsWith('/admin/logs') ? 'bg-amber-50 text-amber-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'">
                    Logs
                </Link>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="flex h-16 shrink-0 items-center justify-between border-b border-gray-200 bg-white px-6">
                <h1 class="text-xl font-bold text-gray-900">
                    <slot name="title">Dashboard</slot>
                </h1>
                <div class="flex items-center gap-4">
                    <span v-if="page.props.auth?.user?.fullname" class="text-sm font-medium text-gray-700">
                        {{ page.props.auth.user.fullname }}
                    </span>
                    <Link href="/admin/logout" method="post" as="button"
                          class="text-sm font-medium text-red-600 transition hover:text-red-700">
                        Logout
                    </Link>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden p-6">
                <div v-if="page.props.flash?.success"
                     class="mb-4 flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                    {{ page.props.flash.success }}
                </div>
                <div v-if="page.props.flash?.error"
                     class="mb-4 flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                    {{ page.props.flash.error }}
                </div>
                <slot />
            </main>
        </div>
    </div>
</template>
```

> CATATAN: Link Dashboard aktif jika halaman `/admin` ATAU `/admin/dashboard`. Kondisi di atas sudah mengecualikan semua prefix lain sehingga `/admin` tetap benar. Jangan ubah logika ini.

---

## 6. Checklist Hasil Akhir

Setelah selesai, pastikan:
- [ ] Semua kartu memakai `rounded-xl border border-gray-200 bg-white shadow-sm` (R1)
- [ ] Semua input memakai R6 (fokus amber, `rounded-lg`, shadow)
- [ ] Semua tombol memakai R2/R3/R4 sesuai peran
- [ ] Semua tabel memakai R9–R12
- [ ] Semua badge memakai R13 + warna §3
- [ ] Layout AdminLayout & GuestLayout sudah ditimpa sesuai §5
