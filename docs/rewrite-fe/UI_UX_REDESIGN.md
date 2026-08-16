# PHPNuxBill — UI/UX Redesign Specification

## 1. Tujuan

Redesign seluruh UI PHPNuxBill agar terlihat seperti modern network/business control plane, bukan template admin CRUD sederhana.

Target visual:

* Cloudflare Dashboard
* 9Router Dashboard
* Modern SaaS control panels
* Network management dashboards
* ISP / billing control plane

Jangan membuat clone Cloudflare atau 9Router.

Ambil prinsip desainnya saja:

* strong visual hierarchy
* compact but readable information
* professional control-plane feeling
* clear status indicators
* consistent spacing
* subtle borders
* restrained colors
* meaningful data visualization
* polished interaction states
* responsive layout

UI harus terlihat seperti aplikasi production-grade yang benar-benar digunakan operator ISP.

---

## 2. Kondisi UI Saat Ini

UI saat ini terlalu polos.

Masalah utama:

* sidebar terlalu kosong
* header terlalu sederhana
* dashboard cards hanya berupa kotak gelap
* hierarchy informasi kurang kuat
* terlalu banyak area kosong
* chart terlihat seperti komponen yang ditempel
* tidak ada contextual action
* tidak ada activity/recent event section
* tidak ada visual status yang kuat
* typography belum memiliki hierarchy yang cukup
* dashboard terasa seperti template admin generik
* tidak ada sense bahwa aplikasi ini adalah ISP management/control plane

Screenshot yang diberikan user menjadi baseline visual sebelum redesign.

Jangan sekadar mengganti warna.

Redesign struktur visual dan information hierarchy.

---

# 3. Design Direction

Gunakan konsep:

## "ISP Control Plane"

PHPNuxBill harus terasa sebagai pusat kendali bisnis ISP.

Operator harus dapat membuka dashboard dan langsung memahami:

1. Kondisi customer
2. Kondisi subscription
3. User online
4. Voucher
5. Revenue
6. Payment
7. Network/service status
8. Aktivitas terbaru
9. Hal yang membutuhkan perhatian

Dashboard bukan sekadar kumpulan statistik.

Dashboard adalah operational overview.

---

# 4. Visual Language

Gunakan visual language:

* modern
* professional
* technical
* clean
* compact
* slightly dense
* premium
* calm
* trustworthy

Hindari:

* gradient berlebihan
* glassmorphism berlebihan
* neon cyberpunk
* terlalu banyak rounded cards
* shadow berat
* icon besar tanpa fungsi
* warna-warni seperti dashboard template
* oversized typography
* animasi yang tidak berguna

Gunakan:

* subtle border
* soft shadow
* radius moderat
* strong typography hierarchy
* muted secondary text
* compact cards
* small status badges
* clear CTA
* consistent iconography

---

# 5. Color System

Pertahankan identitas PHPNuxBill melalui warna orange/amber, tetapi jangan menjadikan orange sebagai warna seluruh interface.

Primary:

* Orange / Amber: #F59E0B
* Orange darker: #D97706
* Orange soft background: #FFF7E6

Main dark:

* Navy 950: #0F172A
* Navy 900: #172033
* Navy 800: #1E293B

Light background:

* App background: #F5F7FA
* Surface: #FFFFFF
* Surface subtle: #F8FAFC

Text:

* Primary: #0F172A
* Secondary: #64748B
* Muted: #94A3B8

Status:

* Success: #16A34A
* Warning: #F59E0B
* Danger: #DC2626
* Info: #2563EB

Dark sidebar boleh dipertahankan.

Jangan membuat seluruh dashboard gelap.

Preferred layout:

* dark sidebar
* light main workspace
* white cards
* dark typography
* amber accent
* status colors hanya untuk status

---

# 6. Application Shell

Struktur utama:

```text
┌──────────────────────────────────────────────────────────┐
│ Sidebar │ Topbar                                          │
│         ├─────────────────────────────────────────────────┤
│         │ Breadcrumb / Page title                          │
│         │                                                  │
│         │ Main content                                     │
│         │                                                  │
│         │                                                  │
└──────────────────────────────────────────────────────────┘
```

Sidebar:

* fixed
* collapsible
* desktop width sekitar 250–280px
* collapsed sekitar 72px
* mobile menjadi drawer
* active state jelas
* icon + label
* grouped navigation

Topbar:

* breadcrumb/page context
* search
* network/system status
* notification
* profile menu

Jangan menggunakan topbar kosong seperti UI sekarang.

---

# 7. Sidebar Information Architecture

Jangan menampilkan semua menu sebagai daftar flat.

Gunakan grouping.

Contoh:

```text
PHPNuxBill

OVERVIEW
  Dashboard

CUSTOMERS
  Customers
  Orders
  Subscriptions

CATALOG
  Plans
  Bandwidth
  Vouchers

NETWORK
  Routers
  Online Users

FINANCE
  Payments
  Income Report

SYSTEM
  Notifications
  Logs
  Payment Settings
```

Jika backend saat ini menggunakan nama menu yang berbeda, jangan mengubah route/functionality hanya demi UI.

UI layer boleh melakukan grouping tanpa merusak existing route.

Sidebar active state:

```text
┌────────────────────────────┐
│ ◉ Dashboard                │
└────────────────────────────┘
```

Gunakan accent amber secara subtle.

Jangan membuat active item menjadi kotak kuning terang yang terlalu besar seperti saat ini.

---

# 8. Topbar

Topbar harus memiliki:

Left:

```text
Dashboard
Overview of your ISP operations
```

atau breadcrumb:

```text
Home / Dashboard
```

Right:

```text
● System Online
🔔
Administrator ▾
```

Tambahkan global search jika stack saat ini memungkinkan.

Search harus terasa seperti control-plane search:

```text
Search customers, vouchers, invoices...
```

Shortcut visual:

```text
⌘ K
```

Jangan menambahkan functionality backend baru hanya untuk search jika belum tersedia.

Jika tidak ada global search implementation, buat visual placeholder/component yang mudah diintegrasikan.

---

# 9. Dashboard Layout

Dashboard baru tidak boleh hanya:

```text
6 cards
1 chart
1 pie chart
1 chart
1 pie chart
```

Gunakan hierarchy.

Recommended:

```text
Page Header
    ↓
KPI Overview
    ↓
Revenue + Customer Growth
    ↓
Customer Health + Network Overview
    ↓
Recent Payments + Recent Activity
    ↓
Operational Insights
```

---

# 10. Dashboard Header

Contoh:

```text
Dashboard

Good morning, Administrator.
Here's what's happening with your ISP today.

[ + Add Customer ] [ Create Voucher ]
```

CTA jangan terlalu banyak.

Primary action:

```text
+ Add Customer
```

Secondary:

```text
Create Voucher
```

---

# 11. KPI Cards

Jangan membuat card hanya:

```text
Total Customer
1
```

Buat:

```text
┌────────────────────────────────────┐
│ Total Customers             👥     │
│                                    │
│ 1                                  │
│                                    │
│ ↑ 12.5%     vs last month          │
└────────────────────────────────────┘
```

Card harus memiliki:

* label
* value
* icon
* optional trend
* contextual comparison
* semantic status

Recommended KPIs:

1. Total Customers
2. Active Customers
3. Online Users
4. Monthly Revenue
5. Pending Payments
6. Voucher Usage

Jika backend belum menyediakan trend data, jangan mengarang data.

Gunakan hanya data yang benar-benar tersedia.

---

# 12. KPI Card Rules

Jangan menggunakan icon besar.

Icon:

* 18–22px
* contained inside subtle square/circle

Value:

* 26–32px
* semibold/bold

Label:

* 13–14px

Secondary information:

* 12–13px

Card radius:

* 10–14px

Border:

* subtle

Shadow:

* minimal

Cards harus terasa seperti satu system, bukan enam komponen berbeda.

---

# 13. Revenue Section

Revenue adalah salah satu informasi terpenting.

Gunakan card besar:

```text
Revenue Overview

Rp 12.450.000

+8.4% from previous period

[7D] [30D] [90D] [1Y]

     ╱╲
    ╱  ╲
___╱    ╲____╱╲____
```

Chart harus:

* clean
* minimal grid
* no excessive labels
* tooltip saat hover
* readable
* responsive

Gunakan amber/orange sebagai accent.

Jangan membuat chart menjadi rainbow.

---

# 14. Customer Growth

Customer growth berada sebagai secondary chart.

Contoh:

```text
Customer Growth

New Customers
────────────────────

Sep Oct Nov Dec Jan Feb Mar Apr May Jun Jul Aug
```

Gunakan:

* line/area chart
* subtle grid
* highlighted current month
* tooltip
* legend jika memang diperlukan

---

# 15. Customer Health

Jangan hanya donut:

```text
Status Customer
[ donut ]
Active (1)
```

Buat lebih informative:

```text
Customer Health

         82%
       Active

● Active          82%
● Expiring Soon   11%
● Suspended        7%
```

Jika data hanya memiliki Active saat ini, tampilkan hanya data yang tersedia.

Jangan fabricate categories.

---

# 16. Network Overview

Karena PHPNuxBill adalah ISP management system, dashboard harus memiliki network context.

Tambahkan panel:

```text
Network Overview

Routers                 3
Online                  2
Offline                 1

Active Sessions        48
Peak Today             71

[View Routers]
```

Status:

```text
● Online
● Degraded
● Offline
```

Jika data network belum tersedia di backend, jangan membuat fake values.

Buat component yang dapat menerima data existing.

---

# 17. Recent Payments

Dashboard harus memperlihatkan aktivitas finansial terbaru.

Contoh:

```text
Recent Payments                         View all →

Customer        Invoice       Amount          Status

Ahmad Fauzi     INV-001       Rp150.000       Paid
Budi            INV-002       Rp200.000       Paid
Citra           INV-003       Rp100.000       Pending
```

Status harus berupa compact badge.

---

# 18. Recent Activity

Tambahkan timeline.

Contoh:

```text
Recent Activity

● Payment received
  Ahmad Fauzi paid INV-001
  5 minutes ago

● Customer created
  Customer "Budi" was added
  21 minutes ago

● Voucher generated
  20 vouchers created
  1 hour ago
```

Timeline harus subtle.

Jangan menggunakan card berlebihan untuk setiap event.

---

# 19. Alerts / Attention Required

Tambahkan section kecil:

```text
Needs Attention

! 3 customers expire within 3 days
! Router MikroTik-02 is offline
! 4 unpaid invoices are overdue
```

Gunakan warning/danger hanya jika memang membutuhkan perhatian.

Ini membuat dashboard terasa operational.

---

# 20. Tables

Seluruh table harus mengikuti style modern:

Header:

* uppercase kecil atau medium
* muted color
* compact

Row:

* 52–64px
* subtle divider
* hover state

Status:

```text
● Active
● Suspended
● Pending
● Paid
● Overdue
```

Action:

```text
•••
```

Jangan menampilkan terlalu banyak tombol di setiap row.

---

# 21. Customer Page

Customer page harus menjadi salah satu page paling polished.

Header:

```text
Customers

Manage your subscribers and service accounts.

[Import] [Add Customer]
```

Stats:

```text
Total
Active
Expiring
Suspended
```

Filter bar:

```text
Search customers...

Status ▾
Plan ▾
Router ▾

[Filter]
[Reset]
```

Table:

```text
Customer
Account
Plan
Status
Expires
Balance
Actions
```

Row click dapat membuka customer detail.

---

# 22. Customer Detail

Gunakan layout:

```text
Customer Header

[Avatar]
Ahmad Fauzi
ahmad@example.com

● Active

[Edit] [Renew Subscription]
```

Tabs:

```text
Overview
Subscription
Payments
Sessions
Activity
```

Overview:

```text
Subscription
Plan
Bandwidth
Expires

Account
Username
Router
Connection status
```

Gunakan contextual information.

---

# 23. Plans Page

Plans harus terasa seperti product/catalog management.

Gunakan card/table hybrid.

Contoh:

```text
Internet Plans

Basic 10 Mbps
Rp100.000 / month

10 Mbps
1 Device
30 Days

[Edit]
```

Namun jika jumlah plan besar, gunakan table.

Jangan memaksa card untuk semua data.

---

# 24. Voucher Page

Voucher adalah fitur penting.

Buat dashboard mini:

```text
Vouchers

Total       Available       Used       Expired

13          8               3          2
```

Action:

```text
+ Generate Voucher
```

Generate modal harus profesional.

Flow:

```text
Voucher Configuration

Quantity
[ 20 ]

Plan
[ Basic 10 Mbps ▾ ]

Duration
[ 30 Days ▾ ]

Prefix
[ PHPN ]

[Cancel] [Generate]
```

Hasil:

```text
20 vouchers generated successfully.

[View Vouchers] [Download]
```

---

# 25. Orders / Payments

Gunakan financial UI language.

Status:

* Paid
* Pending
* Overdue
* Cancelled
* Refunded

Amount harus visually prominent.

Contoh:

```text
Rp 150.000
Paid
```

Overdue menggunakan danger.

Pending menggunakan warning.

Paid menggunakan success.

---

# 26. Router Page

Router page harus terasa seperti network management.

Contoh:

```text
Routers

3 Total
2 Online
1 Offline

┌──────────────────────────────┐
│ MikroTik Core                │
│ ● Online                     │
│                              │
│ 192.168.88.1                 │
│ 48 active sessions           │
│                              │
│ Last sync 12 sec ago         │
│                              │
│ [Open Router]                │
└──────────────────────────────┘
```

Jangan membuat router page terlihat seperti CRUD biasa.

---

# 27. Bandwidth Page

Gunakan visual hierarchy berdasarkan package.

Contoh:

```text
10 Mbps
Basic
Rp100.000 / month

20 Mbps
Standard
Rp150.000 / month

50 Mbps
Premium
Rp250.000 / month
```

Gunakan compact visual indicators.

---

# 28. Reports

Income Report harus terasa sebagai analytics page.

Header:

```text
Income Report

Track revenue and payment performance.

[Today] [7 Days] [30 Days] [90 Days]
```

Summary:

```text
Total Revenue
Rp 12.450.000

Paid
Rp 10.200.000

Pending
Rp 1.750.000

Overdue
Rp 500.000
```

Charts:

* revenue trend
* payment status
* revenue by plan
* payment method

Hanya implement chart jika data tersedia.

---

# 29. Logs

Logs jangan berupa table mentah yang melelahkan.

Gunakan:

```text
System Logs

[Search logs...]

Level   Event       Actor       Time

INFO    Login       Admin       09:22
INFO    Payment     Admin       09:20
WARN    Router      System      09:14
ERROR   RADIUS      System      09:10
```

Level harus memiliki visual distinction.

---

# 30. Empty States

Jangan menampilkan:

```text
No data.
```

Gunakan contextual empty state:

```text
No customers yet

Your customer list will appear here once you add
your first subscriber.

[Add Customer]
```

Tetap sederhana.

Jangan menggunakan ilustrasi besar yang tidak perlu.

---

# 31. Loading States

Gunakan skeleton.

Jangan hanya:

```text
Loading...
```

Skeleton harus mengikuti bentuk actual component.

Contoh:

```text
████████████
██████
████████████████
```

Untuk table:

* skeleton rows
* preserve layout dimensions

---

# 32. Error States

Error harus human readable.

Contoh:

```text
Unable to load customers

We couldn't retrieve customer data right now.

[Try again]
```

Jangan expose stack trace kepada user.

---

# 33. Toast

Toast:

* compact
* top-right atau bottom-right
* auto-dismiss
* status icon
* clear message

Examples:

```text
✓ Customer created successfully
✓ Payment recorded
! Unable to connect to router
```

Jangan menggunakan browser alert jika bisa dihindari.

---

# 34. Modal

Modal harus:

* centered
* clear title
* short description
* form
* footer action

Footer:

```text
Cancel                    Save Customer
```

Primary action di kanan.

Danger action harus terpisah secara visual.

---

# 35. Typography

Gunakan satu font family yang konsisten.

Preferred:

```text
Inter
```

Jika project existing sudah menggunakan font lain yang konsisten, jangan melakukan migration besar hanya untuk font.

Hierarchy:

Page title:

28–32px

Section title:

16–18px

Body:

14px

Small:

12–13px

KPI:

28–32px

Jangan membuat semua teks bold.

---

# 36. Spacing

Gunakan spacing system konsisten.

Preferred:

```text
4
8
12
16
20
24
32
40
48
```

Main dashboard padding:

Desktop:

24–32px

Mobile:

16px

Gap:

16–24px

---

# 37. Border Radius

Gunakan moderate radius.

Cards:

12px

Inputs:

8px

Buttons:

8px

Badges:

999px

Jangan semua komponen dibuat pill.

---

# 38. Icons

Gunakan satu icon library.

Preferred:

Lucide Icons.

Jangan mencampur:

* FontAwesome
* random SVG
* emoji
* Bootstrap icons

dalam satu interface.

Icon harus konsisten.

---

# 39. Buttons

Primary:

```text
+ Add Customer
```

Secondary:

```text
Export
```

Ghost:

```text
View all →
```

Danger:

```text
Delete
```

Button jangan terlalu tinggi.

Target:

36–40px.

---

# 40. Responsive

Desktop:

```text
Sidebar + Content
```

Tablet:

```text
Collapsed sidebar + Content
```

Mobile:

```text
Topbar
Drawer navigation
Single-column cards
Horizontal scroll tables where necessary
```

Jangan membuat dashboard desktop mengecil secara paksa ke mobile.

Chart harus responsive.

Cards:

Desktop:

```text
4–6 columns depending on data
```

Tablet:

```text
2 columns
```

Mobile:

```text
1 column
```

---

# 41. Accessibility

Pastikan:

* keyboard navigation
* visible focus
* semantic buttons
* aria labels untuk icon-only buttons
* sufficient contrast
* no information conveyed only through color
* modal focus handling
* escape closes modal
* tooltip tidak menjadi satu-satunya cara memahami icon

---

# 42. Animation

Gunakan sangat sedikit animation.

Allowed:

* sidebar collapse
* dropdown
* modal
* toast
* hover
* chart appearance
* skeleton shimmer

Duration:

150–250ms.

Tidak perlu:

* parallax
* animated gradients
* excessive bouncing
* page transition berlebihan

---

# 43. Dashboard Specific Target

Dashboard final minimal memiliki:

```text
┌ Sidebar ┐ ┌───────────────────────────────────────────┐
│         │ │ Dashboard                  Search  Admin  │
│         │ ├───────────────────────────────────────────┤
│         │ │ Good morning, Administrator               │
│         │ │ Overview of your ISP today.   [+ Customer]│
│         │ │                                           │
│         │ │ KPI   KPI   KPI   KPI                     │
│         │ │                                           │
│         │ │ Revenue Overview      Customer Growth     │
│         │ │                                           │
│         │ │ Customer Health       Network Overview    │
│         │ │                                           │
│         │ │ Recent Payments       Recent Activity    │
│         │ │                                           │
│         │ │ Needs Attention                           │
└─────────┘ └───────────────────────────────────────────┘
```

Tidak harus persis seperti ini.

Yang penting hierarchy-nya.

---

# 44. Existing Functionality Is Sacred

CRITICAL.

Jangan merusak functionality yang sudah ada.

Jangan mengubah:

* API
* database schema
* authentication
* authorization
* routes
* business logic
* payment logic
* voucher logic
* RADIUS integration
* MikroTik integration
* existing CRUD behavior

kecuali perubahan memang mutlak diperlukan untuk UI.

Prioritas:

```text
Existing functionality
        ↓
Existing data
        ↓
Existing routes
        ↓
Existing API
        ↓
UI redesign
```

UI redesign harus berada di atas existing system, bukan mengganti sistem.

---

# 45. Do Not Over Engineer

Jangan:

* membuat design system package baru
* membuat UI framework sendiri
* membuat abstraction layer berlebihan
* membuat state management baru tanpa alasan
* menambah dependency besar hanya untuk komponen kecil
* mengubah backend hanya untuk statistik dekoratif
* membuat fake data
* membuat API baru hanya untuk mempercantik dashboard
* melakukan rewrite project

Gunakan infrastructure existing.

Jika project sudah memiliki:

* Tailwind → gunakan Tailwind
* Bootstrap → gunakan Bootstrap
* React → gunakan React
* Vue → gunakan Vue
* Chart library → gunakan existing library

Jangan melakukan migration stack.

---

# 46. Data Integrity

CRITICAL.

Tidak boleh ada fake metrics.

Contoh:

Jika backend hanya menghasilkan:

```text
Total Customer: 1
Active Customer: 1
Voucher: 13
Income: Rp50.000
```

maka UI harus tetap menggunakan nilai tersebut.

Jangan membuat:

```text
+12.5%
+8.4%
48 active sessions
71 peak
```

jika backend tidak memiliki data tersebut.

Untuk visual yang belum mempunyai source data, gunakan:

```text
—
```

atau sembunyikan secondary metric.

---

# 47. Visual Quality Bar

Sebelum dianggap selesai, compare dengan screenshot baseline.

Old:

```text
generic admin panel
```

Target:

```text
professional ISP control plane
```

Check:

* Does sidebar feel intentional?
* Does dashboard hierarchy feel clear?
* Does the eye immediately find important information?
* Do cards have meaningful content?
* Does the application look expensive/professional?
* Does it feel like a real network/business system?
* Is spacing consistent?
* Are status colors meaningful?
* Are charts integrated rather than pasted?
* Does mobile remain usable?

---

# 48. Reference Philosophy

Cloudflare:

* control-plane feeling
* strong navigation hierarchy
* information architecture
* professional operational UI
* scalable dashboard structure

9Router:

* modern developer/control-plane dashboard
* clear system states
* cards used for meaningful operational information
* modern settings/provider management
* compact but polished interface

Do NOT copy their branding, logo, exact layouts, or exact colors.

PHPNuxBill must have its own identity.

---

# 49. Definition of Done

Redesign is considered complete only when:

* [ ] Dashboard no longer looks like a generic admin template
* [ ] Sidebar has grouped navigation
* [ ] Sidebar is collapsible
* [ ] Topbar has proper hierarchy
* [ ] Dashboard has clear page header
* [ ] KPI cards have icon + contextual information
* [ ] Revenue section is visually dominant
* [ ] Customer growth section is integrated
* [ ] Customer status is more informative
* [ ] Recent activity exists where data allows
* [ ] Recent payments exists where data allows
* [ ] Empty states are polished
* [ ] Loading states use skeletons
* [ ] Error states are polished
* [ ] Tables have modern spacing and status badges
* [ ] Customer page receives proper visual treatment
* [ ] Voucher page receives proper visual treatment
* [ ] Router page feels like network management
* [ ] Payment/income pages feel like financial tools
* [ ] Mobile is usable
* [ ] No fake data is introduced
* [ ] Existing functionality remains intact
* [ ] Existing routes remain intact
* [ ] Existing backend remains intact
* [ ] No unnecessary dependency migration
* [ ] No over-engineering
* [ ] UI has consistent typography
* [ ] UI has consistent spacing
* [ ] UI has consistent icons
* [ ] UI has consistent status colors
* [ ] UI has meaningful hover/focus/loading states

---

# 50. Final Principle

Do not think:

"How do I make the existing admin page prettier?"

Think:

"How would a professional ISP operator control customers, billing, vouchers, routers, and service status from one control plane?"

The result should be PHPNuxBill with the same functionality and data, but with a significantly more mature information architecture, visual hierarchy, and interaction design.
