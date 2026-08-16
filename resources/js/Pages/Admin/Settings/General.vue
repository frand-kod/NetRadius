<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ settings: Object });

const form = useForm({
    company_name: props.settings?.company_name || '',
    company_address: props.settings?.company_address || '',
    company_phone: props.settings?.company_phone || '',
    company_email: props.settings?.company_email || '',
    currency_symbol: props.settings?.currency_symbol || 'Rp',
    currency_code: props.settings?.currency_code || 'IDR',
});

function submit() {
    form.post('/admin/settings/general');
}
</script>

<template>
    <AdminLayout>
        <template #title>Pengaturan Umum</template>

        <form @submit.prevent="submit"
              class="max-w-4xl space-y-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800 transition-colors sm:p-8">

            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Identitas Perusahaan</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Ditampilkan pada invoice dan portal publik.</p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Perusahaan</label>
                    <input v-model="form.company_name" type="text"
                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white dark:bg-gray-700 dark:border-gray-600 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm transition placeholder-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25" />
                    <p v-if="form.errors.company_name" class="mt-1 text-xs text-red-600">{{ form.errors.company_name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Telepon</label>
                    <input v-model="form.company_phone" type="text"
                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white dark:bg-gray-700 dark:border-gray-600 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm transition placeholder-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25" />
                    <p v-if="form.errors.company_phone" class="mt-1 text-xs text-red-600">{{ form.errors.company_phone }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input v-model="form.company_email" type="email"
                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white dark:bg-gray-700 dark:border-gray-600 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm transition placeholder-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25" />
                    <p v-if="form.errors.company_email" class="mt-1 text-xs text-red-600">{{ form.errors.company_email }}</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alamat</label>
                <textarea v-model="form.company_address" rows="3"
                          class="mt-1 block w-full rounded-lg border border-gray-300 bg-white dark:bg-gray-700 dark:border-gray-600 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm transition placeholder-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25"></textarea>
                <p v-if="form.errors.company_address" class="mt-1 text-xs text-red-600">{{ form.errors.company_address }}</p>
            </div>

            <div class="border-t border-gray-100 dark:border-gray-700 pt-5">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Mata Uang</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Digunakan untuk format harga di seluruh aplikasi.</p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Simbol (contoh: Rp)</label>
                    <input v-model="form.currency_symbol" type="text" maxlength="10"
                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white dark:bg-gray-700 dark:border-gray-600 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm transition placeholder-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25" />
                    <p v-if="form.errors.currency_symbol" class="mt-1 text-xs text-red-600">{{ form.errors.currency_symbol }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kode (contoh: IDR)</label>
                    <input v-model="form.currency_code" type="text" maxlength="10"
                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white dark:bg-gray-700 dark:border-gray-600 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm transition placeholder-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25" />
                    <p v-if="form.errors.currency_code" class="mt-1 text-xs text-red-600">{{ form.errors.currency_code }}</p>
                </div>
            </div>

            <button type="submit" :disabled="form.processing"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500/40 disabled:cursor-not-allowed disabled:opacity-60">
                {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
            </button>
        </form>
    </AdminLayout>
</template>
