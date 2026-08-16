<script setup>
import ThemeToggler from '@/Components/ThemeToggler.vue';
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
        </div>
    </div>
</template>
