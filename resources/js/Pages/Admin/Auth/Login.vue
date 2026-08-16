<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import NetRadiusLogo from '@/Components/NetRadiusLogo.vue';

const app = usePage().props.app;
const form = useForm({
    username: '',
    password: '',
});

function submit() {
    form.post('/admin/login');
}
</script>

<template>
    <div class="flex min-h-screen flex-col items-center justify-center bg-gray-950 p-4">
        <div class="w-full max-w-sm">
            <!-- Header / Brand -->
            <div class="mb-10 flex flex-col items-center text-center">
                <NetRadiusLogo class="mb-6" size="h-12 w-12" />
                <p class="text-xs uppercase tracking-widest text-gray-500">Panel Admin</p>
            </div>

            <!-- Login Card -->
            <div class="rounded-xl border border-gray-800 bg-gray-900 p-8 shadow-2xl">
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-gray-400">Username</label>
                        <input v-model="form.username" type="text" required autofocus
                               class="w-full rounded-lg border border-gray-700 bg-gray-950 px-4 py-3 text-sm text-white outline-none transition-all placeholder-gray-600 focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                               placeholder="Masukkan username" />
                        <p v-if="form.errors.username" class="mt-2 text-[10px] text-red-500">{{ form.errors.username }}</p>
                    </div>

                    <div>
                        <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-gray-400">Password</label>
                        <input v-model="form.password" type="password" required
                               class="w-full rounded-lg border border-gray-700 bg-gray-950 px-4 py-3 text-sm text-white outline-none transition-all placeholder-gray-600 focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                               placeholder="••••••••" />
                        <p v-if="form.errors.password" class="mt-2 text-[10px] text-red-500">{{ form.errors.password }}</p>
                    </div>

                    <button type="submit" :disabled="form.processing"
                            class="w-full rounded-lg bg-amber-600 py-3 text-sm font-semibold text-white transition-all duration-200 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500/50 disabled:opacity-50">
                        {{ form.processing ? 'Memproses...' : 'Masuk' }}
                    </button>
                </form>
            </div>

            <!-- Forgot -->
            <div class="mt-8 text-center">
                <a href="/admin-forgot-password"
                   class="text-[10px] uppercase tracking-widest text-gray-600 transition hover:text-amber-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-500">
                    Lupa Password?
                </a>
            </div>

            <!-- Footer -->
            <footer class="mt-8 border-t border-gray-800 pt-6 text-center text-xs text-gray-500">
                <p class="font-semibold text-gray-400">{{ app?.name }} v{{ app?.version }}</p>
                <p class="mt-1">Manajemen billing &amp; hotspot untuk MikroTik + FreeRADIUS</p>
                <p class="mt-1">&copy; {{ new Date().getFullYear() }} {{ app?.name }}. All rights reserved.</p>
            </footer>
        </div>
    </div>
</template>
