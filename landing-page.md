# NuxBill: Billing Hotspot Mikrotik

## Tentang NuxBill
NuxBill adalah solusi billing hotspot Mikrotik yang ditulis ulang ke Laravel 13, dirancang khusus untuk penggunaan pribadi (ISP rumahan/perumahan kecil, single admin).

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
