# GOWA WhatsApp Gateway

**GOWA** adalah **WhatsApp API Gateway** *Multi-Device* yang di-host sendiri (*self-hosted*).
PHPNuxBill memanfaatkan GOWA untuk mengirim pesan **WhatsApp** ke customer secara otomatis
(misalnya invoice, QR pembayaran, dan kode OTP).

> Referensi API lengkap (spesifikasi OpenAPI) tersedia di `docs/gowa-wa-gateway.md`.

---

## 1. Cara Kerja

- GOWA berjalan sebagai **server terpisah** yang menampung sesi WhatsApp Anda.
- PHPNuxBill mengirim pesan ke GOWA melalui **HTTP REST** dengan **Basic Auth** dan
  header **`X-Device-Id`** untuk memilih perangkat.
- GOWA lalu meneruskan pesan ke nomor tujuan melalui akun WhatsApp yang sudah terhubung.

Endpoint yang dipakai aplikasi:

```
POST {server_url}/send/message
```

Dengan payload:

```
{ "phone": "<kode-negara><nomor>@s.whatsapp.net", "message": "<isi pesan>" }
```

---

## 2. Persiapan Server GOWA

1. **Jalankan server GOWA** di mesin yang selalu menyala (VPS/PC server).
2. **Login/scan QR** dari panel GOWA untuk menghubungkan akun WhatsApp yang akan dipakai
   mengirim pesan (pastikan mencatat **Device ID** dari perangkat tersebut).
3. Atur **Basic Auth** (username + password) di server GOWA untuk keamanan akses.
4. Pastikan server dapat diakses dari PHPNuxBill (mis. `http://192.168.1.10:3000`).
   Gunakan **HTTPS** saat berproduksi, karena kredensial tampak pada koneksi.

---

## 3. Konfigurasi di PHPNuxBill

Buka **Pengaturan → Notification Settings** dan isi bagian GOWA:

| Kolom | Penjelasan |
|-------|------------|
| **GOWA Server URL** | Alamat server GOWA, mis. `http://192.168.1.10:3000` |
| **GOWA Device ID** | ID perangkat WhatsApp yang dipakai (dari panel GOWA) |
| **GOWA Basic Auth Username** | Username Basic Auth server GOWA |
| **GOWA Basic Auth Password** | Password Basic Auth server GOWA |

> **Kode Negara Telepon** (default `62` untuk Indonesia) dipakai untuk mengganti awalan
> `0` pada nomor pelanggan sebelum dikirim, mis. `0812...` → `62812...`.

Klik **Simpan** setelah selesai.

---

## 4. Pengujian

Setelah terkonfigurasi, lakukan aksi yang memicu pengiriman WhatsApp (misalnya membuat order
baru sehingga invoice/QR dikirim ke customer), lalu periksa:

- Nomor tujuan menerima pesan WhatsApp.
- Status pengiriman di halaman **Logs → Message Log** (jenis `WhatsApp`, status `Success`).
- Jika status `Error`, lihat kolom **error_message** untuk informasi kegagalan.

---

## 5. Troubleshooting

- **Pesan tidak terkirim** — pastikan server GOWA aktif dan dapat dijangkau dari server PHPNuxBill.
- **Error `Unauthorized`** — periksa username/password Basic Auth.
- **Perangkat salah** — pastikan **Device ID** sesuai dengan perangkat yang sudah scan QR.
- **Nomor tidak valid** — pastikan format nomor benar dan **Kode Negara Telepon** sudah diatur.

---

*Panduan ini disarikan dari implementasi `app/Services/NotificationService.php` dan
`docs/gowa-wa-gateway.md`.*
