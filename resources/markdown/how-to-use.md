# Panduan Penggunaan Aplikasi

Selamat datang di **NetRadius** — aplikasi billing hotspot untuk Mikrotik.
Panduan ini menjelaskan cara menggunakan setiap bagian aplikasi, baik sebagai **Admin** maupun sebagai **Customer**.

---

## 1. Login

### Admin
- Buka halaman `/admin/login`.
- Masukkan **Username** dan **Password**.
- Setelah berhasil, Anda masuk ke **Dashboard** admin.

> **Kredensial default pengujian:** `admin` / `admin123`. Segera ganti password setelah instalasi.

### Customer
- Customer login di halaman `/customer/login` menggunakan **Username** dan **Password** yang dibuat oleh admin.
- Setelah login, customer masuk ke halaman **Dashboard Customer** untuk melihat riwayat order & transaksi.

### Lupa Password
- Baik admin maupun customer dapat memakai tautan **"Lupa password?"** di halaman login.
- Sistem akan mengirim **kode OTP** melalui WhatsApp untuk memverifikasi, lalu Anda bisa mengatur password baru.

---

## 2. Dashboard Admin

Dashboard menampilkan ringkasan kondisi usaha:

- **KPI Cards** — Total Customer, Customer Aktif, User Online, Voucher (tersedia/terpakai), dan Total Income.
- **Chart** — Customer baru per bulan, status customer, pendapatan 30 hari terakhir, dan penggunaan voucher.
- **Tabel User Online** — daftar sesi RADIUS yang sedang aktif (username, IP, durasi, MAC, waktu mulai).

> Data pada dashboard bersifat **real-time** dari sistem RADIUS/Mikrotik Anda.

---

## 3. Kelola Data Master

### 3.1 Router
Router adalah perangkat Mikrotik yang melayani hotspot.
- **Tambah Router** → isi nama, IP address, dan status.
- Gunakan untuk menghubungkan plan/voucher ke perangkat yang tepat.

### 3.2 Bandwidth
- Kelola definisi **rate down/up** dan burst untuk paket internet.
- Bandwidth dibuat terlebih dahulu, lalu dipakai oleh **Plan**.

### 3.3 Plan
Paket layanan yang dijual (menggabungkan bandwidth + harga + masa aktif).
- **Tambah Plan** → pilih bandwidth, tentukan harga, masa aktif (validity), type, device, dan router.
- Aktifkan/Nonaktifkan plan dengan tombol **Enabled**.

### 3.4 Customer
- **Tambah Customer** → buat username, password (plaintext, dipakai customer untuk login), data diri, balance, dan status.
- Status yang tersedia: `Active`, `Banned`, `Disabled`, `Inactive`, `Limited`, `Suspended`.
- Gunakan kolom **search** untuk mencari username, nama, email, atau telepon.

---

## 4. Order

Alur penjualan paket:

1. **Buat Order** → pilih *Customer* dan *Plan*. Order dibuat dengan status **pending**.
2. Sistem mengirim **Invoice** + **QR pembayaran** ke WhatsApp customer.
3. Setelah customer membayar, admin **Approve** order → customer otomatis di-*recharge* (aktif).
4. Jika batal, admin bisa **Cancel** order.

- Di halaman **Orders**, gunakan filter status (`Semua Status` / `Pending` / `Paid` / `Cancelled`) dan kolom pencarian.
- Tautan **Invoice** membuka halaman invoice publik untuk order tersebut.

---

## 5. Voucher

Voucher adalah kode prepaid yang bisa dijual/dibagikan kepada customer.

- **Tambah Voucher** → buat satu voucher manual.
- **Generate Vouchers** → buat banyak voucher sekaligus: pilih *Plan*, tentukan *Jumlah* (1–500) dan *Panjang Kode*.
- **Print Selected** → centang beberapa voucher lalu cetak untuk dibagikan.
- Filter berdasarkan status `Unused` / `Used`.

---

## 6. Laporan Pendapatan

- Pilih rentang **Dari Tanggal** dan **Sampai Tanggal**.
- Klik **Tampilkan** untuk melihat ringkasan pendapatan per hari: jumlah transaksi dan total.
- Bagian **TOTAL** di bawah tabel menampilkan akumulasi seluruh periode.

---

## 7. Log

Halaman ini menampilkan aktivitas sistem:

- **Activity Log** — jejak aksi yang dilakukan user (login, CRUD, approve, dll) beserta IP.
- **Message Log** — riwayat pesan terkirim (WhatsApp/Telegram), penerima, dan status kirim.

Gunakan kolom **Cari** dan filter **Jenis** untuk menyaring data.

---

## 8. Pengaturan

### 8.1 General Settings
- **Nama perusahaan**, alamat, telepon, email — dipakai untuk identitas & invoice.
- **Simbol & kode mata uang** (default: `Rp` / `IDR`).

### 8.2 Payment Settings
- **Upload QR pembayaran** yang akan tampil di invoice customer.
- QR ini dipakai customer untuk transfer/bayar, lalu admin mengonfirmasi manual.

### 8.3 Notification Settings
- **Telegram** — Bot Token & Chat ID admin untuk notifikasi.
- **GOWA (WhatsApp)** — Server URL, Device ID, username, dan password Basic Auth.
- **Kode Negara Telepon** — dipakai mengganti awalan `0` pada nomor pelanggan (default `62`).

---

## 9. Portal Customer & Halaman Publik

### Dashboard Customer
- Melihat **Riwayat Order** (status pembayaran) dan **Riwayat Transaksi** (paket yang sudah aktif).
- Membuka **Invoice** untuk setiap order.

### Halaman Invoice Publik
- Dapat diakses tanpa login melalui tautan `invoice/{token}`.
- Menampilkan detail pesanan, **QR pembayaran**, dan status.
- Saat order sudah **paid**, menampilkan konfirmasi pembayaran.

---

## 10. Tips Penggunaan

- **Cari dengan cepat** — hampir semua halaman tabel punya kolom pencarian.
- **Approve dengan hati-hati** — meng-approve order akan langsung mengaktifkan (recharge) customer.
- **Voucher yang di-generate** bisa langsung dicetak dan dijual.
- Untuk masalah teknis, periksa **Log** untuk melihat riwayat aktivitas dan status pengiriman pesan.

---

*Dokumentasi ini diperbarui dengan mengedit file `resources/markdown/how-to-use.md` — tanpa perlu mengubah kode aplikasi.*
