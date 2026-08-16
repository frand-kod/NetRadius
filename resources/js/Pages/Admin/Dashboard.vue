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
    usage: Object,
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
    { label: 'Total Customers', value: props.stats?.totalCustomers, color: 'text-gray-900 dark:text-gray-100' },
    { label: 'Online Users', value: props.stats?.onlineUsers, color: 'text-blue-600' },
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

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div v-for="k in kpis" :key="k.label"
                 class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 transition-colors">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ k.label }}</p>
                <p class="mt-1 text-2xl font-bold" :class="k.color">{{ k.value }}</p>
            </div>
        </div>

        <!-- Statistik Penggunaan Aktif (RADIUS) -->
        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 transition-colors">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Sesi Aktif</p>
                <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-gray-100">{{ usage?.activeSessions ?? 0 }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 transition-colors">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Download</p>
                <p class="mt-1 text-2xl font-bold text-blue-600">{{ formatBytes(usage?.totalDown) }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 transition-colors">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Upload</p>
                <p class="mt-1 text-2xl font-bold text-amber-600">{{ formatBytes(usage?.totalUp) }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 transition-colors">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Rata-rata Kecepatan</p>
                <p class="mt-1 text-2xl font-bold text-green-600">{{ formatSpeed(usage?.avgSpeed) }}</p>
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
                        <tr v-for="u in onlineUsers" :key="u.username + u.dateAdded"
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
                        <tr v-if="onlineUsers.length === 0">
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
