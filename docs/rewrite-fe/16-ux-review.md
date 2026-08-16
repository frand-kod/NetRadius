# 16-ux-review.md — Audit UX & Aturan Perbaikan (tanpa menulis kode)

Dokumen ini berisi hasil audit pengalaman pengguna (UX) untuk frontend `rewrite-fe`.
Fokus utama: **menghilangkan redundansi** dan **menyeragamkan pola interaksi**.
Dokumen ini **hanya berisi aturan perbaikan** — tidak ada kode.

Sumber audit: `resources/js/Layouts`, `resources/js/Pages/**`, `resources/js/Components/**`,
`app/Http/Controllers/Admin/*SettingsController.php`, `routes/web.php`, dan kamus desain `12-styling-system.md`.

---

## Ringkasan Eksekutif

Frontend secara visual sudah "rapi" (kartu rounded, aksen amber, dark mode), namun secara
**pengalaman ada tiga masalah besar**:

1. **Ketidakkonsistenan antar halaman** — sebagian halaman sudah memakai sistem desain (R1–R20),
   sebagian besar masih memakai kelas "lama". Pengguna yang berpindah halaman merasakan UI yang berbeda-beda.
2. **Redundansi data di Dashboard** — metrik yang sama ditampilkan dua kali (KPI card + donut/line chart).
3. **Kompetisi aksi utama (CTA)** — beberapa halaman menonjolkan banyak tombol warna-warni sekaligus,
   sehingga tidak ada satu arah yang jelas untuk pengguna.

Prioritas pengerjaan di akhir dokumen (§6).

---

## Temuan & Aturan Perbaikan per Kategori

### 1. Inisialisasi / Data Redundansi

#### 1.1 Dashboard menampilkan data yang sama dua kali — TINGGI
- `Dashboard.vue` menampilkan: KPI "Customer Aktif" **dan** donut "Status Customer";
  KPI "Voucher Belum/Terpakai" **dan** donut "Penggunaan Voucher";
  KPI "Total Income" **dan** line "Pendapatan 30 Hari".
- Donut dan KPI menceritakan hal yang sama tanpa menambah konteks baru.

**RULES:**
- Setiap metrik hanya ditampilkan **sekali** di layar pertama dashboard, kecuali penyajian
  keduanya memberi makna berbeda (mis. KPI = nilai saat ini, chart = tren/perbandingan).
- Jika ingin mempertahankan chart, **hapus** KPI yang identik nilainya, atau ganti donut
  dengan data yang belum ada di KPI (mis. donut "Status" hanya jika KPI tidak menampilkannya).
- Batasi KPI maksimal 4 kartu paling penting. "Voucher Belum Dipakai" + "Voucher Terpakai"
  sebaiknya digabung jadi satu kartu "Voucher Tersedia/Total".

#### 1.2 Portal Customer menampilkan Riwayat Order & Transaksi yang mirip — SEDANG
- `Customer/Dashboard.vue` menampilkan "Riwayat Order" dan "Riwayat Transaksi" sebagai dua blok
  terpisah dengan isi yang sering identik (nama paket + harga). Pengguna sulit membedakan keduanya.

**RULES:**
- Jika kedua data memang berbeda makna (order = pesanan/belum bayar; transaksi = sudah recharge),
  beri **judul + deskripsi singkat** yang menjelaskan bedanya, dan tandai status dengan badge.
- Jika isinya tumpang tindih, **gabung menjadi satu daftar** dengan indikator status.

---

### 2. Kompetisi Aksi Utama (CTA) & Hierarki Tombol

#### 2.1 Halaman Voucher punya 3 tombol primer warna-warni — TINGGI
- `Voucher/Index.vue` menampilkan berdampingan: **amber** "Tambah Voucher",
  **hijau** "Generate Vouchers", **biru** "Print Selected".
- Tiga warna primer sekaligus = tidak ada arah utama; biru & hijau **melanggar palet** sistem desain
  (hanya amber=primer, merah=bahaya).

**RULES:**
- **Satu aksi primer (amber) per layar.** Sisanya jadikan tombol sekunder (outline) atau ghost.
- Warna di luar `amber`/`merah` tidak boleh dipakai untuk tombol primer (konsisten dengan R2–R4).
- Aksi yang jarang/berbahaya jangan diletakkan sejajar dengan aksi utama; gunakan menu "…" atau
  kelompokkan aksi sekunder.

#### 2.2 Tombol Delete tidak seragam: `button` vs `Link method="delete"` — SEDANG
- `Customer/Plan/Voucher` pakai `button @click + confirm()`;
- `Router/Bandwidth` pakai `<Link method="delete">`.

**RULES:**
- Pakai **satu pola** untuk hapus di semua halaman. Buat satu komponen aksi hapus yang reusable.
- Setiap aksi destruktif harus lewat **konfirmasi modal** (bukan `confirm()` browser) — lihat §5.

---

### 3. Konsistensi Status & Badge

#### 3.1 Label status tidak seragam — TINGGI
- Order: `pending`/`paid`/`cancelled` (**huruf kecil**, tampil apa adanya).
- Customer: `Active`/`Banned` (**TitleCase**).
- Voucher: `Unused`/`Used` (TitleCase), tapi filter memakai nilai `0`/`1`.

**RULES:**
- Seragamkan **tampilan label** status ke TitleCase (`Pending`, `Paid`, `Cancelled`, dst.)
  dengan satu fungsi/komponen pemetaan label + warna (satu sumber kebenaran, mis. `useStatus`).
- Nilai `0`/`1` untuk filter **jangan** ditampilkan mentah; tampilkan "Belum Dipakai"/"Terpakai".

#### 3.2 Warna badge menyimpang dari palet — SEDANG
- Customer/Index memakai `bg-yellow-100` (harusnya `amber`).
- Logs memakai `bg-blue-100` (biru di luar palet).
- Plan/Router menampilkan **"Yes"/"No"** sebagai badge hijau/merah — seakan-akan status berbahaya.

**RULES:**
- Hanya pakai palet badge: hijau (baik), merah (buruk/bahaya), amber (menunggu),
  abu-abu (netral) — sesuai §3 file `12`.
- `Yes/No` untuk field boolean (enabled, is_radius) sebaiknya bukan badge warna penuh;
  gunakan teks/ikon biasa atau badge netral, **bukan** hijau-merah yang bisa salah tafsir sebagai status.

#### 3.3 Badge tipe di Logs — RENDAH
- `ActivityLog.type` selalu biru; `MessageLog.type` WhatsApp=hijau, lainnya=biru.

**RULES:**
- Tetapkan skema warna tipe yang bermakna (mis. WhatsApp=hijau, Telegram=biru) dan terapkan konsisten
  di kedua tab; pastikan tidak memakai warna di luar palet yang disepakati.

---

### 4. Sistem Desain Belum Diterapkan Seragam (Task 13 belum tuntas)

Beberapa halaman masih memakai kelas "lama" sehingga tampil beda dari halaman lain yang sudah R1–R20:

| Halaman | Yang belum dikonversi |
|---|---|
| `Customer/Index.vue` | toolbar `flex gap-4`, tombol amber lama, thead lama, badge `yellow`, pagination lama |
| `Bandwidth/Index.vue` | tombol amber lama, thead `bg-gray-50` lama, td `px-4 py-2`, pagination lama |
| `IncomeReport.vue` | kartu `bg-white rounded shadow`, **tombol biru** `bg-blue-600` (masih ada!) |
| `Order/Create.vue` | form `bg-white rounded shadow`, input `rounded border px-3 py-2` |
| `Customer/Create.vue` | input `rounded border px-3 py-2` (bukan R6), label tanpa `text-gray-700` |
| `Admin/Auth/Login.vue` | `border-gray-200`, label uppercase, shadow tebal amber — beda dari halaman lain |
| `Customer/*.vue` | seluruhnya pola `bg-white rounded shadow` lama |

**RULES:**
- Selesaikan `13-styling-pages.md` untuk **semua** file tanpa kecuali (jangan "selective").
- Satu set kelas (R6 input, R7 label, R14 toolbar, R18 pagination) harus **identik** di semua halaman.
- Hapus semua `bg-blue-600` tombol (per §2c file 13) — tidak boleh ada biru untuk tombol.
- Form login admin & portal customer harus memakai pola form yang sama (GuestLayout + R2/R6/R7),
  hanya berbeda konten.

---

### 5. Komponen Reusable Tidak Dipakai / Tidak Dibuat

README (§ `Components/`) merencanakan `DataTable.vue`, `StatusBadge.vue`, `ConfirmModal.vue`,
`Pagination.vue`, `useToast.js`. Kenyataannya hanya chart + `ThemeToggler` yang ada.
Akibatnya tiap halaman **menulis ulang** tabel, pagination, badge, dan `confirm()`.

**RULES:**
- Buat & pakai **satu** `ConfirmModal` reusable untuk semua aksi destruktif (hapus, cancel, approve).
- Buat & pakai **satu** `StatusBadge` (komponen pemetaan label→warna) agar badge konsisten di semua halaman.
- Ekstrak **Pagination** menjadi satu komponen (banyak halaman mengulang 10 baris link pagination).
- Tolak menulis ulang pola tabel/pagination/badge per halaman baru — wajib pakai komponen bersama.

---

### 6. Pengaturan Umum (General Settings) Tidak Bisa Diakses — TINGGI

- Backend sudah punya route + controller `admin/settings/general` (`GeneralSettingsController`),
  dan merender `Inertia('Admin/Settings/General')`.
- Namun **tidak ada** file `Pages/Admin/Settings/General.vue`, dan **tidak ada link di sidebar**
  `AdminLayout.vue` (hanya Payment & Notification).

**RULES:**
- **Buat** halaman `General.vue` (company info + currency) mengikuti pola halaman Settings lain, lalu
  tambahkan link di sidebar — ATAU hapus route/controller jika memang tidak dibutuhkan.
- Setiap halaman yang di-render Inertia harus benar-benar bisa dijangkau dari navigasi;
  jangan biarkan route "yatim" tanpa UI dan tanpa pintu masuk.

---

### 7. Empty State & Pagination Tidak Seragam — RENDAH

- Empty row: sebagian `py-4`, sebagian `py-8`; sebagian `Tidak ada customer.`/`Belum ada plan.`
- Pagination: sebagian `mt-4`, sebagian `mt-6`.

**RULES:**
- Seragamkan empty state: `py-8 text-center text-sm text-gray-500` di semua halaman,
  dengan kalimat yang konsisten (mis. "Belum ada data." + saran tindakan).
- Seragamkan margin & gaya pagination (`mt-6` + R18).

---

### 8. Status Form & Validasi yang Bisa Menyesatkan — SEDANG

- `Customer/Create.vue` memamerkan **6 status** (`Active/Banned/Disabled/Inactive/Limited/Suspended`)
  dalam satu dropdown tanpa warna/penjelasan — mudah memilih status yang salah.
- Error validasi ada dua jalur: flash banner di layout **dan** inline `form.errors` — bisa tampil dobel.

**RULES:**
- Untuk dropdown status, beri **warna badge** di opsi atau kelompokkan status
  (Aktif/Nonaktif/Pemblokiran) agar pilihan tidak ambigu.
- Tetapkan satu jalur penampil error: gunakan **inline `form.errors`** untuk error per-field,
  dan **flash** hanya untuk pesan keseluruhan (sukses/gagal submit), jangan dobel untuk satu kasus.

---

### 9. Aksesibilitas & Keterbacaan — RENDAH

**RULES:**
- Aksi tombol teks ("Edit"/"Delete") yang hanya text tanpa ikon: tambahkan `aria-label`/`title`
  dan pastikan target klik cukup besar.
- Header kolom tabel pakai `uppercase` (R11) — pastikan tetap kontras & tidak terpotong di layar sempit.
- Semua tombol status `disabled` saat `processing` (konsisten) untuk mencegah klik ganda.
- Pada tabel lebar (Customer, Plan) dengan banyak kolom, pastikan scroll horizontal halus
  dan kolom aksi tetap terlihat / sticky di kanan.

---

## 10. Daftar Cepat Redundansi (referensi saat perbaikan)

| # | Lokasi | Masalah | Perbaikan |
|---|---|---|---|
| 1 | Dashboard KPI + charts | data sama tampil 2× | hapus salah satu / ganti data |
| 2 | Voucher toolbar | 3 CTA warna-warni | 1 primer amber + sekunder |
| 3 | Customer portal order vs transaksi | dua daftar mirip | gabung / jelaskan bedanya |
| 4 | Badge status casing & warna | tidak konsisten | satu komponen StatusBadge |
| 5 | Yes/No badge | salah tafsir sebagai status | ganti bukan badge hijau-merah |
| 6 | 6+ halaman masih kelas lama | UI tidak seragam | selesaikan task 13 |
| 7 | General Settings | route tanpa UI/link | buat halaman + link |
| 8 | confirm() native vs modal | tidak konsisten | ConfirmModal reusable |
| 9 | Delete: button vs Link | dua pola | satu komponen aksi hapus |

---

## Ringkasan Prioritas

**Lakukan dulu (dampak terbesar):**
1. Selesaikan `13-styling-pages.md` untuk semua halaman (hapus biru, seragamkan input/toolbar/pagination).
2. Buat halaman + link **General Settings** (saat ini tidak dapat diakses).
3. Kurangi redundansi data di **Dashboard** (hapus/ganti KPI yang dobel dengan chart).

**Kedua:**
4. Rapikan **Voucher** jadi satu aksi utama; seragamkan tombol hapus (satu pola + modal).
5. Seragamkan **badge status** dengan satu komponen (label + warna + casing).

**Terakhir (polish):**
6. Konsistenkan empty state, pagination, aksesibilitas, dan penampil error.

> Prinsip utama: **satu makna = satu tampilan, satu aksi utama = satu warna.**
> Jika dua elemen menampilkan hal yang sama atau menonjol sama kuatnya, itu redundansi — pilih salah satu.
