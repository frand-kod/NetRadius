<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ customers: Array, plans: Array });

const form = useForm({
    customer_id: '',
    plan_id: '',
});

function submit() { form.post('/admin/orders'); }
</script>

<template>
    <AdminLayout>
        <template #title>Buat Order Baru</template>
        <form @submit.prevent="submit" class="max-w-4xl space-y-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8 dark:border-gray-700 dark:bg-gray-800 transition-colors">
            <div>
                <label class="block text-sm font-medium dark:text-gray-300">Customer *</label>
                <select v-model="form.customer_id" required class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25">
                    <option value="">-- Pilih Customer --</option>
                    <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.fullname }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium dark:text-gray-300">Plan *</label>
                <select v-model="form.plan_id" required class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25">
                    <option value="">-- Pilih Plan --</option>
                    <option v-for="p in plans" :key="p.id" :value="p.id">
                        {{ p.name_plan }} — Rp {{ Number(p.price).toLocaleString('id-ID') }}
                    </option>
                </select>
            </div>
            <p class="text-sm text-gray-500">
                Order akan dibuat dengan status <strong>pending</strong>. Invoice + link QR akan dikirim ke WhatsApp customer.
                Admin harus approve manual setelah pembayaran terkonfirmasi.
            </p>
            <button type="submit" :disabled="form.processing"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500/40 disabled:cursor-not-allowed disabled:opacity-60">
                Buat Order
            </button>
        </form>
    </AdminLayout>
</template>

