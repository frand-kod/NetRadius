<script setup>
defineProps({
    size: { type: String, default: 'h-9 w-9' },
    wordmark: { type: Boolean, default: true },
});
</script>

<template>
    <div class="flex items-center gap-2.5">
        <!-- Logo mark: network nodes connected by a RADIUS orbit -->
        <span
            class="flex shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 via-amber-600 to-amber-700 text-white shadow-sm ring-1 ring-black/5 dark:ring-white/10"
            :class="size">
            <svg viewBox="0 0 24 24" fill="none" class="h-3/5 w-3/5" aria-hidden="true">
                <!-- connecting links -->
                <g stroke="white" stroke-width="1.6" stroke-linecap="round" opacity="0.95">
                    <path d="M8.6 5.6 C 11 4.2, 13 4.2, 15.4 5.6" />
                    <path d="M7.2 8.4 C 8.8 12, 10.4 14.6, 11.4 15.8" />
                    <path d="M16.8 8.4 C 15.2 12, 13.6 14.6, 12.6 15.8" />
                    <!-- orbit (RADIUS) -->
                    <path d="M1.8 12 a 10.2 10.2 0 0 1 20.4 0" stroke-width="1.3" stroke-dasharray="2.5 3" opacity="0.6" />
                </g>
                <!-- nodes -->
                <g fill="white">
                    <circle cx="6" cy="5.8" r="2.5" />
                    <circle cx="18" cy="5.8" r="2.5" />
                    <circle cx="12" cy="17" r="2.5" />
                </g>
                <!-- node cores -->
                <g fill="#f59e0b">
                    <circle cx="6" cy="5.8" r="1" />
                    <circle cx="18" cy="5.8" r="1" />
                    <circle cx="12" cy="17" r="1" />
                </g>
            </svg>
        </span>
        <span v-if="wordmark" class="whitespace-nowrap text-lg font-bold tracking-tight text-gray-900 dark:text-white">
            NetRadius
        </span>
    </div>
</template>
