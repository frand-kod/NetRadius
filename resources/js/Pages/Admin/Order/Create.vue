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
        <form @submit.prevent="submit" class="bg-white rounded shadow p-6 max-w-lg space-y-4">
            <div>
                <label class="block text-sm font-medium">Customer *</label>
                <select v-model="form.customer_id" required class="mt-1 block w-full rounded border px-3 py-2">
                    <option value="">-- Pilih Customer --</option>
                    <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.fullname }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium">Plan *</label>
                <select v-model="form.plan_id" required class="mt-1 block w-full rounded border px-3 py-2">
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
                    class="bg-amber-600 text-white px-6 py-2 rounded hover:bg-amber-700 disabled:opacity-50">
                Buat Order
            </button>
        </form>
    </AdminLayout>
</template>
