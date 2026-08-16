<script setup>
import { useTheme } from '@/Composables/useTheme';

const { isDark, toggleTheme } = useTheme();
</script>

<template>
    <button @click="toggleTheme"
            class="rounded-lg p-2 text-gray-600 transition hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-100">
        <span v-if="isDark">☀️</span>
        <span v-else>🌙</span>
    </button>
</template>
