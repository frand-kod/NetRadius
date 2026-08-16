# 14-dashboard.md — Dashboard Modern dengan Chart (Customer, User Online, Voucher)

Task ini membangun ulang halaman **Admin Dashboard** menjadi dashboard dengan KPI cards + chart.
**JANGAN install library chart apa pun** (Chart.js, ApexCharts, Recharts, dsb). Semua chart dibuat
dengan **SVG murni + Vue + Tailwind** yang sudah ada. Ini 100% tanpa dependency baru.

Baca `12-styling-system.md` dulu (gunakan recipe R1–R19). Backend service tidak boleh diubah,
tapi **`DashboardController.php` BOLEH diubah** (itu controller halaman, bukan service).

---

## 1. Sumber Data (dari DB)

| Data | Sumber | Kolom |
|------|--------|-------|
| Total / aktif customer | `tbl_customers` | `status`, `created_at` |
| User online | `rad_acct` (RADIUS) | `username`, `acctstatustype`, `framedipaddress`, `acctsessiontime`, `macaddr`, `dateAdded` |
| Voucher terpakai / belum | `tbl_voucher` | `status` (`'0'` = belum dipakai) |
| Pendapatan | `tbl_transactions` | `price`, `recharged_on` (tanggal) |

> **Catatan "user online":** dihitung dari sesi RADIUS `acctstatustype = 'Start'` (distinct username).
> Ini estimasi sesi online yang tercatat. Untuk lingkungan kecil (~30 customer) ini wajar. Jangan
> hitung via API Mikrotik (butuh router hidup + per-router loop) — di luar scope task ini.

---

## 2. GANTI `app/Http/Controllers/Admin/DashboardController.php` (Timpah Penuh)

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function show(): \Inertia\Response
    {
        $unusedVouchers = Voucher::where('status', '0')->count();
        $usedVouchers = Voucher::where('status', '!=', '0')->count();

        // --- User online dari rad_acct (sesi RADIUS aktif) ---
        $onlineQuery = DB::table('rad_acct')->where('acctstatustype', 'Start');
        $onlineCount = (clone $onlineQuery)->distinct()->count('username');
        $onlineUsers = (clone $onlineQuery)
            ->select('username', 'framedipaddress', 'acctsessiontime', 'macaddr', 'dateAdded')
            ->orderByDesc('dateAdded')
            ->limit(10)
            ->get();

        // --- Customer baru per bulan (12 bulan terakhir) ---
        $customerTrend = collect(range(0, 11))->map(function (int $i) {
            $m = now()->subMonths(11 - $i);

            return [
                'label' => $m->format('M'),
                'value' => Customer::whereYear('created_at', $m->year)
                    ->whereMonth('created_at', $m->month)
                    ->count(),
            ];
        });

        // --- Pendapatan per hari (30 hari terakhir) ---
        $incomeTrend = collect(range(0, 29))->map(function (int $i) {
            $d = now()->subDays(29 - $i);

            return [
                'label' => $d->format('d/m'),
                'value' => (float) Transaction::whereDate('recharged_on', $d->toDateString())
                    ->sum('price'),
            ];
        });

        // --- Distribusi status customer ---
        $statuses = ['Active', 'Banned', 'Disabled', 'Inactive', 'Limited', 'Suspended'];
        $customerStatus = collect($statuses)
            ->map(fn (string $s) => ['label' => $s, 'value' => Customer::where('status', $s)->count()])
            ->filter(fn (array $s) => $s['value'] > 0)
            ->values();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'totalCustomers' => Customer::count(),
                'activeCustomers' => Customer::where('status', 'Active')->count(),
                'onlineUsers' => $onlineCount,
                'unusedVouchers' => $unusedVouchers,
                'usedVouchers' => $usedVouchers,
                'totalIncome' => (float) Transaction::sum('price'),
            ],
            'customerTrend' => $customerTrend,
            'incomeTrend' => $incomeTrend,
            'customerStatus' => $customerStatus,
            'voucherUsage' => [
                ['label' => 'Belum Dipakai', 'value' => $unusedVouchers, 'color' => '#f59e0b'],
                ['label' => 'Terpakai', 'value' => $usedVouchers, 'color' => '#16a34a'],
            ],
            'onlineUsers' => $onlineUsers,
        ]);
    }
}
```

> `where('status', '!=', '0')` untuk voucher terpakai. Jika nanti ada baris dengan `status` kosong,
> mereka masuk hitungan "terpakai". Untuk lingkungan ini sudah benar.

---

## 3. Komponen Chart (Buat 3 FILE BARU)

Buat folder `resources/js/Components/` (sudah dicontohkan di README) lalu isi 3 file di bawah.
Masing-masing komponen murni SVG, `script setup`, props, tanpa library.

### 3a. `resources/js/Components/BarChart.vue`

```vue
<script setup>
import { computed } from 'vue';

const props = defineProps({
    labels: { type: Array, default: () => [] },
    values: { type: Array, default: () => [] },
    color: { type: String, default: '#f59e0b' },
});

const W = 600, H = 220, PAD_L = 40, PAD_B = 30, PAD_T = 14, PAD_R = 12;
const chartW = computed(() => W - PAD_L - PAD_R);
const chartH = computed(() => H - PAD_T - PAD_B);
const max = computed(() => Math.max(...props.values, 1));
const n = computed(() => Math.max(props.values.length, 1));
const slotW = computed(() => chartW.value / n.value);
const barW = computed(() => Math.max(slotW.value * 0.6, 4));

function barX(i) {
    return PAD_L + i * slotW.value + (slotW.value - barW.value) / 2;
}
function barY(v) {
    return PAD_T + chartH.value - (v / max.value) * chartH.value;
}
function barH(v) {
    return (v / max.value) * chartH.value;
}
</script>

<template>
    <svg :viewBox="`0 0 ${W} ${H}`" class="h-auto w-full" role="img">
        <line v-for="i in 4" :key="'g' + i"
              :x1="PAD_L" :x2="W - PAD_R"
              :y1="PAD_T + (chartH / 4) * i" :y2="PAD_T + (chartH / 4) * i"
              stroke="#e5e7eb" stroke-width="1" />
        <rect v-for="(v, i) in values" :key="'b' + i"
              :x="barX(i)" :y="barY(v)" :width="barW" :height="barH(v)"
              :fill="color" rx="3" />
        <text v-for="(v, i) in values" :key="'vl' + i"
              :x="barX(i) + barW / 2" :y="barY(v) - 4"
              text-anchor="middle" class="fill-gray-400 text-[10px]">{{ v }}</text>
        <text v-for="(l, i) in labels" :key="'lb' + i"
              :x="PAD_L + i * slotW + slotW / 2" :y="H - 8"
              text-anchor="middle" class="fill-gray-400 text-[10px]">{{ l }}</text>
    </svg>
</template>
```

### 3b. `resources/js/Components/LineChart.vue`

```vue
<script setup>
import { computed } from 'vue';

const props = defineProps({
    labels: { type: Array, default: () => [] },
    values: { type: Array, default: () => [] },
    color: { type: String, default: '#f59e0b' },
});

const W = 600, H = 220, PAD_L = 40, PAD_B = 30, PAD_T = 14, PAD_R = 12;
const chartW = computed(() => W - PAD_L - PAD_R);
const chartH = computed(() => H - PAD_T - PAD_B);
const max = computed(() => Math.max(...props.values, 1));

function px(i) {
    if (props.values.length > 1) return PAD_L + i * (chartW.value / (props.values.length - 1));
    return PAD_L + chartW.value / 2;
}
function py(v) {
    return PAD_T + chartH.value - (v / max.value) * chartH.value;
}

const linePoints = computed(() => props.values.map((v, i) => `${px(i)},${py(v)}`).join(' '));
const areaPoints = computed(() =>
    `${PAD_L},${PAD_T + chartH} ${linePoints.value} ${PAD_L + chartW.value},${PAD_T + chartH}`);
</script>

<template>
    <svg :viewBox="`0 0 ${W} ${H}`" class="h-auto w-full" role="img">
        <line v-for="i in 4" :key="'g' + i"
              :x1="PAD_L" :x2="W - PAD_R"
              :y1="PAD_T + (chartH / 4) * i" :y2="PAD_T + (chartH / 4) * i"
              stroke="#e5e7eb" stroke-width="1" />
        <polygon :points="areaPoints" :fill="color" fill-opacity="0.12" />
        <polyline :points="linePoints" fill="none" :stroke="color" stroke-width="2.5"
                  stroke-linejoin="round" stroke-linecap="round" />
        <circle v-for="(v, i) in values" :key="'c' + i"
                :cx="px(i)" :cy="py(v)" r="3.5" :fill="color"
                class="stroke-white" stroke-width="1.5" />
        <text v-for="(l, i) in labels" :key="'lb' + i"
              :x="px(i)" :y="H - 8"
              text-anchor="middle" class="fill-gray-400 text-[10px]">{{ l }}</text>
    </svg>
</template>
```

### 3c. `resources/js/Components/DonutChart.vue`

```vue
<script setup>
import { computed } from 'vue';

const props = defineProps({
    segments: { type: Array, default: () => [] }, // [{label, value, color}]
    size: { type: Number, default: 180 },
});

const R = 70, C = 2 * Math.PI * R;
const total = computed(() =>
    props.segments.reduce((s, x) => s + Number(x.value || 0), 0) || 1);

const arcs = computed(() => {
    let acc = 0;
    return props.segments.map((s) => {
        const frac = Number(s.value || 0) / total.value;
        const offset = acc * C;
        acc += frac;
        return { ...s, frac, offset };
    });
});
</script>

<template>
    <div class="flex flex-col items-center gap-3">
        <svg :width="size" :height="size" viewBox="0 0 180 180" class="-rotate-90">
            <circle cx="90" cy="90" :r="R" fill="none" stroke="#e5e7eb" stroke-width="24" />
            <circle v-for="(a, i) in arcs" :key="'d' + i"
                    cx="90" cy="90" :r="R" fill="none" :stroke="a.color" stroke-width="24"
                    :stroke-dasharray="`${a.frac * C} ${C}`" :stroke-dashoffset="a.offset" />
        </svg>
        <ul class="flex flex-wrap justify-center gap-x-4 gap-y-1">
            <li v-for="(a, i) in arcs" :key="'l' + i"
                class="flex items-center gap-1.5 text-xs text-gray-600">
                <span class="inline-block h-2.5 w-2.5 rounded-full"
                      :style="{ backgroundColor: a.color }"></span>
                {{ a.label }} ({{ a.value }})
            </li>
        </ul>
    </div>
</template>
```

---

## 4. GANTI `resources/js/Pages/Admin/Dashboard.vue` (Timpah Penuh)

```vue
<script setup>
import { computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BarChart from '@/Components/BarChart.vue';
import LineChart from '@/Components/LineChart.vue';
import DonutChart from '@/Components/DonutChart.vue';

const props = defineProps({
    stats: Object,
    customerTrend: Array,
    incomeTrend: Array,
    customerStatus: Array,
    voucherUsage: Array,
    onlineUsers: Array,
});

const STATUS_COLORS = {
    Active: '#16a34a',
    Banned: '#dc2626',
    Disabled: '#dc2626',
    Inactive: '#f59e0b',
    Suspended: '#f59e0b',
    Limited: '#6b7280',
};

const statusSegments = computed(() =>
    (props.customerStatus || []).map((s) => ({
        ...s,
        color: STATUS_COLORS[s.label] || '#6b7280',
    })));

const kpis = computed(() => [
    { label: 'Total Customer', value: props.stats?.totalCustomers, color: 'text-gray-900' },
    { label: 'Customer Aktif', value: props.stats?.activeCustomers, color: 'text-green-600' },
    { label: 'User Online', value: props.stats?.onlineUsers, color: 'text-blue-600' },
    { label: 'Voucher Belum Dipakai', value: props.stats?.unusedVouchers, color: 'text-amber-600' },
    { label: 'Voucher Terpakai', value: props.stats?.usedVouchers, color: 'text-gray-900' },
    { label: 'Total Income', value: 'Rp ' + Number(props.stats?.totalIncome || 0).toLocaleString('id-ID'), color: 'text-gray-900' },
]);
</script>

<template>
    <AdminLayout>
        <template #title>Dashboard</template>

        <!-- KPI Cards -->
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-3 xl:grid-cols-6">
            <div v-for="k in kpis" :key="k.label"
                 class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-gray-500">{{ k.label }}</p>
                <p class="mt-1 text-2xl font-bold" :class="k.color">{{ k.value }}</p>
            </div>
        </div>

        <!-- Bar: Customer baru / bulan  +  Donut: Status customer -->
        <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm lg:col-span-2">
                <h3 class="mb-3 text-sm font-semibold text-gray-900">Customer Baru per Bulan</h3>
                <BarChart :labels="customerTrend.map(t => t.label)"
                          :values="customerTrend.map(t => t.value)" color="#f59e0b" />
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="mb-3 text-sm font-semibold text-gray-900">Status Customer</h3>
                <DonutChart :segments="statusSegments" />
            </div>
        </div>

        <!-- Line: Pendapatan 30 hari  +  Donut: Voucher -->
        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm lg:col-span-2">
                <h3 class="mb-3 text-sm font-semibold text-gray-900">Pendapatan 30 Hari Terakhir</h3>
                <LineChart :labels="incomeTrend.map(t => t.label)"
                           :values="incomeTrend.map(t => t.value)" color="#2563eb" />
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="mb-3 text-sm font-semibold text-gray-900">Penggunaan Voucher</h3>
                <DonutChart :segments="voucherUsage" />
            </div>
        </div>

        <!-- Tabel: User Online -->
        <div class="mt-4 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-4 py-3">
                <h3 class="text-sm font-semibold text-gray-900">User Online (Sesi RADIUS)</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Username</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">IP</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Durasi (dtk)</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">MAC</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Aktif Sejak</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="u in onlineUsers" :key="u.acctsessionid"
                            class="border-b border-gray-100 transition hover:bg-amber-50/40">
                            <td class="px-4 py-3 font-mono text-gray-700">{{ u.username }}</td>
                            <td class="px-4 py-3 font-mono text-gray-700">{{ u.framedipaddress || '-' }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ u.acctsessiontime }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ u.macaddr || '-' }}</td>
                            <td class="px-4 py-3 text-xs text-gray-700">{{ u.dateAdded }}</td>
                        </tr>
                        <tr v-if="onlineUsers.length === 0">
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">
                                Tidak ada sesi online terpantau.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
```

---

## 5. Verifikasi

1. Build berhasil (tidak ada library baru yang diinstall):
   ```
   npm run build
   ```
2. Login admin (`admin` / `admin123`), buka `/admin`.
3. Cek:
   - [ ] 6 KPI card muncul (Total, Aktif, Online, Voucher belum/terpakai, Income).
   - [ ] Bar chart "Customer Baru per Bulan" menampilkan data.
   - [ ] Donut "Status Customer" menampilkan segmen berwarna + legend.
   - [ ] Line chart "Pendapatan 30 Hari" menampilkan garis biru.
   - [ ] Donut "Penggunaan Voucher" menampilkan amber (belum) vs hijau (terpakai).
   - [ ] Tabel "User Online" menampilkan sesi RADIUS (atau baris kosong jika tidak ada data).
   - [ ] Halaman responsif (chart mengecil di layar sempit).
4. Kalau `rad_acct` kosong, wajar: tabel online kosong & KPI online = 0. Tambahkan data uji bila perlu lewat tinker (tidak wajib).

## 6. Jangan Sentuh
- JANGAN install package chart (Chart.js/ApexCharts).
- JANGAN ubah backend service (`app/Services/**`).
- JANGAN ubah `Routes`, model, atau migration.
- Boleh ubah hanya: `DashboardController.php`, `Dashboard.vue`, dan 3 komponen chart baru.
