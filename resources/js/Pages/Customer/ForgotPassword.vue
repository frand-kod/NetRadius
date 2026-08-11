<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const page = usePage();

const requestForm = useForm({ username: '' });
const resetForm = useForm({ username: '', otp: '' });
</script>

<template>
    <GuestLayout>
        <h1 class="text-xl font-bold mb-4 text-center">Lupa Password</h1>

        <div v-if="page.props.flash?.status" class="bg-green-100 text-green-700 p-3 rounded mb-4">
            {{ page.props.flash.status }}
        </div>
        <div v-if="page.props.flash?.new_password" class="bg-green-100 text-green-700 p-3 rounded mb-4">
            Password baru Anda: <strong>{{ page.props.flash.new_password }}</strong>
            <br />Silakan login dengan password ini.
        </div>

        <form @submit.prevent="() => requestForm.post('/customer/forgot-password/request')" class="space-y-4 mb-6">
            <h2 class="font-semibold">1. Minta Kode Verifikasi</h2>
            <div>
                <label class="block text-sm font-medium">Username</label>
                <input v-model="requestForm.username" type="text" required
                       class="mt-1 block w-full rounded border px-3 py-2" />
            </div>
            <button type="submit" :disabled="requestForm.processing"
                    class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 disabled:opacity-50">
                Kirim Kode via WhatsApp
            </button>
        </form>

        <form @submit.prevent="() => resetForm.post('/customer/forgot-password/reset')" class="space-y-4">
            <h2 class="font-semibold">2. Reset Password</h2>
            <div>
                <label class="block text-sm font-medium">Username</label>
                <input v-model="resetForm.username" type="text" required
                       class="mt-1 block w-full rounded border px-3 py-2" />
            </div>
            <div>
                <label class="block text-sm font-medium">Kode Verifikasi</label>
                <input v-model="resetForm.otp" type="text" required
                       class="mt-1 block w-full rounded border px-3 py-2" />
                <p v-if="resetForm.errors.otp" class="text-red-500 text-sm mt-1">{{ resetForm.errors.otp }}</p>
            </div>
            <button type="submit" :disabled="resetForm.processing"
                    class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700 disabled:opacity-50">
                Reset Password
            </button>
        </form>

        <p class="mt-4 text-center text-sm">
            <a href="/customer/login" class="text-blue-600 hover:underline">Kembali ke login</a>
        </p>
    </GuestLayout>
</template>
