<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ voucher: Object, plans: Array });

const form = useForm({
    type: 'Hotspot',
    id_plan: props.voucher.id_plan,
    code: props.voucher.code,
    user: props.voucher.user || '',
    status: props.voucher.status,
    used_date: props.voucher.used_date || '',
});

function submit() { form.put(`/admin/vouchers/${props.voucher.id}`); }
</script>

<template>
    <AdminLayout>
        <template #title>Edit Voucher: {{ voucher.code }}</template>
        <form @submit.prevent="submit" class="max-w-4xl space-y-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8 dark:border-gray-700 dark:bg-gray-800 transition-colors">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300">Plan *</label>
                    <select v-model="form.id_plan" required class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25">
                        <option value="">-- Pilih Plan --</option>
                        <option v-for="p in plans" :key="p.id" :value="p.id">{{ p.name_plan }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300">Kode Voucher *</label>
                    <input v-model="form.code" required class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25" />
                    <p v-if="form.errors.code" class="text-red-500 text-xs mt-1">{{ form.errors.code }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300">User</label>
                    <input v-model="form.user" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25" />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Username pelanggan yang memakai voucher ini. Terisi otomatis saat voucher digunakan.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300">Status</label>
                    <select v-model="form.status" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25">
                        <option value="0">Unused</option>
                        <option value="1">Used</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Berubah menjadi "Used" otomatis saat voucher dipakai.</p>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium dark:text-gray-300">Used Date</label>
                <input v-model="form.used_date" type="datetime-local" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25" />
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Waktu voucher terpakai (terisi otomatis).</p>
            </div>

            <button type="submit" :disabled="form.processing"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500/40 disabled:cursor-not-allowed disabled:opacity-60">
                Update
            </button>
        </form>
    </AdminLayout>
</template>
