<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    customer: Object,
    orders: Array,
    transactions: Array,
});

const logoutForm = useForm({});

function logout() {
    logoutForm.post('/customer/logout');
}
</script>

<template>
    <div class="min-h-screen bg-gray-100 p-6">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded shadow p-4 mb-4 flex justify-between items-center">
                <div>
                    <h1 class="text-xl font-bold">Halo, {{ customer.fullname }}</h1>
                    <p class="text-sm text-gray-500">Status: {{ customer.status }}</p>
                </div>
                <button @click="logout" :disabled="logoutForm.processing"
                        class="text-red-600 hover:underline text-sm">Logout</button>
            </div>

            <div class="bg-white rounded shadow p-4 mb-4">
                <h2 class="font-semibold mb-2">Riwayat Order</h2>
                <ul class="space-y-2">
                    <li v-for="order in orders" :key="order.id" class="text-sm border-b pb-2">
                        <span class="font-medium">{{ order.plan?.name_plan }}</span>
                        — Rp {{ Number(order.price).toLocaleString('id-ID') }}
                        — <span :class="{
                            'text-amber-600': order.status === 'pending',
                            'text-green-600': order.status === 'paid',
                            'text-red-600': order.status === 'cancelled',
                        }">{{ order.status }}</span>
                        <a :href="`/invoice/${order.invoice_token}`"
                           class="text-blue-600 hover:underline ml-2 text-xs">Lihat Invoice</a>
                    </li>
                    <li v-if="orders.length === 0" class="text-gray-500 text-sm">Belum ada order.</li>
                </ul>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <h2 class="font-semibold mb-2">Riwayat Transaksi</h2>
                <ul class="space-y-2">
                    <li v-for="tx in transactions" :key="tx.id" class="text-sm border-b pb-2">
                        {{ tx.plan_name }}
                        — Rp {{ Number(tx.price).toLocaleString('id-ID') }}
                        — {{ tx.recharged_on }}
                    </li>
                    <li v-if="transactions.length === 0" class="text-gray-500 text-sm">Belum ada transaksi.</li>
                </ul>
            </div>
        </div>
    </div>
</template>

