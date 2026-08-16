# Integrasi FreeRADIUS REST

Dokumen ini adalah **satu-satunya panduan** untuk menghubungkan **FreeRADIUS** ke aplikasi ini
(Laravel NuxBill) melalui **FreeRADIUS REST API**. Dengan metode ini, FreeRADIUS berbicara ke
aplikasi melalui **HTTPS POST**, sehingga konfigurasi lebih sederhana dan **tidak memerlukan
IP Publik** untuk MikroTik — cukup aplikasi yang dapat dijangkau FreeRADIUS.

> **Versi aplikasi**: Laravel 13 · endpoint `/api/radius`
> **Dukungan autentikasi**: **PAP** dan **CHAP**

---

## Daftar Isi

1. [Arsitektur & Alur](#1-arsitektur--alur)
2. [Prasyarat](#2-prasyarat)
3. [Instalasi FreeRADIUS](#3-instalasi-freeradius)
4. [Konfigurasi Clients (`clients.conf`)](#4-konfigurasi-clients-clientsconf)
5. [Konfigurasi Modul REST (`mods-enabled/rest`)](#5-konfigurasi-modul-rest-mods-enabledrest)
6. [Konfigurasi Sites (`sites-enabled/default`)](#6-konfigurasi-sites-sites-enableddefault)
7. [Verifikasi Konfigurasi](#7-verifikasi-konfigurasi)
8. [Konfigurasi di Aplikasi Laravel](#8-konfigurasi-di-aplikasi-laravel)
9. [Alur Kerja Endpoint](#9-alur-kerja-endpoint)
10. [Pengujian & Debug](#10-pengujian--debug)
11. [Troubleshooting](#11-troubleshooting)
12. [Keamanan & Hardening](#12-keamanan--hardening)

---

## 1. Arsitektur & Alur

```
MikroTik (NAS) ──RADIUS/UDP──▶ FreeRADIUS ──HTTPS POST──▶ /api/radius (Laravel)
  port 1812/1813              (clients.conf)   (rlm_rest + shared secret)
```

- **MikroTik** mengirim `Access-Request` / `Accounting-Request` ke FreeRADIUS (UDP 1812/1813),
  diverifikasi lewat **shared secret** di `clients.conf`.
- **FreeRADIUS** menerjemahkannya menjadi **HTTP POST** ke endpoint `/api/radius` aplikasi,
  membawa parameter atribut RADIUS + **shared secret aplikasi** (`secret=`).
- **Aplikasi** (Laravel) memverifikasi `secret` (middleware), lalu memproses aksi dan mengembalikan
  **reply attributes** (rate-limit, kuota, kedaluwarsa) sebagai JSON.

Lingkungan FreeRADIUS menjalankan 2 lapis secret yang **berbeda**:

| Lapis | Tempat | Fungsi |
|-------|--------|--------|
| Secret **NAS ↔ FreeRADIUS** | `clients.conf` | Mengautentikasi MikroTik ke FreeRADIUS |
| Secret **FreeRADIUS ↔ Aplikasi** | `data` di `rest` + `RADIUS_API_SECRET` | Mengautentikasi FreeRADIUS ke `/api/radius` |

---

## 2. Prasyarat

- **Server Linux** (Debian/Ubuntu) untuk menjalankan FreeRADIUS.
- Aplikasi Laravel sudah berjalan dan **dapat dijangkau** dari server FreeRADIUS (HTTPS disarankan).
- MikroTik sudah dikonfigurasi menunjuk ke IP FreeRADIUS sebagai RADIUS server.
- Akses `root` (atau `sudo`) untuk menginstal paket dan mengedit konfigurasi.

---

## 3. Instalasi FreeRADIUS

```bash
apt-get update
apt-get install -y freeradius freeradius-rest
```

Paket `freeradius-rest` menyediakan modul **rlm_rest** yang dipakai untuk berbicara ke aplikasi.

Setelah instalasi, lokasi berkas utama:

| Berkas | Fungsi |
|--------|--------|
| `/etc/freeradius/3.0/clients.conf` | Daftar NAS (MikroTik) yang diizinkan |
| `/etc/freeradius/3.0/mods-available/rest` | Definisi modul rest (template) |
| `/etc/freeradius/3.0/mods-enabled/rest` | Modul rest yang **aktif** (salinan dari mods-available) |
| `/etc/freeradius/3.0/sites-enabled/default` | Alur `authorize` / `authenticate` / `accounting` |

Aktifkan modul rest jika belum:

```bash
ln -sf /etc/freeradius/3.0/mods-available/rest /etc/freeradius/3.0/mods-enabled/rest
```

---

## 4. Konfigurasi Clients (`clients.conf`)

Tambahkan setiap MikroTik (atau jaringan NAS) sebagai client. **Setiap kali menambah/mengedit
client, restart FreeRADIUS.**

```
# /etc/freeradius/3.0/clients.conf
client myRouterA {
    ipaddr   = 10.0.1.0/24
    secret   = VERY_LONG_RANDOM_SECRET_A
    shortname = router-a
}

client localhost {
    ipaddr   = 127.0.0.1
    secret   = testing123
}
```

> **⚠️ `secret` client adalah pre-shared key** — wajib kuat, panjang, dan unik per NAS.
> Jangan pakai contoh `verysecret`. Secret ini hanya mengamankan MikroTik → FreeRADIUS,
> **berbeda** dengan secret aplikasi pada langkah berikutnya.

---

## 5. Konfigurasi Modul REST (`mods-enabled/rest`)

Ini adalah **inti** integrasi. Isi/konfigurasikan `/etc/freeradius/3.0/mods-enabled/rest`:

> Ganti:
> - `https://your-app-domain/api/radius` dengan URL **endpoint radius aplikasi**.
> - `YOUR_RADIUS_API_SECRET` dengan nilai `RADIUS_API_SECRET` di `.env` aplikasi (lihat langkah 8).
> - `ca_file` bila Anda memakai CA custom (lihat bagian Hardening).

```
# /etc/freeradius/3.0/mods-enabled/rest
rest {
    tls {
        # Verifikasi sertifikat HTTPS aplikasi (wajib di produksi)
        ca_file      = /etc/ssl/certs/ca-certificates.crt
        check_cert   = yes
        check_cert_cn = no
    }

    connect_uri = "https://your-app-domain/api/radius"

    # ---- AUTHENTICATE: verifikasi kredensial login ----
    authenticate {
        uri    = "${..connect_uri}?action=authenticate"
        method = 'post'
        body   = 'post'
        data   = "username=%{urlquote:%{User-Name}}&password=%{urlquote:%{User-Password}}&nasid=%{urlquote:%{NAS-Identifier}}&CHAPchallenge=%{urlquote:%{CHAP-Challenge}}&CHAPassword=%{urlquote:%{CHAP-Password}}&realm=%{urlquote:%{Mikrotik-Realm}}&macAddr=%{urlquote:%{Calling-Station-Id}}&nasip=%{urlquote:%{NAS-IP-Address}}&secret=YOUR_RADIUS_API_SECRET"
        tls    = ${..tls}
    }

    # ---- AUTHORIZE: tentukan atribut balasan (rate-limit, kuota, kedaluwarsa) ----
    authorize {
        uri    = "${..connect_uri}?action=authorize"
        method = 'post'
        body   = 'post'
        data   = "username=%{urlquote:%{User-Name}}&password=%{urlquote:%{User-Password}}&nasid=%{urlquote:%{NAS-Identifier}}&CHAPchallenge=%{urlquote:%{CHAP-Challenge}}&CHAPassword=%{urlquote:%{CHAP-Password}}&realm=%{urlquote:%{Mikrotik-Realm}}&macAddr=%{urlquote:%{Calling-Station-Id}}&nasip=%{urlquote:%{NAS-IP-Address}}&secret=YOUR_RADIUS_API_SECRET"
        tls    = ${..tls}
    }

    # ---- ACCOUNTING: catat pemakaian kuota/waktu ----
    accounting {
        uri    = "${..connect_uri}?action=accounting"
        method = 'post'
        body   = 'post'
        data   = "username=%{urlquote:%{User-Name}}&nasIpAddress=%{urlquote:%{NAS-IP-Address}}&realm=%{urlquote:%{Mikrotik-Realm}}&nasid=%{urlquote:%{NAS-Identifier}}&acctSessionId=%{urlquote:%{Acct-Session-Id}}&macAddr=%{urlquote:%{Calling-Station-Id}}&acctSessionTime=%{urlquote:%{Acct-Session-Time}}&acctInputOctets=%{urlquote:%{Acct-Input-Octets}}&acctOutputOctets=%{urlquote:%{Acct-Output-Octets}}&acctInputGigawords=%{urlquote:%{Acct-Input-Gigawords}}&acctOutputGigawords=%{urlquote:%{Acct-Output-Gigawords}}&acctInputPackets=%{urlquote:%{Acct-Input-Packets}}&acctOutputPackets=%{urlquote:%{Acct-Output-Packets}}&nasPortId=%{urlquote:%{NAS-Port-Id}}&framedIPAddress=%{urlquote:%{Framed-IP-Address}}&sessionTimeout=%{urlquote:%{Session-Timeout}}&framedIPNetmask=%{urlquote:%{Framed-IP-Netmask}}&acctStatusType=%{urlquote:%{Acct-Status-Type}}&nasPortType=%{urlquote:%{NAS-Port-Type}}&secret=YOUR_RADIUS_API_SECRET"
        tls    = ${..tls}
    }

    pool {
        start = ${thread[pool].start_servers}
        min   = ${thread[pool].min_spare_servers}
        max   = ${thread[pool].max_servers}
        spare = ${thread[pool].max_spare_servers}
        uses  = 0
        retry_delay = 30
        lifetime    = 0
        idle_timeout = 60
    }
}
```

> **Catatan action yang didukung**: aplikasi hanya memproses `authenticate`, `authorize`, dan
> `accounting`. **Jangan** menambahkan section `post-auth` — aplikasi akan menolaknya
> (`Invalid Command : post-auth`). Gunakan blok `post-auth` di `sites-enabled/default`
> (langkah 6) untuk menerjemahkan atribut balasan, bukan lewat modul rest.

### Mengapa `secret` dikirim di body?

Aplikasi memverifikasi secret melalui **header `X-Radius-Secret`** **atau** **field `secret` di body**
(lihat `app/Http/Middleware/EnsureRadiusRequest.php`). Karena `rlm_rest` tidak menyediakan cara
mudah mengirim header statis, cara yang disarankan adalah **menambahkan `secret=` pada parameter
`data`** (yang dikirim sebagai body POST). Dengan HTTPS, nilai ini aman di jaringan.

---

## 6. Konfigurasi Sites (`sites-enabled/default`)

Konfigurasikan `/etc/freeradius/3.0/sites-enabled/default` dengan menyisipkan blok `rest`.
**Jangan hapus bagian lain** — hanya tambahkan/ubah bagian berikut.

```
# /etc/freeradius/3.0/sites-enabled/default

authorize {
    # ... (bagian lain dibiarkan)
    rest
    if (ok) {
        update control {
            Auth-Type := rest
        }
    }
}

authenticate {
    Auth-Type rest {
        rest
        if (updated) {
            ok
        }
    }
}

accounting {
    detail
    rest
}

session {
    radutmp
}

post-auth {
    if (reply:Group-Name) {
        update control {
            &Group := "%{reply:Group-Name}"
        }
    }
    if (reply:Mikrotik-Rate-Limit) {
        update reply {
            Mikrotik-Rate-Limit := "%{reply:Mikrotik-Rate-Limit}"
        }
    }
    if (reply:Expiration) {
        update reply {
            Expiration := "%{reply:Expiration}"
        }
    }
    update {
        &reply: += &session-state:
    }
}
```

Bagian `post-auth` di sini **menerjemahkan atribut balasan** yang dikirim aplikasi
(`reply:Mikrotik-Rate-Limit`, `reply:Expiration`, `Group-Name`) ke atribut yang dipahami MikroTik.

---

## 7. Verifikasi Konfigurasi

Sebelum menjalankan layanan, uji konfigurasi:

```bash
freeradius -XC
```

Jika output berakhir dengan `Configuration appears to be OK`, konfigurasi valid. Perbaiki error
yang muncul sebelum melanjutkan.

---

## 8. Konfigurasi di Aplikasi Laravel

Di sisi aplikasi, setel **shared secret** pada file `.env`:

```env
# .env
RADIUS_API_SECRET=YOUR_RADIUS_API_SECRET
```

Nilai ini **harus sama persis** dengan `YOUR_RADIUS_API_SECRET` pada `mods-enabled/rest`
(langkah 5). Gunakan nilai acak panjang, misalnya hasil:

```bash
openssl rand -hex 32
```

> **⚠️ Fail-closed**: bila `RADIUS_API_SECRET` kosong, endpoint `/api/radius` **menolak semua
> request** (HTTP 401) — ini disengaja. Radius tidak akan berfungsi sampai secret di-set.

### Mengubah secret aplikasi

1. Ubah `RADIUS_API_SECRET` di `.env`.
2. Ubah `YOUR_RADIUS_API_SECRET` di `mods-enabled/rest` (semua section).
3. `php artisan config:clear` (atau `php artisan config:cache`).
4. Restart FreeRADIUS: `systemctl restart freeradius`.

---

## 9. Alur Kerja Endpoint

Aplikasi menerima `POST /api/radius?action=<aksi>` (dilindungi middleware secret + rate-limit
60/menit/IP). Aksi yang didukung:

| Aksi | Dipanggil saat | Apa yang dilakukan |
|------|----------------|--------------------|
| `authenticate` | MikroTik meminta verifikasi login | Cek kredensial customer (PAP/CHAP) atau keberadaan kode voucher |
| `authorize` | Sesudah auth, untuk atribut balasan | Bangun reply attributes: rate-limit, kuota, kedaluwarsa; aktivasi voucher sekali pakai |
| `accounting` | Sesi mulai/berhenti/update | Catat & akumulasi pemakaian octets/waktu di tabel `rad_acct` |

**Jawaban yang dikembalikan aplikasi:**

- **2xx** → sukses; untuk `authorize` berisi atribut balasan seperti
  `reply:Mikrotik-Rate-Limit`, `reply:expiration`, `Simultaneous-Use`, dst.
- **401** → ditolak (kredensial salah, voucher habis/terpakai, masa aktif habis, melebihi kuota,
  sesi ganda, atau **secret salah**).
- **5xx** → kegagalan internal.

---

## 10. Pengujian & Debug

### Uji endpoint aplikasi secara langsung (curl)

```bash
# authorize untuk username customer
curl -s -X POST "https://your-app-domain/api/radius?action=authorize" \
  -d "username=TESTUSER&password=secret123&secret=YOUR_RADIUS_API_SECRET"
```

### Debug FreeRADIUS

```bash
systemctl stop freeradius.service
freeradius -X
```

### Aktifkan log radius di MikroTik

```
/system logging add topics=radius,debug action=memory
```

Lalu coba login. Perhatikan baris `Login OK` / `Reject` di output FreeRADIUS dan log MikroTik.

---

## 11. Troubleshooting

| Gejala | Penyebab & Solusi |
|--------|-------------------|
| `HTTP 401 Invalid Radius API secret` | `secret=` di `mods-enabled/rest` tidak sama dengan `RADIUS_API_SECRET` di `.env`. Samakan lalu restart. |
| `HTTP 401` pada semua request | `RADIUS_API_SECRET` kosong (fail-closed). Set nilai di `.env`. |
| Login selalu ditolak (PAP) | Cek username/password customer (`status` harus `Active`). |
| Login gagal untuk voucher | Voucher sudah terpakai (`status` ≠ 0), atau kode salah. |
| `Failed to sync customer to device` di log aplikasi | Non-fatal; aplikasi tetap mencatat recharge. Periksa layanan RADIUS. |
| Accounting tidak tercatat | Pastikan `nasid`/`macAddr` konsisten; kolom wajib `acctSessionId`, `realm`, `nasipaddress`, `framedipaddress`. |
| `CURLE_SSL_ISSUER_ERROR` | `check_cert = yes` tetapi `ca_file` menunjuk root CA sedangkan sertifikat diterbitkan CA intermediate. Arahkan `ca_file` ke **CA penerbit (intermediate)**. |
| `bad-replies` naik di `/radius monitor` MikroTik | Shared secret `clients.conf` tidak sama di kedua sisi. |
| `timeouts` naik | FreeRADIUS/aplikasi tidak dapat dijangkau; periksa firewall/port. |

---

## 12. Keamanan & Hardening

- **Verifikasi sertifikat HTTPS aplikasi**: gunakan `check_cert = yes` + `ca_file`. Di produksi
  jangan `check_cert = no` (mencegah man-in-the-middle antara FreeRADIUS dan aplikasi).
- **Secret kuat**: `RADIUS_API_SECRET` dan setiap `secret` di `clients.conf` harus panjang & acak.
- **Firewall**: hanya izinkan IP server FreeRADIUS yang dapat mencapai `/api/radius` aplikasi
  (mis. aturan UFW/security group). Batasi port RADIUS 1812/1813 hanya dari IP MikroTik.
- **`APP_DEBUG=false`** di produksi agar error tidak membocorkan stack trace.
- **Hanya aktifkan protocol yang dibutuhkan.** CHAP rentan *offline dictionary attack* dan
  memaksa password plaintext; pertimbangkan hanya memakai **PAP** bila klien mendukung.
- **Rate limiting** sudah aktif bawaan pada `/api/radius` (60 permintaan/menit/IP) untuk
  meredam brute-force.

---

*Dokumentasi ini sinkron dengan implementasi `app/Http/Controllers/RadiusAuthController.php`,
`app/Http/Middleware/EnsureRadiusRequest.php`, dan `config/radius.php`. Diperbarui dengan
mengedit file `resources/markdown/freeradius.md` — tanpa perlu mengubah kode aplikasi.*
