<script setup>
import { useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const form = useForm({
    username: '',
    password: '',
});

function submit() {
    form.post('/admin/login');
}
</script>

<template>
    <GuestLayout>
        <h1 class="text-xl font-bold mb-4 text-center">Admin Login</h1>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label class="block text-sm font-medium">Username</label>
                <input v-model="form.username" type="text" required autofocus
                       class="mt-1 block w-full rounded border border-gray-300 px-3 py-2" />
                <p v-if="form.errors.username" class="text-red-500 text-sm mt-1">{{ form.errors.username }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium">Password</label>
                <input v-model="form.password" type="password" required
                       class="mt-1 block w-full rounded border border-gray-300 px-3 py-2" />
            </div>
            <button type="submit" :disabled="form.processing"
                    class="w-full bg-amber-600 text-white py-2 rounded hover:bg-amber-700 disabled:opacity-50">
                Login
            </button>
        </form>

        <p class="mt-4 text-center text-sm">
            <a :href="route('admin.forgot-password.show')" class="text-amber-600 hover:underline">
                Lupa password?
            </a>
        </p>
    </GuestLayout>
</template>
