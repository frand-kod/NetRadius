<script setup>
import Icon from '@/Components/Icon.vue';
import { useTheme } from '@/Composables/useTheme';

const { isDark, toggleTheme } = useTheme();
</script>

<template>
    <button type="button"
            @click="toggleTheme"
            :aria-label="isDark ? 'Aktifkan mode terang' : 'Aktifkan mode gelap'"
            class="flex h-9 w-9 items-center justify-center rounded-md text-gray-600 transition hover:bg-gray-100 hover:text-gray-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-600 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-100">
        <Icon v-if="isDark" name="sun" />
        <Icon v-else name="moon" />
    </button>
</template>
