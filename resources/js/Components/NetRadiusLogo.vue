<script setup>
defineProps({
    size: { type: String, default: 'h-9 w-9' },
    wordmark: { type: Boolean, default: true },
});
</script>

<template>
    <div class="flex items-center gap-2.5">
        <!-- Logo mark: network nodes + RADIUS orbit -->
        <span class="flex shrink-0 items-center justify-center rounded-lg bg-amber-600 text-white shadow-sm" :class="size">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-2/3 w-2/3" aria-hidden="true">
                <circle cx="5.5" cy="6" r="2.2" />
                <circle cx="18.5" cy="6" r="2.2" />
                <circle cx="12" cy="18" r="2.2" />
                <path d="M7.4 5.4l8.8.8" stroke-linecap="round" />
                <path d="M7 7.6L10.4 16" stroke-linecap="round" />
                <path d="M17 7.6L13.6 16" stroke-linecap="round" />
                <path d="M3.2 10.5a9.6 9.6 0 0 1 4-3" stroke-linecap="round" stroke-dasharray="1.5 2" opacity="0.7" />
            </svg>
        </span>
        <span v-if="wordmark" class="whitespace-nowrap text-lg font-bold tracking-tight text-gray-900 dark:text-white">
            NetRadius
        </span>
    </div>
</template>
