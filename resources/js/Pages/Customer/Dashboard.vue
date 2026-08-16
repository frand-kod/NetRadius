<script setup>
import { useForm } from '@inertiajs/vue3';
import Icon from '@/Components/Icon.vue';
import ThemeToggler from '@/Components/ThemeToggler.vue';
import { computed } from 'vue';

const props = defineProps({
    customer: Object,
    orders: Array,
    transactions: Array,
});

const logoutForm = useForm({});

const totalOrders = computed(() => props.orders?.length ?? 0);
const pendingOrders = computed(() => props.orders?.filter((o) => o.status === 'pending').length ?? 0);
const totalSpent = computed(() => props.transactions?.reduce((sum, t) => sum + Number(t.price || 0), 0) ?? 0);

const rupiah = (n) => Number(n || 0).toLocaleString('id-ID');

function orderStatus(s) {
    switch (s) {
        case 'paid':
            return { label: 'Lunas', cls: 'bg-green-100 text-green-700' };
        case 'pending':
            return { label: 'Menunggu', cls: 'bg-amber-100 text-amber-700' };
        case 'cancelled':
            return { label: 'Dibatalkan', cls: 'bg-red-100 text-red-700' };
        default:
            return { label: s, cls: 'bg-gray-100 text-gray-600' };
    }
}

function logout() {
    logoutForm.post('/customer/logout');
}
</script>

<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900 transition-colors">
        <div class="mx-auto max-w-3xl px-4 py-6">

            <!-- Header -->
            <div class="mb-6 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Halo, {{ customer.fullname }}</h1>
                    <span class="mt-1 inline-block rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-700">
                        Status: {{ customer.status }}
                    </span>
                </div>
                <div class="flex items-center gap-4">
                    <ThemeToggler />
                    <a href="/customer/profile"
                       class="flex items-center gap-1.5 text-sm font-medium text-blue-600 transition hover:text-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                        <Icon name="customers" />
                        Profil
                    </a>
                    <button @click="logout" :disabled="logoutForm.processing"
                            class="flex items-center gap-1.5 text-sm font-medium text-red-600 transition hover:text-red-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">
                        <Icon name="logout" />
                        Logout
                    </button>
                </div>
            </div>

            <!-- Stat Cards -->
            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-50 text-slate-600 dark:bg-gray-700 dark:text-gray-300">
                            <Icon name="orders" />
                        </span>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Total Order</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ totalOrders }}</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                            <Icon name="vouchers" />
                        </span>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Menunggu</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ pendingOrders }}</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-green-50 text-green-600 dark:bg-green-900/30 dark:text-green-400">
                            <Icon name="payment" />
                        </span>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Total Pengeluaran</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">Rp {{ rupiah(totalSpent) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Orders -->
            <div class="mb-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center gap-2 border-b border-gray-100 px-4 py-3 dark:border-gray-700">
                    <Icon name="orders" class="text-gray-500 dark:text-gray-400" />
                    <h2 class="font-semibold text-gray-900 dark:text-white">Riwayat Order</h2>
                </div>
                <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                    <li v-for="order in orders" :key="order.id" class="flex items-center justify-between gap-3 px-4 py-3 text-sm">
                        <div class="min-w-0">
                            <p class="font-medium text-gray-900 dark:text-white">{{ order.plan?.name_plan }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Rp {{ rupiah(order.price) }}</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium" :class="orderStatus(order.status).cls">
                                {{ orderStatus(order.status).label }}
                            </span>
                            <a :href="`/invoice/${order.invoice_token}`"
                               class="text-xs font-medium text-blue-600 hover:underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                Invoice
                            </a>
                        </div>
                    </li>
                    <li v-if="orders.length === 0" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                        Belum ada order.
                    </li>
                </ul>
            </div>

            <!-- Transactions -->
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center gap-2 border-b border-gray-100 px-4 py-3 dark:border-gray-700">
                    <Icon name="income" class="text-gray-500 dark:text-gray-400" />
                    <h2 class="font-semibold text-gray-900 dark:text-white">Riwayat Transaksi</h2>
                </div>
                <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                    <li v-for="tx in transactions" :key="tx.id" class="flex items-center justify-between gap-3 px-4 py-3 text-sm">
                        <div class="min-w-0">
                            <p class="font-medium text-gray-900 dark:text-white">{{ tx.plan_name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ tx.recharged_on }}</p>
                        </div>
                        <p class="shrink-0 font-medium text-gray-900 dark:text-white">Rp {{ rupiah(tx.price) }}</p>
                    </li>
                    <li v-if="transactions.length === 0" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                        Belum ada transaksi.
                    </li>
                </ul>
            </div>

        </div>
    </div>
</template>
