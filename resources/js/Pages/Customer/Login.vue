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
        <h1 class="mb-4 text-center text-xl font-bold text-gray-900 dark:text-gray-100">Login Pelanggan</h1>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Username</label>
                <input v-model="form.username" type="text" required autofocus
                       class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-500" />
                <p v-if="form.errors.username" class="mt-1 text-sm text-red-500">{{ form.errors.username }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                <input v-model="form.password" type="password" required
                       class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-500" />
                <p v-if="form.errors.password" class="mt-1 text-sm text-red-500">{{ form.errors.password }}</p>
            </div>
            <button type="submit" :disabled="form.processing"
                    class="w-full rounded-lg bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500/40 disabled:cursor-not-allowed disabled:opacity-60">
                {{ form.processing ? 'Memproses...' : 'Login' }}
            </button>
        </form>

        <p class="mt-4 text-center text-sm">
            <a href="/customer/forgot-password"
               class="text-amber-600 transition hover:text-amber-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-600 dark:text-amber-400">
                Lupa password?
            </a>
        </p>
    </GuestLayout>
</template>
