[![License](https://img.shields.io/github/license/frand-kod/NetRadius)](https://github.com/frand-kod/NetRadius/blob/master/LICENSE) [![PHP](https://img.shields.io/badge/PHP-%5E8.3-%23777BB4)](https://www.php.net/) [![Laravel](https://img.shields.io/badge/Laravel-13.x-red)](https://laravel.com/) [![Stars](https://img.shields.io/github/stars/frand-kod/NetRadius?style=social)](https://github.com/frand-kod/NetRadius/stargazers)

# Fitur NetRadius

## Tentang NetRadius
NetRadius adalah solusi billing hotspot Mikrotik, dirancang khusus untuk penggunaan pribadi

## Target Audiens
- Pemilik ISP rumahan atau perumahan kecil.
- Single admin yang membutuhkan manajemen Mikrotik sederhana dan efisien.

## Use Case
- Mengelola hotspot Mikrotik secara otomatis.
- Mengintegrasikan FreeRADIUS via REST API.
- Manajemen voucher hotspot.
- Pencatatan log aktivitas dan notifikasi transaksi melalui WhatsApp & Telegram.

## Fitur Utama
- **Admin Panel:** CRUD lengkap untuk Customer, Plan (Bandwidth), Router, dan Voucher.
- **Self-Service Customer:** Portal login untuk melihat status dan riwayat order.
- **Integrasi Mikrotik & RADIUS:** Provisioning otomatis, autentikasi berbasis voucher.
- **Otomatisasi:** Cron job untuk expired plan, reminder notifikasi (WhatsApp/Telegram).

## Fitur yang Tidak Ada (Sengaja Dihapus)
- Tidak ada fitur balance/saldo customer.
- Tidak ada payment gateway pihak ketiga (menggunakan QR statis & approve manual).
- Tidak ada fitur mid-cycle plan switch.
