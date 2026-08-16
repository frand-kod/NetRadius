# PHPNuxBill — Sidebar UX Restructure

## Tujuan

Perbaiki struktur sidebar PHPNuxBill agar lebih mudah dipahami dan tidak terlihat seperti daftar seluruh fitur aplikasi.

PENTING: Ini adalah perubahan pada navigation/information architecture saja.

Jangan melakukan redesign ulang terhadap halaman yang sudah selesai.

Jangan mengubah backend, route, API, database, business logic, atau functionality yang sudah ada.

---

## Prinsip Utama

Sidebar bukan daftar seluruh fitur.

Sidebar harus membantu user menemukan tujuan utama:

```text
Dashboard
→ Operations
→ Billing
→ Network
→ System
→ Settings
```

Menu yang sudah ada tetap digunakan. Yang berubah hanya cara menu tersebut dikelompokkan dan ditampilkan.

---

## Struktur Sidebar

Gunakan struktur berikut sebagai target:

```text
PHPNuxBill

OVERVIEW
  Dashboard

OPERATIONS
  Customers
  Subscriptions
  Online Users
  Vouchers

BILLING
  Invoices
  Payments
  Income

NETWORK
  Routers
  Plans & Bandwidth

SYSTEM
  Notifications
  Activity Logs

────────────────

Settings

Administrator
```

Namun JANGAN membuat menu baru jika functionality/route-nya memang belum ada.

Jika salah satu item di atas tidak ada di project, jangan dipaksakan.

Sebaliknya, jika project memiliki menu existing yang belum tercantum di atas, tempatkan pada group yang paling logis.

---

## Critical: Existing Routes Must Remain

Semua menu yang sudah memiliki route harus tetap menuju route/page yang sama.

Contoh:

```text
Customers
→ existing Customers route

Vouchers
→ existing Vouchers route

Routers
→ existing Routers route

Payments
→ existing Payments route
```

Jangan:

* rename route
* menghapus route
* mengubah URL
* mengubah controller
* mengubah API
* memindahkan functionality antar halaman

Perubahan hanya pada sidebar/navigation layer.

---

## Grouping

Gunakan section/group label yang sederhana:

```text
OVERVIEW
OPERATIONS
BILLING
NETWORK
SYSTEM
```

Jangan membuat terlalu banyak group.

Target sidebar harus tetap compact.

---

## Collapsible Groups

Group seperti:

```text
OPERATIONS
BILLING
NETWORK
SYSTEM
```

boleh dibuat collapsible.

Behavior:

* group aktif otomatis terbuka
* group lain dapat ditutup
* state tidak boleh mengganggu navigation
* jangan membuat submenu lebih dari 2 level

Contoh ketika user berada di Customers:

```text
OPERATIONS              ˅
  Customers             active
  Subscriptions
  Online Users
  Vouchers

BILLING                 >
NETWORK                 >
SYSTEM                  >
```

Dashboard tidak perlu berada dalam collapsible group.

---

## Settings

Settings sebaiknya berada di bagian bawah sidebar dan tidak bercampur dengan operational menu.

Contoh:

```text
────────────────────

⚙ Settings
```

Jika aplikasi memiliki beberapa settings page, Settings dapat membuka submenu:

```text
Settings
  General
  Billing
  Payment
  Network
  Notifications
  System
```

Gunakan hanya settings yang memang sudah tersedia.

Jangan membuat halaman settings baru.

---

## Active State

Menu aktif harus tetap mengikuti route/page yang sedang dibuka.

Contoh:

```text
OPERATIONS
  Customers          ← active
```

Jika submenu sedang aktif, parent group otomatis terbuka.

Active state harus lebih menonjol daripada menu biasa tetapi tetap subtle.

Hindari active state yang terlalu besar atau terlalu terang.

---

## Sidebar Density

Tujuannya mengurangi kesan:

```text
20+ menu items
↓
sidebar penuh
↓
user harus mencari
```

menjadi:

```text
5–6 logical groups
↓
user mengenali kategori
↓
menu lebih mudah dipindai
```

Jangan memperbesar sidebar hanya untuk menampung struktur baru.

---

## Desktop

Pertahankan behavior sidebar yang sudah dibuat pada redesign sebelumnya:

* expanded
* collapsed
* active state
* responsive

Perubahan ini hanya merapikan isi dan grouping.

Jangan membuat ulang sidebar component jika component existing sudah bekerja dengan baik.

---

## Mobile

Pertahankan drawer/sidebar mobile yang sudah ada.

Gunakan grouping yang sama dengan desktop.

Jangan membuat navigation system kedua khusus mobile jika tidak diperlukan.

---

## Visual Consistency

Pertahankan:

* typography
* color system
* Light/Dark/System mode
* icon library
* spacing
* border
* radius
* hover state
* active state

yang sudah diterapkan dari:

```text
UI_UX_REDESIGN.md
UI_UX_THEME.md
```

File ini hanya menambahkan aturan information architecture.

---

## Do Not Touch Existing Pages

CRITICAL.

Setelah pekerjaan UI sebelumnya hampir selesai, jangan melakukan redesign ulang terhadap:

* Dashboard
* Customers
* Vouchers
* Payments
* Routers
* Reports
* Forms
* Tables
* Modals
* Charts
* Settings pages

kecuali ada perubahan kecil yang benar-benar diperlukan agar sidebar baru bekerja.

Halaman yang sudah selesai harus tetap seperti sekarang.

---

## Definition of Done

* [ ] Sidebar lebih mudah dipindai
* [ ] Menu dikelompokkan berdasarkan workflow
* [ ] Operational menu terpisah dari Settings
* [ ] Existing routes tetap sama
* [ ] Existing pages tetap sama
* [ ] Active route tetap benar
* [ ] Active group otomatis terbuka
* [ ] Collapsible group bekerja
* [ ] Desktop tetap bekerja
* [ ] Mobile tetap bekerja
* [ ] Light mode tetap bekerja
* [ ] Dark mode tetap bekerja
* [ ] Tidak ada functionality yang hilang
* [ ] Tidak ada halaman yang di-redesign ulang

## Final Rule

Anggap file ini sebagai **navigation refactor**, bukan UI redesign baru.

Target:

```text
Existing UI
+
Better information architecture
=
More UX-friendly PHPNuxBill
```

Bukan:

```text
Existing UI
→ redesign everything again
```
