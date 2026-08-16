<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const page = usePage();

const requestForm = useForm({ username: '' });
const resetForm = useForm({ username: '', otp: '' });
</script>

<template>
    <GuestLayout>
        <h1 class="text-xl font-bold mb-4 text-center dark:text-gray-100">Lupa Password - Admin</h1>

        <!-- Status messages -->
        <div v-if="page.props.flash?.status" class="bg-green-100 text-green-700 p-3 rounded mb-4 dark:bg-green-900/30 dark:text-green-300 dark:border dark:border-green-800">
            {{ page.props.flash.status }}
        </div>
        <div v-if="page.props.flash?.new_password" class="bg-green-100 text-green-700 p-3 rounded mb-4 dark:bg-green-900/30 dark:text-green-300 dark:border dark:border-green-800">
            Password baru Anda: <strong>{{ page.props.flash.new_password }}</strong>
            <br />Silakan login dengan password ini.
        </div>

        <!-- Step 1: Request OTP -->
        <form @submit.prevent="() => requestForm.post('/admin-forgot-password/request')" class="space-y-4 mb-6">
            <h2 class="font-semibold dark:text-gray-100">1. Minta Kode Verifikasi</h2>
            <div>
                <label class="block text-sm font-medium dark:text-gray-300">Username</label>
                <input v-model="requestForm.username" type="text" required
                       class="mt-1 block w-full rounded border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-500" />
            </div>
            <button type="submit" :disabled="requestForm.processing"
                    class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 disabled:opacity-50">
                Kirim Kode via WhatsApp
            </button>
        </form>

        <!-- Step 2: Reset Password -->
        <form @submit.prevent="() => resetForm.post('/admin-forgot-password/reset')" class="space-y-4">
            <h2 class="font-semibold dark:text-gray-100">2. Reset Password</h2>
            <div>
                <label class="block text-sm font-medium dark:text-gray-300">Username</label>
                <input v-model="resetForm.username" type="text" required
                       class="mt-1 block w-full rounded border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-500" />
            </div>
            <div>
                <label class="block text-sm font-medium dark:text-gray-300">Kode Verifikasi</label>
                <input v-model="resetForm.otp" type="text" required
                       class="mt-1 block w-full rounded border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-500" />
                <p v-if="resetForm.errors.otp" class="text-red-500 text-sm mt-1">{{ resetForm.errors.otp }}</p>
            </div>
            <button type="submit" :disabled="resetForm.processing"
                    class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700 disabled:opacity-50">
                Reset Password
            </button>
        </form>

        <p class="mt-4 text-center text-sm">
            <a href="/admin/login" class="text-amber-600 hover:underline">Kembali ke login</a>
        </p>
    </GuestLayout>
</template>

