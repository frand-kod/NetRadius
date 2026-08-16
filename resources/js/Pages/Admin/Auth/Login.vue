<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    username: '',
    password: '',
});

function submit() {
    form.post('/admin/login');
}
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-950 p-4">
        <div class="w-full max-w-sm">
            <!-- Header -->
            <div class="mb-10 text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-amber-600/10 border border-amber-600/20 mb-6">
                    <span class="text-xl font-bold text-amber-500">B</span>
                </div>
                <h1 class="text-xl font-semibold text-white tracking-wide">PHPNuxBill</h1>
                <p class="text-xs text-gray-500 mt-2 uppercase tracking-widest">Admin Control Plane</p>
            </div>

            <!-- Login Card -->
            <div class="bg-gray-900 border border-gray-800 p-8 rounded-xl shadow-2xl">
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Username</label>
                        <input v-model="form.username" type="text" required autofocus
                               class="w-full bg-gray-950 border border-gray-700 rounded-lg px-4 py-3 text-sm text-white placeholder-gray-600 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all outline-none" 
                               placeholder="Enter your username" />
                        <p v-if="form.errors.username" class="text-red-500 text-[10px] mt-2">{{ form.errors.username }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Password</label>
                        <input v-model="form.password" type="password" required
                               class="w-full bg-gray-950 border border-gray-700 rounded-lg px-4 py-3 text-sm text-white placeholder-gray-600 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all outline-none"
                               placeholder="••••••••" />
                    </div>

                    <button type="submit" :disabled="form.processing"
                            class="w-full bg-amber-600 hover:bg-amber-700 text-white py-3 rounded-lg text-sm font-semibold transition-all duration-200 disabled:opacity-50">
                        {{ form.processing ? 'Authenticating...' : 'Sign In' }}
                    </button>
                </form>
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center">
                <a href="/admin-forgot-password" class="text-[10px] text-gray-600 hover:text-amber-500 transition uppercase tracking-widest">
                    Forgot Password?
                </a>
            </div>
        </div>
    </div>
</template>
