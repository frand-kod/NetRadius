# Integrasi GOWA WhatsApp Gateway

Panduan **lengkap** untuk menghubungkan **GOWA** — *WhatsApp API Gateway* *multi-device* yang
*self-hosted* — dengan aplikasi ini, sehingga aplikasi dapat mengirim **WhatsApp** ke pelanggan
secara otomatis (invoice, QR pembayaran, kode OTP, notifikasi aktivasi, dll).

> **Referensi API lengkap** (spesifikasi OpenAPI): `docs/gowa-wa-gateway.md`

---

## Daftar Isi

1. [Ikhtisar & Arsitektur](#1-ikhtisar--arsitektur)
2. [Persiapan Server GOWA](#2-persiapan-server-gowa)
3. [Hubungkan Device WhatsApp](#3-hubungkan-device-whatsapp)
4. [Konfigurasi di Aplikasi](#4-konfigurasi-di-aplikasi)
5. [Pengujian](#5-pengujian)
6. [Cara Kerja di Sisi Aplikasi](#6-cara-kerja-di-sisi-aplikasi)
7. [Troubleshooting](#7-troubleshooting)
8. [Keamanan](#8-keamanan)

---

## 1. Ikhtisar & Arsitektur

GOWA adalah server mandiri yang menampung sesi WhatsApp Anda (bukan aplikasi ini). Aplikasi
mengirim pesan ke GOWA via **HTTP REST** dengan **Basic Auth** dan header **`X-Device-Id`**,
lalu GOWA meneruskan ke nomor tujuan memakai akun WhatsApp yang sudah terhubung.

```
Laravel (NetRadius) ──HTTPS POST──▶ GOWA server ──WhatsApp──▶ Pelanggan
  NotificationService              /send/message            (akun WA terhubung)
  (Basic Auth + X-Device-Id)
```

**Endpoint yang dipakai aplikasi:**

```
POST {server_url}/send/message
```

Dengan **Basic Auth** (`alt_wga_username` / `alt_wga_password`) + header
`X-Device-Id: <alt_wga_device_id>`, dan body:

```json
{ "phone": "62812xxxxxxx@s.whatsapp.net", "message": "<isi pesan>" }
```

---

## 2. Persiapan Server GOWA

1. **Jalankan server GOWA** di mesin yang selalu menyala (VPS/PC server).
   - Ikuti petunjuk *setup* proyek GOWA (mis. jalankan binary/container, buka port aksesnya).
2. **Atur Basic Auth** (username + password) di server GOWA — dipakai mengamankan API.
3. **Catat URL server** yang dapat dijangkau aplikasi, mis. `http://192.168.1.10:3000`.
   - Gunakan **HTTPS** saat berproduksi karena kredensial tampak pada koneksi.
4. Pastikan server dapat dijangkau dari mesin aplikasi (uji dengan `curl`).

Verifikasi server hidup:

```bash
curl -s http://<server_url>/health
# → OK
```

---

## 3. Hubungkan Device WhatsApp

1. Buka panel/UI GOWA.
2. **Login/scan QR** (atau *pairing code*) untuk menghubungkan akun WhatsApp yang akan dipakai
   mengirim pesan.
3. **Catat Device ID** dari perangkat yang baru terhubung — nilai ini wajib diisi pada
   konfigurasi aplikasi (`alt_wga_device_id`).
4. (Opsional) Buat *device slot* dengan ID khusus agar lebih mudah dikenali.

> Untuk daftar lengkap endpoint manajemen device (login QR, pairing code, status, logout),
> lihat bagian `/devices` di `docs/gowa-wa-gateway.md`.

---

## 4. Konfigurasi di Aplikasi

Buka **Pengaturan → Notification Settings** dan isi bagian GOWA. Kolom pada UI dipetakan ke
kunci penyimpanan berikut (disimpan di `tbl_appconfig`):

| Kolom (UI) | Kunci config | Contoh | Keterangan |
|------------|--------------|--------|------------|
| **GOWA Server URL** | `alt_wga_server_url` | `http://192.168.1.10:3000` | Alamat server GOWA (tanpa `/send/message`). Jika kosong, WhatsApp tidak dikirim. |
| **GOWA Device ID** | `alt_wga_device_id` | `org_2` | ID device WhatsApp (dari langkah 3), dikirim sebagai header `X-Device-Id`. |
| **GOWA Basic Auth Username** | `alt_wga_username` | `admin` | Username Basic Auth server GOWA. |
| **GOWA Basic Auth Password** | `alt_wga_password` | `***` | Password Basic Auth server GOWA. |
| **Kode Negara Telepon** | `country_code_phone` | `62` | Dipakai mengganti awalan `0` pada nomor pelanggan (lihat cara kerja). |

Klik **Simpan** setelah selesai.

> **Catatan format nomor**: aplikasi mengubah `0812xxxx` → `62812xxxx` lalu mengirim sebagai
> `62812xxxx@s.whatsapp.net`. Pastikan `country_code_phone` sesuai negara Anda (default `62`).

---

## 5. Pengujian

Setelah terkonfigurasi, lakukan aksi yang memicu WhatsApp (mis. buat order baru sehingga
invoice/QR dikirim ke customer), lalu periksa:

1. **Nomor tujuan** menerima pesan WhatsApp.
2. **Logs → Message Log** mencatat entri jenis `WhatsApp`:
   - Status `Success` → terkirim.
   - Status `Error` → lihat kolom `error_message` untuk penyebab.

Bila perlu menguji API langsung:

```bash
curl -u admin:password -H "X-Device-Id: org_2" \
  -X POST "http://192.168.1.10:3000/send/message" \
  -H "Content-Type: application/json" \
  -d '{"phone":"62812xxxxxxx@s.whatsapp.net","message":"Halo, ini uji coba."}'
```

Aplikasi menganggap pengiriman **sukses** hanya bila HTTP 2xx **dan** body JSON memiliki
`code == "SUCCESS"`.

---

## 6. Cara Kerja di Sisi Aplikasi

Implementasi di `app/Services/NotificationService.php::sendWhatsapp()`:

1. Baca `alt_wga_server_url` — **jika kosong, fungsi langsung berhenti** (tidak mengirim apa pun).
2. Format nomor: buang `0` di depan, beri `country_code_phone` (default `62`), tambahkan
   `@s.whatsapp.net`.
3. Kirim `POST {server_url}/send/message` dengan Basic Auth + header `X-Device-Id`.
4. Catat hasil ke **Message Log** (`WhatsApp`, status `Success`/`Error`, beserta `error_message`).

Pengiriman dipicu oleh listener notifikasi (mis. `SendCustomerRechargedNotification`,
`SendOrderCreatedNotification`, `PasswordResetOtpService`), masing-masing mematuhi toggle
aktif/nonaktif template di **Pengaturan → Notification**.

---

## 7. Troubleshooting

| Gejala | Penyebab & Solusi |
|--------|-------------------|
| **Pesan tidak terkirim** | `alt_wga_server_url` kosong (fungsi berhenti), atau server GOWA tidak dapat dijangkau. |
| **Error `Unauthorized`** | Username/password Basic Auth salah. |
| **Device salah / tidak terhubung** | `alt_wga_device_id` tidak sesuai device yang sudah scan QR; pastikan device terhubung di panel GOWA. |
| **Nomor tidak valid** | Pastikan nomor benar dan `country_code_phone` sudah diatur; kirim sebagai `62812...@s.whatsapp.net`. |
| **Status `Error` di Message Log** | Baca kolom `error_message`; periksa respons server (mis. perangkat `disconnected`). |
| **`CURLE_*` / SSL** | Gunakan URL HTTPS yang valid; pastikan sertifikat server GOWA dipercaya aplikasi. |

---

## 8. Keamanan

- **Gunakan HTTPS** untuk URL server GOWA agar Basic Auth tidak tampak di jaringan.
- **Basic Auth wajib aktif** di server GOWA — tanpa itu, siapa pun yang bisa menjangkau GOWA
  dapat mengirim pesan atas nama akun Anda.
- Jangan bagikan `alt_wga_password` / `X-Device-Id` secara publik.
- Batasi akses jaringan: hanya mesin aplikasi yang boleh menjangkau port GOWA.

---

*Panduan ini sinkron dengan `app/Services/NotificationService.php` dan
`docs/gowa-wa-gateway.md` (speks OpenAPI). Diperbarui dengan mengedit
`resources/markdown/gowa-wa-gateway.md`.*
