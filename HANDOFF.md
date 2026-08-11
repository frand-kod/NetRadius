# Handoff Notes — PHPNuxBill → Laravel Migration

Baca file ini di awal sesi baru (root directory: `laravel-nuxbill/`) untuk melanjutkan tanpa mengulang konteks. Tulis ke user: "sudah baca HANDOFF.md, lanjut dari [state terakhir]".

## Konteks Proyek

Migrasi **PHPNuxBill** (billing hotspot Mikrotik, PHP prosedural lama tanpa framework, di `../` relatif dari folder ini — repo `phpnuxbill-fork`) ke **Laravel**, untuk pemakaian **pribadi** (bukan OSS publik, bukan produk multi-tenant). Skala: ISP rumahan/perumahan kecil, ~23 customer, single admin (user sendiri).

**Alasan migrasi**: maintainability jangka panjang (kode asli god-file, global state, tanpa test), mau nambah fitur besar yang susah di arsitektur lama, dan sekalian belajar Laravel. User sudah pernah kerjakan project Laravel sebelumnya (bukan pemula).

**Target waktu: sangat agresif — 2 hari.** User sadar ini padat, sudah di-warn eksplisit soal risiko meleset terutama di integrasi Mikrotik/RadiusRest dan order flow.

## Keputusan yang Sudah Difinalisasi (JANGAN tanya ulang ini)

- **Lokasi project**: nested di `phpnuxbill-fork/laravel-nuxbill/` (bukan sibling folder terpisah seperti rencana awal — user yang install manual di sini). Sudah di-gitignore dari repo `phpnuxbill-fork` (lihat `.gitignore` di root repo: `laravel-nuxbill/`).
- **Database: SQLite** (bukan MySQL/Postgres). Alasan: skala kecil, resource efficiency (target deploy di hardware kecil), FreeRADIUS user pakai integrasi **REST** (bukan `rlm_sql` langsung ke DB) jadi tidak ada keharusan match engine DB dengan FreeRADIUS. Bisa pindah ke Postgres nanti kalau perlu (tinggal ganti `.env` + `php artisan migrate`), bukan keputusan permanen.
- **Multi-router**: YA, user kelola lebih dari 1 Mikrotik.
- **Notifikasi**: Telegram + WhatsApp saja (bukan SMS/Email).
- **Fitur ekstra yang dipakai**: Voucher generate + print. TIDAK pakai: coupons, self-registration signup (customer didaftarkan admin manual). Customer portal (Task #5, sudah selesai) untuk login + lihat status/riwayat — **BUKAN untuk membuat order baru** (lihat koreksi alur QR di bawah, order dibuat admin, bukan customer).
- **Hanya Hotspot** (tidak pakai PPPoE) → tidak perlu IP pool management (`tbl_pool`, `tbl_port_pool` di-skip).
- **Single admin**, tidak ada role staff (Agent/Sales) → skip `accounts.php`/role management kompleks.
- **Pembayaran**: QR statis manual — **DIKOREKSI, baca section "SPEC: Order/QR Flow" di bawah untuk detail lengkap, JANGAN pakai deskripsi lama "upload bukti" yang masih tersisa di bagian lain dokumen ini**. Ringkas: QR cuma SATU gambar statis (diupload sekali oleh admin di Settings, dipakai untuk semua transaksi), admin yang BUAT order manual di admin panel (bukan customer self-service), sistem otomatis kirim invoice+link QR via WA, TIDAK ADA upload bukti dari customer sama sekali — admin cek pembayaran masuk sendiri (via notifikasi bank/e-wallet pribadi) lalu approve manual. BUKAN payment gateway pihak ketiga.
- **Forgot password** dan **laporan income/rekap transaksi**: both must-have, bukan nice-to-have.
- **Explicitly OUT OF SCOPE** (jangan diporting): `tbl_payment_gateway`/payment gateway framework, plugin manager ekosistem publik (`pluginmanager.php`), CMS pages (`page.php`/`pages.php`), ODP/maps fiber (`tbl_odps`, `maps.php`, `odp.php`), custom fields (`tbl_customers_fields`, `customfield.php`), channel email, export/bandwidth widget kosmetik (`export.php`, `bandwidth.php`, `widgets.php`), `tbl_coupons`, `tbl_recharge_cards`/`tbl_recharge_lock`, `tbl_meta`, `community.php`, `callback.php` (webhook payment gateway).

## Task List Final (13 task)

Must-have, urutan prioritas jalur kritis:
1. Setup Laravel skeleton + Eloquent ke skema existing — **IN PROGRESS, lihat status di bawah**
2. Admin auth + core CRUD (customer/plan/voucher)
3. Port Mikrotik API + RadiusRest integration
4. End-to-end test: add customer → active di Mikrotik
5. Customer auth (self-service login, guard terpisah dari admin)
6. Multi-router config CRUD
7. Order/renew flow via QR statis (admin buat order, kirim invoice+QR via WA, admin approve manual — **BUKAN** "upload bukti", lihat SPEC lengkap di bawah)
8. Notifikasi Telegram + WhatsApp
9. Voucher generate + print
10. Cron/scheduler: expiry check + auto-disable + reminder
11. Activity log (admin audit trail)
12. Forgot password (admin & customer)
13. Laporan income/rekap transaksi

Boleh disederhanakan dulu kalau waktu mepet: #9 (voucher print versi basic dulu), #11 (log tanpa viewer UI dulu, cukup nulis ke DB).

## Sumber Data & Skema Referensi

- Dump SQL live production ada di `../db.txt` (relatif dari folder ini) — **SENSITIF, jangan pernah commit/expose**. Berisi PII customer, WA gateway secret (`tbl_appconfig` row `wa_url`), kredensial `alt_wga_*`. Sudah di-gitignore di repo luar (`db.txt`, `nuxbill.sql`, `*.sql`).
- Untuk lihat schema-only (tanpa data rows) dari dump: `awk '/^CREATE TABLE/{p=1} p{print} /^\) ENGINE/{p=0}' ../db.txt`
- 23 tabel total di dump asli. **Yang di-porting** (12 tabel, sudah ada migration & sudah dijalankan): `rad_acct`, `tbl_appconfig`, `tbl_bandwidth`, `tbl_customers`, `tbl_logs`, `tbl_message_logs`, `tbl_plans`, `tbl_routers`, `tbl_transactions`, `tbl_user_recharges` (ditambahkan belakangan, lihat section "Update Status" di bawah — TERLEWAT dari batch awal, krusial untuk RADIUS/recharge logic), `tbl_users`, `tbl_voucher`.
- **Yang di-skip** (sesuai scope decisions di atas): `tbl_coupons`, `tbl_customers_fields`, `tbl_customers_inbox`, `tbl_meta`, `tbl_odps`, `tbl_payment_gateway`, `tbl_pool`, `tbl_port_pool`, `tbl_recharge_cards`, `tbl_recharge_lock`, `tbl_widgets`.
- Kode asli PHP lama untuk referensi porting logic ada di `../system/controllers/*.php` (40 file), `../system/autoload/*.php` (kelas domain: `Message.php` untuk notifikasi Telegram/WA/SMS/Email, `User.php`, `Package.php`, `Invoice.php`, `Balance.php`), `../system/devices/Mikrotik*.php` dan `../system/devices/RadiusRest.php` (device integration, RadiusRest cuma 78 baris — REST call tipis), `../radius.php` (475 baris, bukan 23k — jangan salah baca ukuran file KB vs jumlah baris).

## Status Teknis Saat Ini (per file, sudah tersimpan di disk)

- **Laravel 13.24.0** terinstall (`composer install` dijalankan manual oleh user).
- **`laravel/boost` v2.5.3** + `laravel/mcp` v0.9.2 terinstall. `CLAUDE.md` di root folder ini berisi guidelines Laravel Boost (ikuti itu: pakai `php artisan make:*` commands, jalankan `vendor/bin/pint --dirty --format agent` setelah edit PHP, dst).
- **`.mcp.json` sudah ada di folder ini** (`{"mcpServers":{"laravel-boost":{"command":"php","args":["artisan","boost:mcp"]}}}`). **PENTING**: MCP ini cuma ke-load kalau Claude Code session di-start dengan root directory = folder `laravel-nuxbill` ini (bukan folder `phpnuxbill-fork` di atasnya). Setelah pindah ke sini, cek dengan `ToolSearch` query seperti `"select:search-docs,database-schema,tinker,application-info"` — kalau masih kosong, MCP belum ke-load, lanjut pakai `php artisan` via Bash biasa (sudah cukup, cuma tanpa bantuan `search-docs`).
- **`.env`**: `DB_CONNECTION=sqlite`, file `database/database.sqlite` sudah dibuat.
- **Migrations**: 11 migration custom + migration default `0001_01_01_000000_create_users_table.php` yang sudah dimodifikasi (tabel `users` bawaan Laravel DIHAPUS dari situ, `tbl_users` dipakai sebagai gantinya untuk admin auth; `password_reset_tokens` dan `sessions` tetap ada apa adanya). Semua migration **sudah dijalankan sukses** (`php artisan migrate` — cek ulang dengan `php artisan migrate:status` kalau perlu, terakhir semua status "Ran").
- **Models** (`app/Models/`):
  - ✅ `User.php` — SUDAH lengkap. Repurposed jadi admin auth model: `$table = 'tbl_users'`, `CREATED_AT = 'creationdate'`, `UPDATED_AT = null`, fillable (username, fullname, password, phone, email, city, subdistrict, ward, user_type, status), hidden (password, remember_token, login_token), casts (password => hashed, last_login/creationdate => datetime).
  - ✅ `Bandwidth.php` — SUDAH lengkap. `$table = 'tbl_bandwidth'`, `$timestamps = false`, fillable (name_bw, rate_down, rate_down_unit, rate_up, rate_up_unit, burst), relasi `hasMany(Plan::class, 'id_bw')`.
  - ✅ `Router.php` — SUDAH lengkap. `$table = 'tbl_routers'`, `$timestamps = false`, fillable (name, ip_address, username, password, description, coordinates, status, last_seen, coverage, enabled), hidden (password), casts (last_seen => datetime, enabled => boolean).
  - ✅ `Plan.php` — SUDAH lengkap. `$table = 'tbl_plans'`, `$timestamps = false`, fillable sesuai kolom migration, relasi `bandwidth(): belongsTo(Bandwidth::class, 'id_bw')`, casts (is_radius/enabled => boolean).
  - ✅ `Customer.php` — SUDAH lengkap. `$table = 'tbl_customers'`, extends `Illuminate\Foundation\Auth\User as Authenticatable` (guard auth terpisah, dipakai di task #5), `UPDATED_AT = null` (tabel tidak punya kolom updated_at), fillable sesuai kolom migration, hidden (password, remember_token), casts (password => hashed, balance => decimal:2, auto_renewal => boolean, created_at/last_login => datetime).
  - ✅ `Transaction.php` — SUDAH lengkap. `$table = 'tbl_transactions'`, `$timestamps = false`, fillable sesuai migration, casts (recharged_on/expiration => date).
  - ✅ `Voucher.php` — SUDAH lengkap. `$table = 'tbl_voucher'`, `UPDATED_AT = null`, fillable sesuai migration, relasi `plan(): belongsTo(Plan::class, 'id_plan')`, casts (created_at/used_date => datetime).
- **Filament v3** SUDAH diinstall (user pilih "Filament" saat ditanya, bukan Blade manual). Panel admin di `app/Providers/Filament/AdminPanelProvider.php`, id `admin`, path `/admin`, guard default `web` yang provider-nya sudah `App\Models\User` (tbl_users) — jadi tidak perlu guard terpisah untuk admin. `User` model implements `FilamentUser` dengan `canAccessPanel()` yang cek `status === 'Active'`.
- **Filament Resources** SUDAH digenerate (via `make:filament-resource --generate`, auto dari schema DB) untuk: `CustomerResource`, `PlanResource`, `VoucherResource`, `RouterResource`, `BandwidthResource` — di `app/Filament/Resources/`. Form/table basic (auto-generated dari kolom migration), field enum masih `TextInput` biasa (bukan `Select` dengan opsi) — cukup untuk MVP single-admin tapi idealnya diperbaiki jadi Select kalau ada waktu lebih (enum: `status`, `account_type`, `service_type` di Customer; `type`/`typebp`/dll di Plan).
- **Mikrotik/RadiusRest integration** (Task #3 & #4) SUDAH diporting ke `app/Services/Hotspot/`:
  - `HotspotDeviceInterface.php` — kontrak (addCustomer, removeCustomer, syncCustomer, changeUsername, addPlan, updatePlan, removePlan, onlineCustomer, connectCustomer, disconnectCustomer), diadaptasi dari pola `../system/devices/*.php`.
  - `MikrotikHotspotService.php` — port dari `../system/devices/MikrotikHotspot.php`, pakai package composer `pear2/net_routeros:1.0.0b6` (harus di-require dengan `minimum-stability: beta` di `composer.json` karena versi stabil tidak tersedia di Packagist — sudah di-set, `prefer-stable: true` tetap dipertahankan supaya package lain tidak ikut ke-downgrade).
  - `RadiusRestService.php` — port dari `../system/devices/RadiusRest.php` (aslinya nyaris no-op, FreeRADIUS baca `tbl_customers`/`tbl_plans` langsung via REST module-nya sendiri; satu-satunya efek nyata adalah reset `rad_acct.acctinputoctets`/`acctoutputoctets` ke 0 saat customer di-nonaktifkan untuk plan berbasis data limit).
  - `HotspotDeviceResolver.php` — pilih service berdasarkan kolom `Plan->device` (`'MikrotikHotspot'` atau `'RadiusRest'`, default ke `MikrotikHotspot` kalau kosong), mirror dari `Package::getDevice()` di kode lama.
  - `CustomerActivationService.php` — orchestrator tipis: `activate(Customer, Plan)` dan `deactivate(Customer, Plan)`, dipanggil dari controller/job nanti untuk task #7 (order/renew flow).
- **Model factories** SUDAH dibuat untuk testing: `BandwidthFactory`, `RouterFactory`, `PlanFactory`, `CustomerFactory` (di `database/factories/`). Semua model terkait (`Bandwidth`, `Router`, `Plan`, `Customer`) sudah pakai trait `HasFactory`. Catatan: `PlanFactory` kolom `routers` adalah **nama** router (string), bukan foreign id — pakai `fn () => Router::factory()->create()->name`, JANGAN `Router::factory()` langsung (itu akan resolve ke id, bukan name, dan salah untuk lookup di `MikrotikHotspotService::client()`).
- **Test end-to-end (Task #5)** SUDAH ada di `tests/Feature/CustomerActivationTest.php` — karena tidak ada router Mikrotik asli untuk dites, pendekatannya adalah bind fake `HotspotDeviceInterface` ke container (`$this->app->instance(MikrotikHotspotService::class, $fakeDevice)`) lalu assert `CustomerActivationService::activate()` memanggil `addCustomer()` dengan customer & plan yang benar. Test kedua verifikasi `HotspotDeviceResolver` pilih `RadiusRestService` kalau `Plan->device = 'RadiusRest'`. **Semua 4 test pass** (`php artisan test --compact`).

## ⚠️ KOREKSI STATUS (ditemukan saat audit ulang — baca sebelum lanjut)

Klaim "Task #1-5 SELESAI" di versi HANDOFF sebelumnya **TIDAK AKURAT**. Verifikasi ulang (cek langsung file, bukan cuma percaya narasi):

- **Task #1-4: genuinely selesai** (migration jalan, model lengkap, Filament resource ada, 4 test pass — sudah diverifikasi `php artisan test --compact`).
- **Task #5 (customer auth): BELUM selesai.** `config/auth.php` tidak ada guard `customer`, `routes/web.php` cuma placeholder default Laravel (`welcome` view). Yang ada baru `Customer extends Authenticatable` di model — itu prasyarat, bukan implementasi.

### GAP BESAR yang baru ditemukan (belum ada di task list manapun sebelumnya)

1. **RADIUS REST API endpoint belum diporting sama sekali.** Kode lama `../radius.php` (475 baris, di root `phpnuxbill-fork`, BUKAN di dalam `system/`) adalah **REST server** yang dipanggil FreeRADIUS untuk 4 action: `authenticate`, `authorize`, `accounting`, `post-auth` (lihat `../docs/freeradius-rest-integration.md` — sebenarnya berlokasi di `laravel-nuxbill/docs/`, dokumen tutorial dari developer asli). Ini beda total dari `RadiusRestService.php` yang sudah dibuat (itu untuk arah sebaliknya: phpnuxbill → device, dipicu aksi admin). **Tanpa endpoint REST inbound ini, tidak ada customer yang bisa authenticate ke hotspot sama sekali** — jalur Mikrotik → FreeRADIUS → REST call ke Laravel app kita akan gagal total karena endpoint-nya tidak ada.
   - Isi logic yang harus diporting: verifikasi PAP & CHAP (`Password::chap_verify()`, kecil ~15 baris, gampang), lookup customer/voucher, cek plan aktif & masa berlaku, hitung reply attributes (`Mikrotik-Rate-Limit`, `Mikrotik-Total-Limit` untuk data limit, `Max-All-Session`/`Expire-After` untuk time limit), enforce `shared_users` (concurrent session limit), tulis ke `rad_acct` (accounting), **DAN aktivasi voucher terjadi persis di action `authorize`** (memanggil `Package::rechargeUser()`) — ini konfirmasi alur voucher yang kamu jelaskan (customer ketik kode di captive portal Mikrotik → masuk sebagai RADIUS authorize request → endpoint ini yang aktivasi).
2. **`Package::rechargeUser()` (457 baris, `../system/autoload/Package.php` baris 21-478) belum genuinely diporting.** `CustomerActivationService::activate()` yang sudah ada adalah versi simplified — orchestrator asli menangani: cek plan lama yang masih aktif (switch plan logic), recharge via balance vs Custom Balance, tax, pembuatan `tbl_transactions`/`tbl_user_recharges`, generate invoice, kirim notifikasi otomatis. Fungsi lain di file yang sama juga relevan: `rechargeBalance()` (baris 478), `rechargeCustomBalance()` (536), `createInvoice()` (615), `getDevice()` (728, ini sudah setara `HotspotDeviceResolver`).

**Dampak ke voucher (Task #9)**: karena aktivasi voucher terjadi di RADIUS `authorize` endpoint (bukan lewat portal customer web), Task #9 TIDAK butuh route/halaman customer terpisah untuk redeem — generate+print saja cukup di sisi admin, aktivasinya otomatis lewat jalur RADIUS begitu voucher dipakai di Mikrotik. Ini menyederhanakan Task #9, tapi memperjelas bahwa RADIUS REST endpoint (poin 1 di atas) adalah **prasyarat**, bukan task terpisah yang bisa ditunda.

### Konfirmasi scope tambahan (setelah audit gap di atas — JANGAN tanya ulang)

- **TIDAK pakai balance/deposit sama sekali** — user konfirmasi berdasarkan pengalaman riil, tidak ada customer yang pernah top-up. Skip total: `Package::rechargeBalance()`, `rechargeCustomBalance()`, `tax()`. Porting `rechargeUser()` cukup fokus ke jalur prepaid biasa (bukan `router_name == 'balance'` atau `'Custom Balance'`).
- **TIDAK ada mid-cycle plan switch** — customer selalu tunggu plan lama expired dulu baru bisa aktivasi plan baru. Skip logic "1 customer only 1 PPPOE + 1 Hotspot plan, cek switch" yang ada di `rechargeUser()` baris ~109-235 (query ke `tbl_user_recharges` untuk cek existing plan aktif) — cukup: kalau tidak ada recharge aktif → buat baru, kalau ada dan masih aktif → tolak/tunggu expired.
- **Tidak ada environment FreeRADIUS+Mikrotik live untuk testing** — andalkan porting hati-hati dari kode asli + unit/feature test dengan fake binding (pola sama seperti `CustomerActivationTest.php`). Validasi ke device/FreeRADIUS asli jadi item terpisah SEBELUM go-live production, bukan blocker development.
- **Endpoint RADIUS REST tidak perlu path sama persis dengan `radius.php` lama** — user akan update `connect_uri` di config FreeRADIUS (`mods-enabled/rest`) saat cutover, jadi bebas pakai routing convention Laravel biasa (mis. `routes/api.php` dengan prefix `radius`, action sebagai path segment atau query param — desain bebas, tidak terikat kompatibilitas URL lama).

## Update Status (setelah audit lanjutan)

- **`tbl_user_recharges` — tabel yang TERLEWAT dari 11 migration awal, sudah ditambahkan**: migration `database/migrations/2026_08_08_144848_create_tbl_user_recharges_table.php` + model `app/Models/UserRecharge.php`, SUDAH dijalankan (`php artisan migrate` sukses). Kolom persis mirror `tbl_user_recharges` asli (`customer_id`, `username`, `plan_id`→FK `tbl_plans`, `namebp`, `recharged_on`, `recharged_time`, `expiration`, `time`, `status`, `method`, `routers`, `type`, `admin_id`). **PENTING**: `customer_id` SENGAJA `unsignedInteger` biasa TANPA FK constraint (bukan `foreignId->constrained`) — karena recharge via voucher punya `customer_id = 0` (tidak ada row `tbl_customers` sungguhan, identitas sintetis dari kode voucher). Model punya helper `isExpired(): bool` (Carbon parse dari `expiration` + `time`).
- Belum ada file service/controller lain yang ditulis untuk RADIUS endpoint atau recharge logic — bagian ini masih **spec only**, lihat detail lengkap di bawah untuk siapa pun yang mengimplementasikan.

## SPEC: `RechargeService` (pengganti penuh untuk `CustomerActivationService` yang masih simplified)

Port dari `../system/autoload/Package.php` fungsi `rechargeUser()` (baris 21-476), **dengan scope yang sudah disederhanakan** sesuai konfirmasi user:
- SKIP total: balance/deposit (`router_name == 'balance'`/`'Custom Balance'`), tax, `add_cost`/`User::getBills()` (semua terkait fitur balance yang tidak dipakai).
- SKIP: `validity_unit == 'Period'` (postpaid/billing bulanan) — lempar exception kalau plan pakai ini, bukan didukung. Fokus hanya `Months`, `Days`, `Hrs`, `Mins`.
- SKIP: logic "extend expiry kalau plan sama" (baris 195-226 kode asli, butuh `config['extend_expiry']`) — TIDAK didukung karena user konfirmasi "selalu tunggu expired dulu, tidak ada mid-cycle switch/extend".
- SKIP: `tbl_customers_fields` Invoice tracking (baris 319-333, 432-456) — tabel itu memang sudah di luar scope dari awal.

**Behaviour yang HARUS diimplementasikan** (signature disarankan, boleh disesuaikan gaya Laravel):

```
recharge(?Customer $customer, Plan $plan, string $routerName, string $gateway, string $channel, ?int $adminId = null): Transaction
```

1. Tentukan identitas: kalau `$customer` null → ini recharge voucher, `username = $channel` (kode voucher), `fullname = $gateway` (biasanya string "Voucher"), buat instance `Customer` TIDAK TERSIMPAN (`new Customer([...])`, jangan `->save()`) hanya untuk dikirim ke `HotspotDeviceInterface::addCustomer()` yang butuh type-hint `Customer`. Kalau `$customer` ada → pakai `$customer->username`/`$customer->fullname` langsung.
2. Hitung tanggal expired: `$expiresAt = now()->copy()->addMonths($plan->validity)` untuk `Months`, `->addDays()` untuk `Days`, `->addHours()` untuk `Hrs`, `->addMinutes()` untuk `Mins` — dari **waktu sekarang**, BUKAN dari expired lama (karena tidak ada extend). Pecah jadi `expiration` (date) dan `time` (time string) untuk kolom `tbl_user_recharges`/`tbl_transactions`.
3. Cari existing `UserRecharge`: `where('routers', $routerName)->where('type', $plan->type)` DAN (`where('username', $channel)` kalau voucher, atau `where('customer_id', $customer->id)` kalau bukan).
   - Kalau ADA dan **belum expired** (`!$existing->isExpired()`) → **THROW exception** (mis. `ActivePlanStillActiveException`) — customer harus tunggu sampai expired, sesuai keputusan user. JANGAN diam-diam extend atau ganti plan.
   - Kalau ADA dan **sudah expired** → UPDATE row itu di tempat (reuse, bukan create baru) — set semua kolom baru (plan_id, namebp, recharged_on/time, expiration/time baru, status='on', method="$gateway - $channel", admin_id).
   - Kalau TIDAK ADA → CREATE row baru dengan kolom yang sama.
4. Panggil `HotspotDeviceResolver::resolve($plan)->addCustomer($customerForDevice, $plan)` — bungkus try/catch, kalau gagal JANGAN throw ke pemanggil (device sync error tidak boleh block billing record), cukup log (pakai Laravel `Log::error()` dulu sebagai pengganti `Message::sendTelegram()` yang belum ada di Task #8 — nanti tinggal ganti/tambah channel notifikasi setelah Task #8 jadi).
5. Buat `Transaction` baru (SELALU insert baru, bukan update — beda dari `UserRecharge` yang bisa di-reuse): `invoice = 'INV-' . (Transaction::max('id') + 1)`, `price = 0` kalau `$gateway === 'Voucher'` (voucher dianggap sudah lunas saat generate), else `price = $plan->price`, kolom lain mirror `UserRecharge`.
6. Dispatch Laravel event `App\Events\CustomerRecharged` (bawa `Transaction` + `Plan` + info baru/perpanjang) — supaya Task #8 (notifikasi Telegram/WhatsApp) tinggal listen ke event ini nanti, tidak perlu ubah `RechargeService` lagi saat itu dikerjakan. JANGAN hardcode pemanggilan notifikasi langsung di service ini.
7. Return `Transaction` yang baru dibuat.

**Test yang harus ada** (feature test, pola sama seperti `CustomerActivationTest.php` — fake `HotspotDeviceInterface` binding, bukan device asli):
- Recharge baru untuk customer tanpa recharge aktif sebelumnya → `UserRecharge` baru dibuat, `Transaction` dibuat, `addCustomer()` device dipanggil.
- Recharge voucher (customer null) → `UserRecharge.customer_id === 0`, `Transaction.price === 0`.
- Recharge saat masih ada `UserRecharge` aktif belum expired → exception dilempar, TIDAK ada row baru/perubahan.
- Recharge saat `UserRecharge` existing sudah expired → row lama di-UPDATE (id sama), bukan row baru.

## SPEC: RADIUS REST API Endpoint

Port dari `../radius.php` (475 baris) — endpoint ini dipanggil FreeRADIUS (lihat `docs/freeradius-rest-integration.md` di folder ini untuk kontrak request/response lengkap dari developer asli). Routing bebas (lihat keputusan cutover di atas — user akan update `connect_uri` FreeRADIUS, tidak perlu match path lama `/radius.php`). Sarankan `routes/api.php` dengan controller `RadiusAuthController`, method per action, atau satu method dengan action sebagai parameter — ikuti konvensi Laravel/RESTful yang masuk akal.

4 action yang harus diimplementasikan (detail lengkap ada di `../radius.php`, baca line-by-line saat porting, banyak edge case):

1. **`authenticate`** (baris 38-130): verifikasi PAP (`username == password` untuk voucher) ATAU CHAP (`Password::chap_verify()`, lihat `../system/autoload/Password.php` baris 43-49 — algoritma kecil, gampang diport ke PHP biasa: `substr`, `hex2bin`, `md5`). Cek terhadap `tbl_customers` (username ATAU pppoe_username) atau `tbl_voucher` (kalau username==password, berarti voucher). Response: `204 No Content` kalau valid, `401` dengan `Reply-Message` kalau invalid.
2. **`authorize`** (baris 131-265): mirip `authenticate` tapi lebih lengkap — cek `UserRecharge` existing (`tbl_user_recharges` di kode asli). Kalau BELUM ada recharge existing DAN username==password (voucher pattern) → **panggil `RechargeService::recharge(null, $plan, 'radius', 'Voucher', $code)`** (voucher redemption terjadi PERSIS di sini). Kalau recharge sudah ada → panggil helper `process_radiust_rest()` (baris 354-462 di kode asli) yang menghitung reply attributes RADIUS (rate limit, data/time limit, `shared_users` concurrent check) — ini logic terpisah, port jadi method/service sendiri (mis. `RadiusReplyAttributesBuilder`), dipakai juga oleh action `accounting`.
3. **`accounting`** (baris 266-334): upsert row `rad_acct` (akumulasi `acctOutputOctets`/`acctInputOctets`, bukan overwrite — `+=`), lalu kalau `acctStatusType == 'Start'` cek data limit terlampaui pakai `process_radiust_rest()` yang sama.
4. **`post-auth`** — SUDAH DIKONFIRMASI (baca ulang `radius.php` baris 37-352): `switch ($action)` cuma punya case `authenticate`/`authorize`/`accounting`, TIDAK ADA case `post-auth`. Kalau FreeRADIUS panggil action ini, kode asli jatuh ke fallback baris 352: `show_radius_result(['Reply-Message' => 'Invalid Command : post-auth'], 401)`. Sistem produksi user berjalan normal dengan perilaku ini (lihat data `rad_acct` real di `db.txt`), jadi ini BUKAN bug yang perlu diperbaiki — **cukup replikasi persis**: endpoint Laravel juga boleh return generic 401 "Invalid Command" untuk action yang tidak dikenal (termasuk `post-auth`), tidak perlu logic khusus.

**Response format**: JSON, kode HTTP bervariasi (`200`/`204`/`401`) — fungsi `show_radius_result($array, $code)` (baris 464-475) jadi acuan format response FreeRADIUS `rest` module (lihat juga `docs/freeradius-rest-integration.md` untuk field seperti `control:Auth-Type`, `reply:Mikrotik-Rate-Limit`, dst — ini attribute RADIUS yang harus persis namanya, JANGAN diubah nama field saat porting karena FreeRADIUS parsing berdasarkan nama field ini).

**Test**: karena tidak ada environment FreeRADIUS/Mikrotik live (dikonfirmasi user), test dengan HTTP feature test biasa (`$this->postJson(...)`) mensimulasikan payload yang FreeRADIUS kirim (lihat contoh field di `docs/freeradius-rest-integration.md` bagian `data = "..."` tiap action) dan assert response JSON + HTTP code sesuai kontrak. Validasi ke FreeRADIUS/Mikrotik asli adalah task terpisah SEBELUM go-live, dicatat sebagai follow-up, bukan blocker.

## Update Status (RechargeService + RADIUS endpoint SELESAI diimplementasi)

- **`RechargeService`** (`app/Services/RechargeService.php`) SUDAH lengkap sesuai spec: `recharge(?Customer, Plan, string $routerName, string $gateway, string $channel, ?int $adminId)`. Voucher path (`$customer = null`) pakai `customer_id = 0`, `price = 0`. `UserRecharge` di-reuse (update in place) kalau existing row sudah expired, throw `App\Exceptions\ActivePlanStillActiveException` kalau masih aktif. Selalu insert `Transaction` baru. Dispatch event `App\Events\CustomerRecharged` (bukan panggil notifikasi langsung — Task #8 tinggal listen). `CustomerActivationService` yang lama (simplified) **sudah DIHAPUS**, tidak ada pemanggil lain selain test lama yang juga sudah dihapus/diganti `RechargeServiceTest`.
- **RADIUS REST API endpoint** SUDAH diimplementasi di `routes/api.php` (`POST /api/radius`) → `App\Http\Controllers\RadiusAuthController@handle`. Dispatch berdasar `action` (query/body param atau header `X-FreeRadius-Section`) ke 3 method: `authenticate`, `authorize`, `accounting`. Action tak dikenal (termasuk `post-auth`) → 401 "Invalid Command" (replikasi persis perilaku asli, BUKAN bug yang perlu diperbaiki).
  - Helper classes di `app/Services/Radius/`: `ChapAuthenticator` (port literal `Password::chap_verify()`, TERMASUK comparison `!=` yang terlihat aneh — sengaja tidak "diperbaiki", replikasi persis dari sistem lama), `RadiusIdentityResolver` (resolve CHAP/PAP/voucher identity dari request, dipakai `authenticate` & `authorize` — **catatan penting**: `authorize` seed `$isVoucher = ($username === $password)` di awal SEBELUM resolusi CHAP/plain, beda dari `authenticate` yang tidak; ini eksplisit di-handle di constructor `RadiusIdentityResolver::resolve()`), `RadiusReplyAttributesBuilder` (port `process_radiust_rest()` — rate limit, data/time limit, `shared_users` concurrent check, dan expiry check, throw `App\Exceptions\RadiusRejectedException` untuk semua reject path).
  - **KEPUTUSAN PENTING (baru dikonfirmasi user)**: `Customer.password` **TIDAK di-hash** (cast `'hashed'` sudah DIHAPUS dari model) — disimpan **plaintext**, persis seperti sistem lama, karena RADIUS PAP/CHAP butuh password asli untuk verifikasi/challenge (mustahil verify CHAP terhadap bcrypt hash). User pilih opsi ini secara eksplisit: **"plaintext dulu sebagai yang stabil, CHAP kemudian sebagai experimental"** — artinya PAP (plain password match) adalah jalur utama yang harus solid duluan, CHAP masih ada di kode (sudah diport) tapi anggap belum battle-tested sampai divalidasi ke Mikrotik/FreeRADIUS asli. **Dampak ke Task #5 (customer auth/portal login)**: JANGAN pakai `Hash::check()` untuk cek password Customer di guard `customer` nanti — bandingkan string plain biasa (`$customer->password === $inputPassword`), sesuai keputusan ini.
  - `CustomerFactory` sudah disesuaikan (`'password' => 'password'`, bukan `bcrypt('password')`).
- **12 test pass semua** (`php artisan test --compact`): `RechargeServiceTest` (4), `RadiusAuthTest` (6, simulasi payload FreeRADIUS via `postJson('/api/radius', ...)`), `CustomerActivationTest`+`ExampleTest` versi lama sebelumnya (sekarang tergantikan, total test suite 12).
- `php artisan install:api` sudah dijalankan untuk mengaktifkan `routes/api.php` (sebelumnya belum ada di skeleton Laravel 13 minimal) — ini juga menginstall **Laravel Sanctum** (migration `personal_access_tokens` sudah jalan) sebagai efek samping standar scaffolding, TIDAK dipakai aktif oleh endpoint RADIUS (endpoint ini public, tidak pakai `auth:sanctum` middleware — proteksi akses sebaiknya di level network/firewall karena FreeRADIUS yang panggil, bukan browser).

## Verifikasi Independen (audit terpisah, sesi lain — semua klaim di section "Update Status" di atas SUDAH dicek langsung ke disk + `php artisan test --compact`, bukan cuma percaya narasi)

- Semua file yang diklaim ada memang ada (`RechargeService.php`, `RadiusAuthController.php`, `ChapAuthenticator.php`, `RadiusIdentityResolver.php`, `RadiusReplyAttributesBuilder.php`, `CustomerRecharged.php`, `ActivePlanStillActiveException.php`, `RadiusRejectedException.php`, 3 test file).
- **12/12 test pass, terverifikasi ulang** (`php artisan test --compact` → `{"tests":12,"passed":12,"assertions":24}`).
- Isi `RechargeService.php` dan `RadiusAuthController.php` dibaca langsung, cocok dengan spec di atas (reuse `UserRecharge` kalau expired, throw exception kalau masih aktif, `Transaction` selalu insert baru, device sync error di-swallow+log bukan throw, voucher pakai `customer_id=0`/`price=0`). Port `ChapAuthenticator`/`Customer` model juga sudah punya komentar kode yang menjelaskan *kenapa* (bukan cuma replikasi buta) — kualitas port bagus.

**2 catatan kecil ditemukan saat audit ini (belum blocker, tapi jangan lupa):**

1. **`RadiusAuthController::accounting()` tidak refresh kolom `dateAdded` saat UPDATE row `rad_acct` existing** — cuma keisi otomatis via `useCurrent()` migration saat INSERT pertama kali. Kode asli SELALU set `$d->dateAdded = date('Y-m-d H:i:s')` di tiap panggilan accounting (baris 304 `radius.php`), berfungsi sebagai timestamp "last seen" sesi. Perlu ditambahkan `'dateAdded' => now()` ke array `$data` di `accounting()` kalau ada fitur ke depan yang bergantung ke freshness timestamp ini (mis. deteksi sesi mati/stale).
2. **Dampak password plaintext ke Task #12 (Forgot Password), belum ditulis eksplisit sebelumnya** — kalau reset password customer nanti pakai `Hash::make($newPassword)` (default Laravel `ResetPasswordController`/Fortify), itu akan menyimpan hash padahal RADIUS PAP/CHAP butuh plaintext untuk verifikasi. **Reset password Customer (bukan admin `tbl_users`) HARUS assign plain string langsung**, sama seperti keputusan Task #5. Admin (`tbl_users`) beda cerita — itu boleh tetap di-hash normal karena tidak dipakai RADIUS.

## Update Status (Task #5 Customer auth SELESAI)

- **`dateAdded` di `RadiusAuthController::accounting()` sudah diperbaiki** — sekarang selalu di-set `now()` di array `$data`, mirror perilaku kode asli.
- **Guard `customer` SUDAH ada di `config/auth.php`**: guard `customer` (session driver, provider `customers`), provider `customers` → `App\Models\Customer`, plus password-reset broker `customers` (disiapkan untuk Task #12 nanti, belum dipakai).
- **Routes** (`routes/web.php`, prefix `/customer`, name prefix `customer.`): `GET/POST /customer/login` (middleware `guest:customer`), `POST /customer/logout` + `GET /customer/dashboard` (middleware `auth:customer`). Controller di `app/Http/Controllers/Customer/` (`AuthController`, `DashboardController`). View minimal (belum di-styling, cuma functional) di `resources/views/customer/` (`login.blade.php`, `dashboard.blade.php`) — Task #7 nanti akan perluas dashboard ini jadi portal order/renew sungguhan.
- **Login TIDAK pakai `Auth::attempt()`/`Hash::check()`** — sesuai keputusan password plaintext, `AuthController::login()` query manual `Customer::where('username', ...)->where('password', $plainInput)->where('status', 'Active')->first()`, baru `Auth::guard('customer')->login($customer)`.
- **`bootstrap/app.php`** ditambah `$middleware->redirectGuestsTo(fn () => route('customer.login'))` — perlu karena middleware `auth:customer` bawaan Laravel akan redirect ke route bernama `login` yang tidak ada (Filament admin pakai routing sendiri, bukan route `login` biasa). Kalau nanti ada guard `web` lain yang juga butuh redirect berbeda, closure ini perlu diperluas (cek `$request->is(...)`).
- **6 test baru** di `tests/Feature/CustomerAuthTest.php`: login sukses, password salah, customer `Inactive` ditolak, dashboard accessible setelah login, guest di-redirect ke login, guard admin (`web`) tidak kepengaruh guard `customer`. **Total test suite sekarang 18, semua pass** (`php artisan test --compact` → `{"tests":18,"passed":18,"assertions":40}`).

## SPEC: Order/QR Flow (Task #7) — KOREKSI TOTAL dari asumsi awal, baca sebelum implementasi

**Belum ada kode ditulis untuk ini — masih spec only.** Asumsi lama ("customer upload bukti transfer") SALAH, sudah dikonfirmasi ulang ke user. Alur yang benar:

1. **QR cuma SATU gambar statis**, bukan digenerate per transaksi (bukan QRIS dinamis dengan nominal tertampil). Admin upload sekali via halaman Settings, dipakai terus untuk semua order sampai diganti manual.
   - Storage: simpan sebagai file di `storage/app/public` (via Filament `FileUpload` component, `disk('public')`), path-nya disimpan sebagai value di `tbl_appconfig` dengan key mis. `payment_qr_path` (tabel ini sudah ada & sudah dipakai untuk general settings di sistem lama — konsisten dengan pola aslinya meski key ini baru).
   - UI: custom Filament Page (bukan Resource penuh — cuma butuh 1 field upload + save), taruh di `app/Filament/Pages/PaymentSettings.php` atau sejenis.
2. **Order dibuat MANUAL oleh admin**, bukan customer. Admin pilih Customer + Plan di admin panel (Filament) → sistem buat record order baru berstatus "pending". Customer **TIDAK** submit order sendiri dan **TIDAK** upload apapun.
3. **Tabel BARU dibutuhkan**: `tbl_orders` — **INI TABEL BARU, TIDAK ADA DI 23 TABEL SKEMA ASLI PHPNUXBILL** (skema asli tidak punya konsep "pending order menunggu konfirmasi manual" untuk alur non-gateway seperti ini; `tbl_payment_gateway` yang mirip sudah eksplisit out-of-scope). Kolom yang disarankan:
   - `id`, `customer_id` (FK `tbl_customers`), `plan_id` (FK `tbl_plans`), `price` (snapshot harga plan saat order dibuat, supaya kalau plan price berubah setelahnya tidak mempengaruhi order lama), `status` (enum: `pending`/`paid`/`cancelled`), `invoice_token` (string random unik, untuk link publik — pola mirip `voucher.php` lama yang pakai `md5($id.$db_pass)`, tapi lebih aman pakai `Str::random(32)` atau `Str::uuid()`), `admin_id` (siapa yang buat), `created_at`, `paid_at` (nullable). Router TIDAK perlu kolom terpisah — ambil dari `$order->plan->routers` (plan sudah terikat ke 1 router by design, kolom `tbl_plans.routers`).
   - Model `Order` — relasi `belongsTo(Customer::class)` dan `belongsTo(Plan::class)`.
4. **Link invoice publik** (bisa dibuka dari WA tanpa login): route `GET /invoice/{order:invoice_token}` (route model binding by token, bukan by id — supaya ID order tidak predictable), view menampilkan: nama plan, harga, nama customer, gambar QR statis (ambil dari `tbl_appconfig` key `payment_qr_path`), status order. Tidak perlu middleware auth sama sekali (memang didesain publik, itulah gunanya token acak di URL).
5. **Kirim WA otomatis saat order dibuat**: dispatch event `App\Events\OrderCreated` (bawa `Order $order`) segera setelah order disimpan — Task #8 (belum dikerjakan) nanti listen ke event ini untuk kirim pesan WA berisi ringkasan + link invoice dari poin 4. Pola sama seperti `CustomerRecharged` — JANGAN hardcode pemanggilan WA langsung di controller/action pembuatan order.
6. **Admin approve manual**: Filament table action "Mark as Paid" di `OrderResource` (baru, belum ada) → panggil `RechargeService::recharge($order->customer, $order->plan, $order->plan->routers, 'QR Payment', $order->invoice_token, auth()->id())`. Bungkus try/catch untuk `ActivePlanStillActiveException` (tampilkan notifikasi Filament yang jelas ke admin, JANGAN silent fail) — kasus ini bisa terjadi kalau admin approve order padahal customer masih ada plan aktif yang belum expired. Kalau sukses: `$order->update(['status' => 'paid', 'paid_at' => now()])`.
7. **Action "Cancel"** juga disarankan (status → `cancelled`) untuk order yang tidak pernah dibayar, supaya tidak menggantung selamanya di status pending — housekeeping sederhana, bukan requirement inti.
8. **Dashboard customer** (`resources/views/customer/dashboard.blade.php`, sudah ada dari Task #5) cukup diperluas untuk **menampilkan** riwayat `Order`/`Transaction` milik customer yang login (read-only) — TIDAK perlu form/tombol "buat order baru" di sana, karena order selalu diinisiasi admin.

**Test yang disarankan**: feature test untuk (a) admin membuat order → row `tbl_orders` status pending + event `OrderCreated` dispatched (pakai `Event::fake()`), (b) akses `GET /invoice/{token}` publik tanpa login berhasil menampilkan data yang benar, (c) admin approve order → `RechargeService::recharge()` terpanggil, status jadi `paid`, (d) approve order untuk customer yang masih ada plan aktif → exception ditangani dengan baik, status TETAP pending (tidak berubah jadi paid).

## Update Status (Task #6 & #7 SELESAI)

- **Task #6 (Multi-router CRUD)**: dianggap selesai lewat `RouterResource` Filament yang sudah ada dari Task #2 (create/list/edit CRUD lengkap untuk `tbl_routers`) — tidak ada requirement tambahan spesifik di task list untuk item ini, jadi tidak ada kerjaan baru.
- **Task #7 (Order/QR Flow)** SUDAH diimplementasi PERSIS sesuai spec di atas:
  - **Migration baru** `database/migrations/..._create_tbl_orders_table.php` → tabel `tbl_orders` (`customer_id` FK, `plan_id` FK, `price`, `status` enum pending/paid/cancelled, `invoice_token` unique, `admin_id`, `created_at`, `paid_at`). Model `App\Models\Order` — route key `invoice_token` (bukan `id`, jadi route model binding otomatis pakai token, bukan predictable integer id), relasi `customer()`/`plan()`.
  - **Model baru** `App\Models\AppConfig` (`tbl_appconfig`, tabel lama yang sebelumnya belum punya model) — helper statis `AppConfig::get($key, $default)` / `AppConfig::set($key, $value)`, dipakai untuk simpan path QR statis di key `payment_qr_path`.
  - **`App\Services\OrderService`**: `create(Customer, Plan, ?adminId): Order` (buat order pending + dispatch `App\Events\OrderCreated`), `markAsPaid(Order, ?adminId): Order` (panggil `RechargeService::recharge()` lalu update status jadi `paid` + `paid_at`, biarkan `ActivePlanStillActiveException` menjalar ke caller — TIDAK di-catch di service ini, caller/UI yang harus handle), `cancel(Order): Order`.
  - **`App\Filament\Pages\PaymentSettings`** (custom Filament page, bukan Resource) — 1 field `FileUpload` (disk `public`, folder `payment-qr`) untuk upload QR statis, submit via `save()` yang panggil `AppConfig::set()`. `php artisan storage:link` sudah dijalankan supaya file ke-serve dari `public/storage`.
  - **`App\Filament\Resources\OrderResource`**: form cuma `customer_id` + `plan_id` (Select searchable) — kolom lain (`price`, `status`, `invoice_token`, `admin_id`) di-generate otomatis lewat `OrderService::create()`, BUKAN diinput manual. `CreateOrder` page di-override (`handleRecordCreation()`) supaya lewat `OrderService`, bukan Eloquent create biasa. Table actions: **"Mark as Paid"** (visible cuma kalau `status=pending`, panggil `OrderService::markAsPaid()`, catch `ActivePlanStillActiveException` → tampilkan Filament `Notification` danger, JANGAN silent fail) dan **"Cancel"**. Tidak ada Edit page (order tidak boleh diedit manual setelah dibuat, cuma lewat action).
  - **Invoice publik**: `GET /invoice/{order:invoice_token}` (route model binding by token) → `App\Http\Controllers\InvoiceController@show`, view `resources/views/invoice/show.blade.php` (plain HTML, tampilkan nama customer/plan/harga/status, gambar QR dari `storage/{path}` kalau status masih `pending`). TIDAK ada middleware auth (memang publik by design).
  - **`resources/views/customer/dashboard.blade.php`** diperluas: list riwayat `Order` (dengan link ke invoice publik) DAN `Transaction` milik customer yang login — read-only, tidak ada form buat order baru (sesuai spec, order selalu diinisiasi admin).
  - **4 test baru** di `tests/Feature/OrderFlowTest.php`: (a) `OrderService::create()` bikin row pending + dispatch event (`Event::fake()`), (b) invoice publik accessible tanpa login & tampilkan data benar, (c) `markAsPaid()` sukses → status `paid` + `UserRecharge` dibuat (device di-fake, bukan Mikrotik asli), (d) `markAsPaid()` untuk customer yang masih ada plan aktif → exception dilempar, status TETAP `pending` (verified via `$order->fresh()`). **Total test suite sekarang 22, semua pass** (`{"tests":22,"passed":22,"assertions":51}`).
  - **Efek samping kecil**: `install:api` sebelumnya sudah bawa Sanctum; sekarang juga sudah ada `public/storage` symlink untuk file upload QR — kalau deploy ke server baru, jangan lupa `php artisan storage:link` lagi (symlink tidak ikut ke-commit ke git).

## Update Status (Task #8 SELESAI)

- **`App\Models\MessageLog`** (`tbl_message_logs`, tabel lama yang sebelumnya belum punya model) — `CREATED_AT = 'sent_at'`, `UPDATED_AT = null`.
- **`App\Services\NotificationService`**: `sendTelegram(string $text, ?string $chatId = null)` dan `sendWhatsapp(string $phone, string $text)` — port ringkas dari `Message::sendTelegram()`/`sendWhatsapp()` (TANPA sistem template placeholder `[[name]]` dkk dari kode asli — disederhanakan, pesan dirakit langsung di listener sebagai string, cukup untuk skala personal). Baca config dari `AppConfig` (`telegram_bot`, `telegram_target_id`, `wa_url` dengan placeholder `[number]`/`[text]` persis seperti kode asli). Kalau config kosong → no-op, tidak error. Semua percobaan kirim (sukses/gagal) di-log ke `MessageLog`.
- **2 listener baru** di `app/Listeners/`, auto-registered oleh Laravel (BUKAN didaftarkan manual di `AppServiceProvider` — **PENTING, catat baik-baik**: Laravel 11+/13 auto-discover listener dari signature `handle(EventType $event)` di `app/Listeners/*.php`, JANGAN tambah `Event::listen()` manual di `AppServiceProvider::boot()` untuk listener yang sudah ada di folder ini, soalnya bakal DOUBLE-FIRE — event terkirim 2x. Ini sempat kejadian & sudah diperbaiki saat implementasi, lihat komentar di `AppServiceProvider::boot()`):
  - `SendOrderCreatedNotification` (listen `OrderCreated`) — WA ke customer (link invoice) + Telegram ke admin.
  - `SendCustomerRechargedNotification` (listen `CustomerRecharged`) — WA ke customer (info expiry baru) + Telegram ke admin. Customer dicari via `Customer::where('username', $transaction->username)` (bisa `null` untuk recharge voucher — WA di-skip kalau customer tidak ditemukan, Telegram ke admin tetap jalan).
- **`App\Filament\Pages\NotificationSettings`** (`/admin/notification-settings`) — form 3 field (`telegram_bot`, `telegram_target_id`, `wa_url`) simpan ke `AppConfig`, pola sama persis seperti `PaymentSettings` dari Task #7.
- **5 test baru** di `tests/Feature/NotificationTest.php`: kirim Telegram sukses (`Http::fake` + assert URL & log), no-op kalau config kosong, kirim WA dengan placeholder replacement, dan 2 test integrasi event (`OrderCreated`/`CustomerRecharged` masing-masing memicu tepat 2 HTTP call: WA + Telegram). **Total test suite sekarang 27, semua pass** (`{"tests":27,"passed":27,"assertions":59}`).

## Update Status (Task #9 SELESAI)

- **`App\Services\VoucherService::generate(Plan, int $quantity, int $codeLength=8, ?int $adminId=null): Collection<Voucher>`** — generate N voucher sekaligus, kode random uppercase alphanumeric unik (`Str::random()` + cek `exists()` sampai unik — TIDAK ada custom charset exclusion untuk karakter ambigu 0/O/1/l, cukup untuk skala kecil). Voucher SELALU dibuat dengan `routers = 'radius'` (**PENTING**: ini harus persis `'radius'`, bukan nama router Mikrotik — karena `RadiusAuthController::authorize()` filter voucher lookup pakai `where('routers', 'radius')`, lihat Task #7-radius. Kalau field ini salah, voucher generate tapi tidak akan pernah bisa diaktivasi via RADIUS) dan `status = '0'` (unused).
- **`VoucherResource`** (Filament) ditambah:
  - Header action **"Generate Vouchers"** — modal form (Plan select, quantity, code_length), panggil `VoucherService::generate()`, notifikasi jumlah yang berhasil dibuat.
  - Bulk action **"Print Selected"** pada tabel — buka tab baru ke route print dengan query `ids` dari record yang dicentang.
  - Form create/edit manual masih ada (untuk 1 voucher ad-hoc), sekarang pakai `Select` untuk `type`/`status`/`id_plan` (bukan `TextInput` polos lagi, sedikit perbaikan dari Task #2).
- **`App\Http\Controllers\VoucherPrintController@show`** (`GET /admin/vouchers/print?ids=1,2,3`, middleware `auth:web`) → view `resources/views/voucher/print.blade.php` (grid card sederhana per voucher: nama plan + kode besar + tombol "Print" yang panggil `window.print()`, ada `@media print` buat sembunyiin tombol saat print).
  - **`bootstrap/app.php` `redirectGuestsTo` diperluas**: sekarang cek `$request->is('admin/*')` → redirect ke `route('filament.admin.auth.login')` (bukan `customer.login`), karena route print ini pakai guard `web` biasa (di luar Filament panel routing) tapi tetap perlu redirect yang benar kalau belum login. Rute non-`admin/*` (customer-facing) tetap ke `customer.login` seperti sebelumnya.
- **`database/factories/UserFactory.php` diperbaiki** — sebelumnya masih pakai skema default Laravel (`name`, `email_verified_at`) yang TIDAK cocok dengan `tbl_users` yang sudah di-repurpose (kolom `username`, `fullname`, `user_type`, `status`, `creationdate` NOT NULL tanpa default). Baru ketahuan sekarang karena baru kali ini ada test yang butuh `User::factory()` (buat simulasi admin login). Kalau ada test lain nanti yang pakai `User::factory()` dan gagal karena constraint kolom, cek dulu apakah factory ini sudah sesuai kolom migration `tbl_users` sebelum debug lebih jauh ke tempat lain.
- **3 test baru** di `tests/Feature/VoucherGenerationTest.php`: generate 20 voucher → kode unik semua + `routers='radius'`/`status='0'` konsisten, print route redirect kalau belum login admin, print route tampilkan kode voucher yang benar setelah login. **Total test suite sekarang 30, semua pass** (`{"tests":30,"passed":30,"assertions":69}`).

## ✅ KOREKSI GOWA SUDAH DIPERBAIKI (lihat detail asli di bawah untuk konteks kenapa)

**Ditemukan saat audit lanjutan, setelah Task #8 "selesai".** User sudah eksplisit konfirmasi WA gateway yang dipakai adalah **GOWA** (self-hosted, `127.0.0.1:3030`, dokumentasi lengkap di `docs/gowa-wa-gateway.md`), TAPI implementasi `sendWhatsapp()` yang sudah ditulis (`app/Services/NotificationService.php`) masih pakai pola LAMA: `Http::get($url)` dengan `wa_url` sebagai URL template (`[number]`/`[text]` placeholder, gaya `Message::sendWhatsapp()` di kode PHP asli).

**Kenapa ini salah**: GOWA butuh:
- Method `POST`, bukan `GET`.
- Body JSON `{"phone": "{62xxx}@s.whatsapp.net", "message": "..."}`, bukan query string di URL.
- Header `X-Device-Id: {device_id}`.
- Basic Auth (`alt_wga_username`/`alt_wga_password`).
- Nomor telepon harus diformat: strip leading `0` → prepend country code (`62`) → tambah suffix `@s.whatsapp.net`.

Pola `Http::get($url)` dengan template GET **tidak bisa** memenuhi kontrak ini sama sekali — beda method HTTP, beda struktur body, tidak ada tempat untuk auth header/device header di URL template biasa. Kalau dipakai apa adanya ke GOWA asli, kirim WA akan gagal terus (401/404/405 tergantung endpoint), meski test-nya PASS (karena test cuma `Http::fake()` generic, tidak validasi kontrak GOWA yang sesungguhnya).

**Yang perlu diperbaiki** (spec, belum dikerjakan):
1. Ganti `sendWhatsapp()` di `NotificationService` jadi:
   ```
   Http::withBasicAuth(AppConfig::get('alt_wga_username'), AppConfig::get('alt_wga_password'))
       ->withHeaders(['X-Device-Id' => AppConfig::get('alt_wga_device_id')])
       ->post(AppConfig::get('alt_wga_server_url') . '/send/message', [
           'phone' => $formattedPhone . '@s.whatsapp.net',
           'message' => $text,
       ]);
   ```
2. Tambah helper format nomor telepon (port `Lang::phoneFormat()`, `../system/autoload/Lang.php` baris 67-75): strip leading `0`, prepend `AppConfig::get('country_code_phone', '62')`.
3. **`App\Filament\Pages\NotificationSettings`** (sudah dibuat Task #8) field-nya masih `telegram_bot`/`telegram_target_id`/`wa_url` — **HARUS diganti** jadi `telegram_bot`/`telegram_target_id`/`alt_wga_server_url`/`alt_wga_device_id`/`alt_wga_username`/`alt_wga_password`/`country_code_phone` (field `wa_url` dihapus, sudah tidak relevan untuk GOWA direct).
4. Response GOWA envelope-nya `{code, message, results: {message_id, status}}` (lihat `docs/gowa-wa-gateway.md` schema `SendResponse`, baris ~4373) — kalau mau cek sukses/gagal lebih presisi dari sekedar HTTP status code, bisa parse `code === 'SUCCESS'` dari body, bukan cuma `$response->successful()`.
5. **Test yang sudah ada di `NotificationTest.php` perlu direvisi**: assert `Http::fake()` sekarang harus cek method POST + header `X-Device-Id` + Basic Auth + body JSON yang benar, bukan cuma assert GET ke URL template.

**Dampak**: 27 test yang "pass" untuk Task #8 sebelumnya tidak membuktikan integrasi WA benar-benar jalan ke gateway asli — ini kasus klasik "test hijau tapi salah kontrak eksternal", persis alasan kenapa audit independen ini penting.

### Perbaikan yang SUDAH diterapkan (sesi ini)

1. **`NotificationService::sendWhatsapp()`** ditulis ulang — sekarang `Http::withBasicAuth(alt_wga_username, alt_wga_password)->withHeaders(['X-Device-Id' => alt_wga_device_id])->post("{alt_wga_server_url}/send/message", ['phone' => "{62xxx}@s.whatsapp.net", 'message' => $text])`. Sukses ditentukan dari `$response->successful() && $response->json('code') === 'SUCCESS'` (parse body GOWA, bukan cuma HTTP status).
2. **Helper `formatPhone()`** ditambahkan (private method di `NotificationService`) — port `Lang::phoneFormat()`: kalau nomor semua digit, ganti prefix `0` di awal dengan `country_code_phone` dari `AppConfig` (default `'62'`).
3. **`App\Filament\Pages\NotificationSettings`** field diganti total: `telegram_bot`, `telegram_target_id`, `alt_wga_server_url`, `alt_wga_device_id`, `alt_wga_username`, `alt_wga_password` (pakai `->password()->revealable()`), `country_code_phone`. Field `wa_url` LAMA sudah dihapus total dari form (tapi kalau ada row lama di DB dengan key itu, tidak otomatis dibersihkan — tidak masalah, sudah tidak dibaca kode manapun).
4. **`tests/Feature/NotificationTest.php` direvisi**: `test_send_whatsapp_posts_to_gowa_with_auth_and_device_header` (assert method POST, header `X-Device-Id`, Basic Auth via `Authorization` header, body `phone`/`message` sesuai kontrak GOWA, termasuk verifikasi format nomor `081234567890` → `6281234567890@s.whatsapp.net`) + `test_send_whatsapp_does_nothing_when_not_configured`. 2 test integrasi event (`OrderCreated`/`CustomerRecharged`) di-update untuk pakai `configureGowa()` helper alih-alih `wa_url` lama.
5. **6 test WA/notifikasi + 31 total test suite, semua pass** (`{"tests":31,"passed":31,"assertions":71}`).

**Task #8 sekarang BENAR-BENAR selesai** (sebelumnya cuma "selesai" secara struktur kode tapi salah kontrak eksternal).

## 📋 STATUS RINGKAS — Apa yang Sudah & Belum (per audit terbaru, terverifikasi ke disk + test suite)

**Terverifikasi ulang barusan**: 31/31 test pass, `NotificationService` sudah benar pakai kontrak GOWA (`alt_wga_server_url` ditemukan di kode). `app/Console/Commands` **tidak ada sama sekali** (folder belum dibuat), tidak ada file `ActivityLog`/`LogResource`/`ForgotPassword`/`ResetPassword`/`Report`/`MessageLogResource` di manapun — jadi 4 item di bawah ini genuinely nol progres, bukan asumsi.

### ✅ SELESAI (Task #1–#9, semua terverifikasi lewat file + test, bukan cuma klaim)
1. Setup Laravel + Eloquent ke skema existing (SQLite, 13 migration, semua model)
2. Admin auth (Filament, guard `web` → `tbl_users`) + CRUD Customer/Plan/Voucher/Router/Bandwidth
3. Mikrotik + RadiusRest integration (`HotspotDeviceInterface`, `MikrotikHotspotService`, `RadiusRestService`)
4. RADIUS REST API endpoint (`authenticate`/`authorize`/`accounting`) + `RechargeService` (gap besar yang ditemukan di tengah jalan, sudah tertutup)
5. Customer auth (guard `customer` terpisah, login plaintext-compatible)
6. Multi-router CRUD (`RouterResource`, trivial lewat Task #2)
7. Order/QR flow (`tbl_orders` baru, `OrderService`, invoice publik via token, admin approve manual — **desain final, BUKAN "upload bukti" seperti asumsi pertama**)
8. Notifikasi Telegram + WhatsApp via GOWA (**sempat salah kontrak API, sudah diperbaiki dan diverifikasi ulang**)
9. Voucher generate + print (`VoucherService::generate()`, print route, `routers='radius'` wajib biar bisa diaktivasi RADIUS)

### ✅ Task #10–#13 SEKARANG JUGA SUDAH SELESAI (dikerjakan setelah audit "❌ belum dikerjakan" di atas ditulis — bacaan di atas SUDAH USANG per baris ini, jangan dipercaya lagi, lihat status akurat di bawah)

**PENTING untuk sesi berikutnya**: kalau ketemu bagian "❌ BELUM DIKERJAKAN" di atas, itu snapshot dari SEBELUM keempat task ini dikerjakan — sudah tidak akurat. User minta lanjut #10→#11→#12→#13 sekaligus dalam satu sesi, test ditulis di akhir (bukan TDD per-task). **Total test suite sekarang 49, semua pass** (`{"tests":49,"passed":49,"assertions":108}`, terverifikasi `php artisan test --compact`).

**Task #10 — Cron/scheduler**: `App\Console\Commands\CheckExpiredPlans` (`php artisan app:check-expired-plans`), dijadwalkan `Schedule::command(...)->dailyAt('07:00')` di `routes/console.php`. Disable expired: query `UserRecharge` `status='on'` + `expiration<=today` (dicek presisi sampai jam via `expiration+time`), panggil `HotspotDeviceResolver::resolve($plan)->removeCustomer()`, set `status='off'` (port `cron.php` asli — dikonfirmasi nilainya persis `'off'`), kirim WA. Reminder H-1/H-3/H-7 (skip voucher `customer_id=0`, port `cron_reminder.php`) dalam command yang sama. **CATATAN DEPLOYMENT**: scheduler Laravel butuh cron OS-level (`* * * * * php artisan schedule:run`) supaya benar-benar jalan — belum di-setup, itu tugas deployment nanti.

**Task #11 — Activity log**: `App\Models\ActivityLog` (`tbl_logs`) + `App\Services\ActivityLogger::log()` (no-op kalau tidak ada admin `web` guard yang login, supaya data seed/test tidak ikut ter-log). Trait `App\Models\Concerns\LogsActivity` (hook Eloquent `created`/`updated`/`deleted`) dipasang di `Customer`, `Plan`, `Router` — **SENGAJA TIDAK** di `Voucher` (supaya generate 100+ voucher tidak spam 100+ baris log; `VoucherService::generate()` nulis 1 baris ringkasan manual sebagai gantinya). `OrderService::markAsPaid()`/`cancel()` juga nulis log manual (event bisnis, bukan raw CRUD). `App\Listeners\LogAdminLogin`/`LogAdminLogout` (listen `Illuminate\Auth\Events\Login`/`Logout` BAWAAN Laravel, filter `guard==='web'` biar login customer tidak ikut ke audit trail admin). **Belum ada viewer UI** (sesuai simplifikasi yang diizinkan di awal) — kalau perlu nanti tinggal bikin `ActivityLogResource` read-only.

**Task #12 — Forgot Password**: mekanisme **OTP 6-digit via WhatsApp** (port simplified dari `forgot.php` + commit lama `c3c2a92d` soal lockout — file-cache diganti Laravel `Cache` facade, TTL 10 menit, key `forgot-password:{guard}:{sha1(username)}`). `App\Services\PasswordResetOtpService::requestOtp()`/`verifyAndReset()` — attempts budget 5x, generic no-op kalau username tidak ketemu (anti-enumeration), password baru `Str::password(10)` (CSPRNG): **Customer plaintext langsung** (konsisten keputusan RADIUS), **User/admin di-`Hash::make()`**. Routes+controller terpisah: `Customer\ForgotPasswordController` (`/customer/forgot-password`) dan `Admin\ForgotPasswordController` (**`/admin-forgot-password`, SENGAJA DI LUAR prefix `/admin/`** karena itu sepenuhnya dikuasai routing Filament panel).

**Task #13 — Laporan income**: `App\Services\ReportService::incomeByDay()`/`totalIncome()` (query `Transaction`, group by `recharged_on`). `App\Filament\Pages\IncomeReport` (`/admin/income-report`) — form date range, tabel Blade biasa (bukan Filament Table component, karena data hasil agregasi bukan Eloquent record langsung).

**⚠️ Bug tersembunyi yang ketemu & diperbaiki saat kerjakan #10/#13 (PENTING)**: `UserRecharge`/`Transaction` model punya cast kolom date-only (`recharged_on`, `expiration`) yang cuma `'date'` polos, BUKAN `'date:Y-m-d'`. Laravel **tidak otomatis truncate format saat SAVE** kalau cast-nya begitu — nilai tersimpan di DB jadi `"2026-08-09 00:00:00"` (datetime lengkap), bukan `"2026-08-09"`. Ini tidak kelihatan selama ini karena semua kode baca ulang lewat accessor Carbon (`$model->expiration->toDateString()`, otomatis benar). **Baru ketahuan** pas Task #10 (`whereIn('expiration', [...])` raw string compare, gagal match) dan Task #13 (`groupBy('recharged_on')` raw SQL, group key jadi datetime bukan date). **Sudah diperbaiki**: cast diganti `'date:Y-m-d'` di kedua model. **Kalau nambah model baru dengan kolom date-only, SELALU pakai `'date:Y-m-d'`**, jangan `'date'` polos.

### 🔶 Item cross-cutting, bukan "task" tapi WAJIB sebelum go-live production

1. **Validasi CHAP + GOWA + Laravel Scheduler ke environment live** — semua baru diverifikasi lewat unit/feature test dengan fake HTTP/tanpa cron OS asli. BELUM PERNAH dites ke FreeRADIUS/Mikrotik asli, instance GOWA asli (`127.0.0.1:3030`), atau cron job nyata. Bukan pekerjaan kode, tapi langkah validasi manual yang tidak boleh dilewati sebelum cutover produksi.
2. **`MessageLogResource`**/**`ActivityLogResource`** (viewer read-only untuk `tbl_message_logs`/`tbl_logs`) belum dibuat — bukan blocker fungsional, worth ditambah kalau sempat.
3. Styling (customer login/dashboard/forgot-password, admin forgot-password, invoice publik, print voucher, income report) masih plain HTML/Blade tanpa CSS — fungsional tapi belum dipercantik.
4. Filament enum fields (Customer/Plan/Router) masih `TextInput`, belum `Select` — kosmetik, prioritas rendah.

### Catatan teknis penting untuk sesi lanjutan (jangan diulang kesalahannya)

- **JANGAN daftarkan listener manual di `AppServiceProvider`** kalau ditaruh di `app/Listeners/` dengan signature `handle(EventType $event)` — auto-discovery Laravel 13 sudah menangani, manual registration bikin event ke-fire 2x (sempat kejadian di Task #8, sudah diperbaiki).
- `User::factory()` (admin `tbl_users`) sudah diperbaiki sesuai kolom migration asli (Task #9) — jangan diubah lagi kecuali migration-nya berubah duluan.
- `AppConfig` key WA sekarang `alt_wga_*` + `country_code_phone`, BUKAN `wa_url` (sudah usang, jangan dipakai lagi di kode baru).
- **Model dengan kolom date-only WAJIB cast `'date:Y-m-d'`, bukan `'date'` polos** — lihat bug/fix Task #10/#13 di atas sebelum menulis raw query/grouping terhadap kolom tanggal manapun.
- **SEMUA 13 TASK LIST ITEM SUDAH SELESAI** per titik ini. Yang tersisa murni item polish/validasi di section "🔶 cross-cutting" di atas — bukan fitur yang belum ada.
- Setiap kali audit menemukan "task X selesai" dari sesi lain, **jangan langsung percaya** — cek file ada di disk + jalankan `php artisan test --compact` sendiri, seperti pola yang konsisten dipakai sepanjang dokumen ini (sudah 2 kali menemukan klaim keliru: Task #5 awal & kontrak GOWA Task #8).
