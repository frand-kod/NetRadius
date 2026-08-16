<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue';
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
    usage: Object,
    expiring: Array,
    topPlans: Array,
});

// --- Realtime: data yang di-polling ringan dari /dashboard/realtime ---
const realtime = ref(false);
let pollTimer = null;
const usageData = ref(props.usage);
const onlineData = ref(props.onlineUsers);
const expiringData = ref(props.expiring);

async function pollRealtime() {
    try {
        const res = await fetch('/admin/dashboard/realtime', { headers: { Accept: 'application/json' } });
        const data = await res.json();
        if (data.usage) usageData.value = data.usage;
        if (data.onlineUsers) onlineData.value = data.onlineUsers;
        if (data.expiring) expiringData.value = data.expiring;
    } catch {
        // Abaikan error polling (jaringan/offline) — data tetap yang terakhir.
    }
}

watch(realtime, (on) => {
    if (on) {
        pollRealtime();
        pollTimer = setInterval(pollRealtime, 5000);
    } else if (pollTimer) {
        clearInterval(pollTimer);
        pollTimer = null;
    }
});

onBeforeUnmount(() => pollTimer && clearInterval(pollTimer));

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

// Interval perbandingan: customer (hari/minggu/bulan) & usage rad_acct (menit).
const customerInterval = ref('Bulan Ini');
const usageInterval = ref(5);

const comparison = computed(() => props.stats?.comparison);

const customerPct = computed(() =>
    comparison.value?.customers?.find((c) => c.key === customerInterval.value)?.pct ?? null);
const sessionPct = computed(() =>
    comparison.value?.usage?.find((u) => u.key === usageInterval.value)?.sessions ?? null);
const downPct = computed(() =>
    comparison.value?.usage?.find((u) => u.key === usageInterval.value)?.down ?? null);
const upPct = computed(() =>
    comparison.value?.usage?.find((u) => u.key === usageInterval.value)?.up ?? null);

// Helper render indikator: null → "—", naik → hijau ▲, turun → merah ▼.
function trend(pct) {
    if (pct === null || pct === undefined) return { arrow: '', color: 'text-gray-400', text: '—' };
    const up = pct >= 0;
    return {
        arrow: up ? '▲' : '▼',
        color: up ? 'text-green-600' : 'text-red-600',
        text: Math.abs(pct) + '%',
    };
}

function usagePct(label) {
    if (label === 'Sesi Aktif') return sessionPct.value;
    if (label === 'Total Download') return downPct.value;
    return upPct.value;
}

const kpis = computed(() => [
    { label: 'Total Customers', value: props.stats?.totalCustomers, color: 'text-gray-900 dark:text-gray-100', trend: true },
    { label: 'Online Users', value: usageData.value?.activeSessions, color: 'text-blue-600' },
]);

const usageCards = computed(() => [
    { label: 'Sesi Aktif', value: usageData.value?.activeSessions ?? 0, color: 'text-gray-900 dark:text-gray-100', trend: true },
    { label: 'Total Download', value: formatBytes(usageData.value?.totalDown), color: 'text-blue-600', trend: true },
    { label: 'Total Upload', value: formatBytes(usageData.value?.totalUp), color: 'text-amber-600', trend: true },
    { label: 'Rata-rata Kecepatan', value: formatSpeed(usageData.value?.avgSpeed), color: 'text-green-600' },
]);

function formatBytes(bytes) {
    const b = Number(bytes) || 0;
    if (b === 0) return '0 B';
    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.min(Math.floor(Math.log(b) / Math.log(1024)), units.length - 1);
    return (b / Math.pow(1024, i)).toFixed(1) + ' ' + units[i];
}

function formatSpeed(bps) {
    const v = Number(bps) || 0;
    if (v >= 1000000) return (v / 1000000).toFixed(2) + ' Mbps';
    if (v >= 1000) return (v / 1000).toFixed(1) + ' Kbps';
    return v + ' bps';
}

function userSpeed(u) {
    return u.time > 0 ? formatSpeed((u.volume * 8) / u.time) : '-';
}
</script>

<template>
    <AdminLayout>
        <template #title>Dashboard</template>

        <!-- Realtime toggle -->
        <div class="mb-4 flex items-center justify-end gap-2">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Realtime (5 detik)</span>
            <button type="button" @click="realtime = !realtime" role="switch" :aria-checked="realtime"
                    :aria-label="realtime ? 'Matikan realtime' : 'Aktifkan realtime'"
                    class="relative h-5 w-9 rounded-full transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-600"
                    :class="realtime ? 'bg-amber-600' : 'bg-gray-300 dark:bg-gray-600'">
                <span class="absolute top-0.5 h-4 w-4 rounded-full bg-white shadow transition-all" :class="realtime ? 'left-4' : 'left-0.5'"></span>
            </button>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div v-for="k in kpis" :key="k.label"
                 class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 transition-colors">
                <div v-if="k.trend" class="mb-1 flex items-center justify-between gap-2">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ k.label }}</p>
                    <select v-model="customerInterval"
                            class="rounded-md border border-gray-300 bg-white px-2 py-1 text-xs text-gray-600 focus:border-amber-500 focus:outline-none dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300">
                        <option v-for="opt in ['Hari Ini', 'Minggu Ini', 'Bulan Ini']" :key="opt" :value="opt">{{ opt }}</option>
                    </select>
                </div>
                <p v-else class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ k.label }}</p>
                <p class="mt-1 text-2xl font-bold" :class="k.color">{{ k.value }}</p>
                <p v-if="k.trend" class="mt-1 text-xs font-medium" :class="trend(customerPct).color">
                    {{ trend(customerPct).arrow }} {{ trend(customerPct).text }}
                    <span class="text-gray-400">vs {{ customerInterval.toLowerCase() }}</span>
                </p>
            </div>
        </div>

        <!-- Statistik Penggunaan Aktif (RADIUS) -->
        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="flex justify-end lg:col-span-4">
                <label class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                    Perbandingan
                    <select v-model="usageInterval"
                            class="rounded-md border border-gray-300 bg-white px-2 py-1 text-xs text-gray-600 focus:border-amber-500 focus:outline-none dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300">
                        <option v-for="m in [5, 10, 15, 60]" :key="m" :value="m">{{ m }} menit</option>
                    </select>
                </label>
            </div>
            <div v-for="u in usageCards" :key="u.label"
                 class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 transition-colors">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ u.label }}</p>
                <p class="mt-1 text-2xl font-bold" :class="u.color">{{ u.value }}</p>
                <p v-if="u.trend" class="mt-1 text-xs font-medium" :class="trend(usagePct(u.label)).color">
                    {{ trend(usagePct(u.label)).arrow }} {{ trend(usagePct(u.label)).text }}
                    <span class="text-gray-400">vs {{ usageInterval }} mnt lalu</span>
                </p>
            </div>
        </div>

        <!-- Akan Expired + Top Terlaris -->
        <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 transition-colors">
                <div class="border-b border-gray-200 dark:border-gray-700 px-4 py-3">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Akan Expired (7 Hari)</h3>
                </div>
                <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                    <li v-for="e in expiringData" :key="e.username + e.expires_at" class="flex items-center justify-between gap-3 px-4 py-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{ e.username }}</p>
                            <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ e.plan }} · {{ e.expires_at }}</p>
                        </div>
                        <span class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-medium"
                              :class="e.days_left <= 1 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700'">
                            {{ e.days_left === 0 ? 'Hari ini' : e.days_left + ' hari' }}
                        </span>
                    </li>
                    <li v-if="expiringData.length === 0" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                        Tidak ada paket yang akan habis dalam 7 hari.
                    </li>
                </ul>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 transition-colors">
                <div class="border-b border-gray-200 dark:border-gray-700 px-4 py-3">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Top Paket Terlaris (30 Hari)</h3>
                </div>
                <ol class="divide-y divide-gray-100 dark:divide-gray-700">
                    <li v-for="(p, i) in topPlans" :key="p.name" class="flex items-center gap-3 px-4 py-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-amber-100 text-xs font-bold text-amber-700">
                            {{ i + 1 }}
                        </span>
                        <p class="min-w-0 flex-1 truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{ p.name }}</p>
                        <span class="shrink-0 text-xs font-medium text-gray-500 dark:text-gray-400">{{ p.count }} terjual</span>
                    </li>
                    <li v-if="topPlans.length === 0" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                        Belum ada penjualan dalam 30 hari terakhir.
                    </li>
                </ol>
            </div>
        </div>

        <!-- Bar: Customer baru / bulan  +  Donut: Status customer -->
        <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm lg:col-span-2 dark:border-gray-700 dark:bg-gray-800 transition-colors">
                <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-gray-100">Customer Baru per Bulan</h3>
                <BarChart :labels="customerTrend.map(t => t.label)"
                          :values="customerTrend.map(t => t.value)" color="#f59e0b" />
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 transition-colors">
                <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-gray-100">Status Customer</h3>
                <DonutChart :segments="statusSegments" />
            </div>
        </div>

        <!-- Line: Pendapatan 30 hari  +  Donut: Voucher -->
        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm lg:col-span-2 dark:border-gray-700 dark:bg-gray-800 transition-colors">
                <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-gray-100">Pendapatan 30 Hari Terakhir</h3>
                <LineChart :labels="incomeTrend.map(t => t.label)"
                           :values="incomeTrend.map(t => t.value)" color="#2563eb" />
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 transition-colors">
                <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-gray-100">Penggunaan Voucher</h3>
                <DonutChart :segments="voucherUsage" />
            </div>
        </div>

        <!-- Tabel: User Online + usage -->
        <div class="mt-4 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 transition-colors">
            <div class="border-b border-gray-200 dark:border-gray-700 px-4 py-3 transition-colors">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">User Online (Sesi RADIUS)</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 transition-colors">
                        <tr>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Username</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">IP</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 text-right">Download</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 text-right">Upload</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 text-right">Volume</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 text-right">Kecepatan</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 text-right">Durasi (dtk)</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Aktif Sejak</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="u in onlineData" :key="u.username + u.dateAdded"
                            class="border-b border-gray-100 dark:border-gray-700 transition hover:bg-amber-50/40 dark:hover:bg-gray-700/50"
                            :class="{ 'border-b-0': u === onlineUsers[onlineUsers.length - 1] }">
                            <td class="px-4 py-3 align-top font-mono text-gray-700 dark:text-gray-300">{{ u.username }}</td>
                            <td class="px-4 py-3 align-top font-mono text-gray-700 dark:text-gray-300">{{ u.framedipaddress || '-' }}</td>
                            <td class="px-4 py-3 align-top text-right text-gray-700 dark:text-gray-300">{{ formatBytes(u.down) }}</td>
                            <td class="px-4 py-3 align-top text-right text-gray-700 dark:text-gray-300">{{ formatBytes(u.up) }}</td>
                            <td class="px-4 py-3 align-top text-right font-medium text-gray-700 dark:text-gray-300">{{ formatBytes(u.volume) }}</td>
                            <td class="px-4 py-3 align-top text-right text-gray-700 dark:text-gray-300">{{ userSpeed(u) }}</td>
                            <td class="px-4 py-3 align-top text-right text-gray-700 dark:text-gray-300">{{ u.time }}</td>
                            <td class="px-4 py-3 align-top text-xs text-gray-700 dark:text-gray-300">{{ u.dateAdded }}</td>
                        </tr>
                        <tr v-if="onlineData.length === 0">
                            <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                Tidak ada sesi online terpantau.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
