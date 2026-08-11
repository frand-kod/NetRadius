# PHPNuxBill → Laravel (Personal Rewrite)

Migrasi billing hotspot Mikrotik dari [PHPNuxBill](https://github.com/hotspotbilling/phpnuxbill) (PHP prosedural lama) ke Laravel 13, untuk pemakaian **pribadi** — ISP rumahan/perumahan kecil, single admin, integrasi FreeRADIUS via REST + Mikrotik.

> Dokumentasi teknis lengkap (keputusan desain, spec detail per fitur, catatan bug yang pernah ditemukan & diperbaiki) ada di [`HANDOFF.md`](./HANDOFF.md) — baca itu kalau mau lanjut development atau audit ulang.

## Fitur

- **Admin panel** (Filament, `/admin`) — CRUD Customer, Plan (dengan Bandwidth), Router (multi-router), Voucher.
- **Customer portal** (`/customer`) — login self-service (guard terpisah dari admin), lihat status & riwayat order/transaksi.
- **Integrasi Mikrotik** — provisioning hotspot user otomatis lewat `HotspotDeviceInterface` (dukung device `MikrotikHotspot` dan `RadiusRest` per-plan).
- **RADIUS REST API** (`POST /api/radius`) — endpoint yang dipanggil FreeRADIUS untuk `authenticate`/`authorize`/`accounting`, termasuk aktivasi voucher otomatis saat customer login di captive portal Mikrotik.
- **Order & pembayaran QR statis** — admin buat order manual, sistem kirim invoice + link QR (gambar statis, diatur di Settings) via WhatsApp, admin approve manual setelah cek pembayaran masuk. Invoice bisa dibuka publik lewat link token tanpa login.
- **Notifikasi WhatsApp (via [GOWA](https://github.com/aldinokemal/go-whatsapp-web-multidevice), self-hosted) + Telegram** — otomatis terkirim saat order dibuat & saat plan berhasil di-recharge, semua percobaan kirim tercatat di log (sukses/gagal).
- **Voucher** — generate massal + halaman print, aktivasi otomatis lewat RADIUS `authorize` (bukan lewat portal customer).
- **Cron/scheduler** — cek plan expired harian, auto-disable di device, reminder H-1/H-3/H-7 sebelum expired.
- **Activity log** — audit trail otomatis untuk aksi admin (create/update/delete Customer/Plan/Router, login/logout, approve/cancel order).
- **Forgot password** (admin & customer, guard terpisah) — OTP 6 digit via WhatsApp.
- **Laporan income** — rekap transaksi per periode.

## Keputusan Desain Penting

- **Database: SQLite** — cukup untuk skala personal (puluhan customer), hemat resource, tidak butuh server DB terpisah.
- **Password Customer disimpan plaintext** (bukan hash) — RADIUS PAP/CHAP butuh password asli untuk verifikasi. Password admin (`tbl_users`) tetap di-hash normal.
- **Tidak ada fitur balance/saldo customer, tidak ada payment gateway pihak ketiga, tidak ada mid-cycle plan switch** — disederhanakan sesuai kebutuhan riil (lihat `HANDOFF.md` untuk daftar lengkap fitur PHPNuxBill asli yang sengaja di-skip).
- **Voucher diaktivasi lewat RADIUS**, bukan portal web — customer cukup masukkan kode voucher di halaman login Mikrotik.

## Instalasi

```bash
composer install
cp .env.example .env
php artisan key:generate

touch database/database.sqlite
php artisan migrate

php artisan storage:link   # perlu untuk serve gambar QR statis
```

Konfigurasi setelah aplikasi jalan (lewat admin panel `/admin`, bukan `.env`):

- **Settings → Payment Settings**: upload gambar QR statis untuk pembayaran.
- **Settings → Notification Settings**: token Telegram bot + target chat id, kredensial GOWA (`server_url`, `device_id`, `username`, `password`), kode negara nomor telepon.

Untuk production, daftarkan cron OS-level supaya scheduler Laravel (cek plan expired, reminder) benar-benar jalan:

```
* * * * * cd /path/ke/project && php artisan schedule:run >> /dev/null 2>&1
```

## Menjalankan Test

```bash
php artisan test --compact
```

## Yang Belum Selesai / Perlu Divalidasi Sebelum Go-Live

- **Integrasi CHAP (RADIUS) dan GOWA belum pernah dites ke environment nyata** — baru diverifikasi lewat automated test dengan HTTP/device di-fake. Wajib divalidasi ke FreeRADIUS/Mikrotik dan instance GOWA asli sebelum dipakai produksi.
- Viewer read-only untuk log (`tbl_message_logs`, `tbl_logs`) belum ada UI-nya di admin panel.
- Styling (customer login/dashboard, invoice publik, halaman print voucher) masih functional minimal, belum dipercantik.

Detail lengkap ada di [`HANDOFF.md`](./HANDOFF.md).
