<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ customer: Object });

const form = useForm({
    username: props.customer.username,
    password: '',
    fullname: props.customer.fullname,
    phonenumber: props.customer.phonenumber,
    status: props.customer.status,
});

function submit() { form.put(`/admin/customers/${props.customer.id}`); }
</script>

<template>
    <AdminLayout>
        <template #title>Edit Customer: {{ customer.username }}</template>
        <form @submit.prevent="submit" class="max-w-4xl space-y-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8 dark:border-gray-700 dark:bg-gray-800 transition-colors">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300">Username *</label>
                    <input v-model="form.username" required class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25" />
                    <p v-if="form.errors.username" class="text-red-500 text-xs mt-1">{{ form.errors.username }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300">Password (kosongkan jika tidak berubah)</label>
                    <input v-model="form.password" type="password" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25"
                           placeholder="Biarkan kosong..." />
                </div>
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300">Fullname *</label>
                    <input v-model="form.fullname" required class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25" />
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300">Phone *</label>
                    <input v-model="form.phonenumber" type="tel" required class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25" />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Nomor untuk notifikasi WhatsApp.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300">Status *</label>
                    <select v-model="form.status" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25">
                        <option value="Active">Active</option>
                        <option value="Banned">Banned</option>
                        <option value="Disabled">Disabled</option>
                        <option value="Inactive">Inactive</option>
                        <option value="Limited">Limited</option>
                        <option value="Suspended">Suspended</option>
                    </select>
                </div>
            </div>

            <button type="submit" :disabled="form.processing"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500/40 disabled:cursor-not-allowed disabled:opacity-60">
                Update
            </button>
        </form>
    </AdminLayout>
</template>

