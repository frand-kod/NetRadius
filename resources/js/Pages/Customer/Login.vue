<script setup>
import { useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const form = useForm({
    username: '',
    password: '',
});

function submit() {
    form.post('/customer/login');
}
</script>

<template>
    <GuestLayout>
        <h1 class="text-xl font-bold mb-4 text-center dark:text-gray-100">Login Pelanggan</h1>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label class="block text-sm font-medium dark:text-gray-300">Username</label>
                <input v-model="form.username" type="text" required autofocus
                       class="mt-1 block w-full rounded border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-500" />
                <p v-if="form.errors.username" class="text-red-500 text-sm mt-1">{{ form.errors.username }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium dark:text-gray-300">Password</label>
                <input v-model="form.password" type="password" required
                       class="mt-1 block w-full rounded border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-500" />
            </div>
            <button type="submit" :disabled="form.processing"
                    class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 disabled:opacity-50">
                Login
            </button>
        </form>

        <p class="mt-4 text-center text-sm">
            <a href="/customer/forgot-password" class="text-blue-600 hover:underline dark:text-blue-400">
                Lupa password?
            </a>
        </p>
    </GuestLayout>
</template>

