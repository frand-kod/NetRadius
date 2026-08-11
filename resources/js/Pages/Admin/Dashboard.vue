<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({ stats: Object });
</script>

<template>
    <AdminLayout>
        <template #title>Dashboard</template>
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-white rounded shadow p-4">
                <p class="text-sm text-gray-500">Total Customer</p>
                <p class="text-2xl font-bold">{{ stats.totalCustomers }}</p>
            </div>
            <div class="bg-white rounded shadow p-4">
                <p class="text-sm text-gray-500">Customer Aktif</p>
                <p class="text-2xl font-bold text-green-600">{{ stats.activeCustomers }}</p>
            </div>
            <div class="bg-white rounded shadow p-4">
                <p class="text-sm text-gray-500">Order Pending</p>
                <p class="text-2xl font-bold text-amber-600">{{ stats.pendingOrders }}</p>
            </div>
            <div class="bg-white rounded shadow p-4">
                <p class="text-sm text-gray-500">Voucher Tersedia</p>
                <p class="text-2xl font-bold text-blue-600">{{ stats.unusedVouchers }}</p>
            </div>
            <div class="bg-white rounded shadow p-4">
                <p class="text-sm text-gray-500">Total Income</p>
                <p class="text-2xl font-bold">Rp {{ Number(stats.totalIncome).toLocaleString('id-ID') }}</p>
            </div>
        </div>
    </AdminLayout>
</template>
