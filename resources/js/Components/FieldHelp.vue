<script setup>
defineProps({ text: String });
</script>

<template>
    <span class="group relative ml-1 inline-flex align-middle">
        <span class="inline-flex h-4 w-4 cursor-help items-center justify-center rounded-full bg-gray-200 text-[10px] font-bold leading-none text-gray-600 dark:bg-gray-700 dark:text-gray-300">?</span>
        <span class="pointer-events-none absolute left-1/2 top-5 z-20 w-56 -translate-x-1/2 rounded-lg border border-gray-200 bg-white p-2 text-xs font-normal leading-snug text-gray-700 opacity-0 shadow-lg transition-opacity duration-150 group-hover:opacity-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
            {{ text }}
        </span>
    </span>
</template>
