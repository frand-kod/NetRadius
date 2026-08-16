<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const page = usePage();

const requestForm = useForm({ username: '' });
const resetForm = useForm({ username: '', otp: '' });

const inputClass = 'mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-500';
</script>

<template>
    <GuestLayout>
        <h1 class="mb-4 text-center text-xl font-bold text-gray-900 dark:text-gray-100">Lupa Password - Admin</h1>

        <div v-if="page.props.flash?.status" class="mb-4 rounded-lg bg-green-100 p-3 text-sm text-green-700 dark:border dark:border-green-800 dark:bg-green-900/30 dark:text-green-300">
            {{ page.props.flash.status }}
        </div>
        <div v-if="page.props.flash?.new_password" class="mb-4 rounded-lg bg-green-100 p-3 text-sm text-green-700 dark:border dark:border-green-800 dark:bg-green-900/30 dark:text-green-300">
            Password baru Anda: <strong>{{ page.props.flash.new_password }}</strong>
            <br />Silakan login dengan password ini.
        </div>

        <form @submit.prevent="() => requestForm.post('/admin-forgot-password/request')" class="mb-6 space-y-4">
            <h2 class="font-semibold text-gray-900 dark:text-gray-100">1. Minta Kode Verifikasi</h2>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Username</label>
                <input v-model="requestForm.username" type="text" required :class="inputClass" />
            </div>
            <button type="submit" :disabled="requestForm.processing"
                    class="w-full rounded-lg bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500/40 disabled:cursor-not-allowed disabled:opacity-60">
                {{ requestForm.processing ? 'Mengirim...' : 'Kirim Kode via WhatsApp' }}
            </button>
        </form>

        <form @submit.prevent="() => resetForm.post('/admin-forgot-password/reset')" class="space-y-4">
            <h2 class="font-semibold text-gray-900 dark:text-gray-100">2. Reset Password</h2>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Username</label>
                <input v-model="resetForm.username" type="text" required :class="inputClass" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kode Verifikasi</label>
                <input v-model="resetForm.otp" type="text" required :class="inputClass" />
                <p v-if="resetForm.errors.otp" class="mt-1 text-sm text-red-500">{{ resetForm.errors.otp }}</p>
            </div>
            <button type="submit" :disabled="resetForm.processing"
                    class="w-full rounded-lg bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500/40 disabled:cursor-not-allowed disabled:opacity-60">
                {{ resetForm.processing ? 'Memproses...' : 'Reset Password' }}
            </button>
        </form>

        <p class="mt-4 text-center text-sm">
            <a href="/admin/login"
               class="text-amber-600 transition hover:text-amber-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-600 dark:text-amber-400">
                Kembali ke login
            </a>
        </p>
    </GuestLayout>
</template>
