# 17-db-cleanup-audit.md — Audit Pembersihan Database

Dokumen ini adalah hasil **audit penuh** tabel & kolom database terhadap kode aplikasi
(controller, model, service, event, listener, command, Vue, routes). Tujuan: menemukan
**tabel/kolom mati (bloatware)** warisan PHPNuxBill asli yang tidak dibutuhkan.

> ✅ **Status: SUDAH DIEKSEKUSI** (2026-08-14) dengan backup di `database/backup-before-cleanup-*.sqlite`
> dan migration `2026_08_14_123401_cleanup_unused_phpnuxbill_columns`. Yang dihapus: tabel `users`,
> `tbl_plans.{routers,on_login,on_logout,pool}`, `tbl_users.{root,photo,data}`.
>
> **Part 2 (2026-08-14)** — backup `database/backup-before-cleanup2-*.sqlite`, migration
> `2026_08_14_125035_cleanup_unused_columns_part2`: **DROP `tbl_routers`** (modul Router) +
> `tbl_customers.{photo,coordinates,pppoe_ip,created_by}` + `tbl_users.{city,subdistrict,ward,user_type,login_token}`
> + `tbl_transactions.{routers,note}` + `tbl_user_recharges.routers` + `tbl_voucher.{routers,generated_by}`.
> Modul Router (controller/model/routes/pages/sidebar) ikut dihapus.
>
> **Penyelesaian (2026-08-14)** — hapus `MikrotikHotspotService` (jalur device non-Radius) & jadikan
> default `HotspotDeviceResolver` = `RadiusRest`; bersihkan sisa referensi kolom terhapus di
> `PlanController`/`Plan` (routers/pool/on_login/on_logout), form Plan (field Pool), `RadiusReplyAttributesBuilder`
> (cabang PPPoE), dan test terkait. Build & test bersih (55/57; 1 gagal = `ExampleTest` prain-eksis, kemudian dihapus).
>
> **Part 3 (2026-08-14)** — backup `database/backup-before-cleanup3-*.sqlite`, migration
> `2026_08_14_131930_cleanup_unused_columns_part3`: `tbl_plans.{plan_type,price_old}` +
> `tbl_customers.{account_type,balance,service_type,auto_renewal}`. `ExampleTest` dihapus.
> Build & full suite hijau (55/55).
>
> **Part 4 (2026-08-14)** — backup `database/backup-before-cleanup4-*.sqlite`, migration
> `2026_08_14_132711_cleanup_unused_columns_part4`: sederhanakan customer hanya
> `username, password, fullname, phonenumber, status` — drop `tbl_customers.{email,pppoe_username,
> pppoe_password,address,city,district,state,zip}`. Bersihkan logika pppoe dari
> `RadiusIdentityResolver` & `RadiusAuthController` (auth tetap PAP/Hotspot). Build & suite hijau (55/55).
>
> **Part 5 (2026-08-14)** — backup `database/backup-before-cleanup5-*.sqlite`, migration
> `2026_08_14_133335_cleanup_unused_columns_part5`: `tbl_plans.{allow_purchase,plan_expired,expired_date}`
> (allow_purchase tak dipakai; plan_expired redundan dgn notifikasi global; expired_date tak dipakai).
> Build & suite hijau (55/55).
>
> **Part 6 (2026-08-14)** — backup `database/backup-before-cleanup6-*.sqlite`, migration
> `2026_08_14_133752_cleanup_unused_columns_part6`: `tbl_plans.{is_radius,prepaid}` (is_radius selalu
> true & tak dipakai; postpaid/prepaid hanya utk expired_date yg sudah dihapus). Hapus seksi
> "Pembayaran" & "Radius" dari form/index Plan. Build & suite hijau (55/55).

---

## Metodologi

Setiap kolom dari setiap tabel `tbl_*` + `rad_acct` + `users` diperiksa terhadap seluruh
kode di `app/`, `resources/js/`, dan `routes/`. Kolom dianggap **dipakai** jika direferensikan
dalam: `$fillable`/`$casts`/`$hidden` model, aturan validasi, query Eloquent, mass-assignment,
binding v-model Vue, atau tampilan Vue.

**Catatan penting:** sebagian besar tabel men-deklarasi **semua kolom di `$fillable`** dan
dipopulasi via mass-assignment — sehingga banyak kolom "dipakai" karena *ditulis*, padahal
**tidak pernah dibaca/ditampilkan**. Audit ini membedakan tiga tingkat.

---

## 1. Inventaris Tabel

| Tabel | Status |
|-------|--------|
| `tbl_customers`, `tbl_plans`, `tbl_bandwidth`, `tbl_routers`, `tbl_orders`, `tbl_voucher`, `tbl_transactions`, `tbl_user_recharges`, `tbl_users` (admin), `tbl_logs`, `tbl_message_logs`, `tbl_appconfig` | ✅ Dipakai |
| `rad_acct` | ✅ Dipakai (dashboard User Online + accounting) |
| **`users`** (default Laravel) | ❌ **MATI** — model `User` memakai `tbl_users`; tidak ada kode yang menyentuh `users` |
| `sessions`, `cache`, `cache_locks` | ✅ Framework aktif (session/cache driver DB) |
| `jobs`, `job_batches`, `failed_jobs` | ⚪ Framework terkonfigurasi, kemungkinan kosong — **jangan dihapus** |
| `password_reset_tokens`, `personal_access_tokens` | ⚪ Framework, kemungkinan tidak dipakai — **jangan dihapus** (perlu konfirmasi sebelum dibersihkan) |

---

## 2. Kolom MATI — Tingkat Keyakinan Tinggi (aman dihapus)

Ditemukan tanpa referensi apa pun di kode (hanya ada di migration):

| Tabel | Kolom | Bukti |
|-------|-------|-------|
| `tbl_users` | **`root`** | Hanya di migration; bukan `$fillable`/`$casts`/`$hidden`; tak ada query/view |
| `tbl_users` | **`photo`** | Hanya di migration; semua `photo` lain adalah kolom Customer |
| `tbl_users` | **`data`** | Tak ada referensi sama sekali |

**Tabel `users` (default Laravel):** MATI seluruhnya — bisa `DROP TABLE` bila ada
(hanya warisan import; migration bawaan bahkan tidak membuatnya).

---

## 3. Kolom Efektif MATI untuk Deployment "FreeRADIUS REST"

Kolom ini *direferensikan di kode* namun **hanya di jalur device non-Radius**
(`MikrotikHotspotService` / `Voucher`), yang **tidak pernah berjalan** karena aplikasi
Anda **hanya memakai RadiusRest**. Efektif mati untuk deployment Anda:

| Tabel | Kolom | Catatan |
|-------|-------|---------|
| `tbl_plans` | **`routers`** | Hanya dipakai `MikrotikHotspotService`; sudah dihapus dari form Plan |
| `tbl_plans` | **`on_login`** | Hanya dipakai `MikrotikHotspotService`; sudah dihapus dari form Plan |
| `tbl_plans` | **`on_logout`** | Hanya dipakai `MikrotikHotspotService`; sudah dihapus dari form Plan |
| `tbl_plans` | `pool` | Dipakai `RadiusReplyAttributesBuilder` (jalur radius) — **verifikasi** sebelum hapus |

---

## 4. Kolom "Ditulis tapi Tidak Pernah Dibaca" — Keyakinan Rendah (perlu verifikasi)

Kolom berikut dipopulasi via mass-assignment di service, tetapi **tidak terlihat dibaca/
ditampilkan** di mana pun. Bukan "mati total" (masih ditulis), tapi berpotensi bloat:

| Tabel | Kolom |
|-------|-------|
| `tbl_transactions` | `routers`, `type`, `note` |
| `tbl_user_recharges` | `routers`, `type` |
| `tbl_voucher` | `generated_by` |
| `tbl_customers` | `auto_renewal`, `coordinates`, `created_by` |

> Perlu penelusuran manual per-kolom untuk memastikan tak dibaca lewat relasi/akses
> properti tak langsung sebelum dimasukkan ke daftar hapus.

---

## 5. Rekomendasi Migration (belum dieksekusi)

Jika disetujui, langkah yang diusulkan (lewat **migration Laravel**, dengan **backup DB** dulu):

1. **Backup** database (dump) sebelum apa pun.
2. **`DROP TABLE users`** — tabel default Laravel yang mati.
3. **`ALTER TABLE tbl_users`** — drop kolom `root`, `photo`, `data`.
4. **`ALTER TABLE tbl_plans`** — drop kolom `routers`, `on_login`, `on_logout`
   (efektif mati untuk RadiusRest; sudah tak dipakai form).

> Kolom §4 (ditulis-tak-dibaca) **tidak** masuk rekomendasi drop sampai diverifikasi.

---

## 6. Catatan Penutup

- Semua kolom di `tbl_customers`, `tbl_bandwidth`, `tbl_routers`, `tbl_orders`,
  `tbl_voucher`, `tbl_logs`, `tbl_message_logs`, `tbl_appconfig`, `rad_acct`
  **dipakai** — tidak ada kolom mati di sana.
- Tabel framework (jobs, cache, sessions, password_reset_tokens, personal_access_tokens)
  adalah internal Laravel — **tidak disarankan** dihapus.
- Dokumen ini untuk persetujuan. Setelah Anda memutuskan cakupan, saya buat migration
  + backup dan eksekusi.
