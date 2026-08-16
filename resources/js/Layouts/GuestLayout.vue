<script setup>
import { usePage } from '@inertiajs/vue3';
import ThemeToggler from '@/Components/ThemeToggler.vue';

const app = usePage().props.app;
</script>

<template>
    <div class="relative flex min-h-screen items-center justify-center bg-gray-100 p-4 dark:bg-gray-900 transition-colors">
        <div class="absolute right-4 top-4">
            <ThemeToggler />
        </div>
        <div class="w-full max-w-sm">
            <div class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm dark:border-gray-700 dark:bg-gray-800 transition-colors">
                <slot />
            </div>
            <footer class="mt-6 text-center text-xs text-gray-500 dark:text-gray-400">
                <p class="font-semibold text-gray-600 dark:text-gray-300">{{ app?.name }} v{{ app?.version }}</p>
                <p class="mt-1">Manajemen billing &amp; hotspot untuk MikroTik + FreeRADIUS</p>
                <p class="mt-1">&copy; {{ new Date().getFullYear() }} {{ app?.name }}. All rights reserved.</p>
            </footer>
        </div>
    </div>
</template>
