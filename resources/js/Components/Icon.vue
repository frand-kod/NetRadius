<script setup>
import { computed } from 'vue';

const props = defineProps({
    name: { type: String, required: true },
    size: { type: String, default: 'h-5 w-5' },
});

// Stroke-based icon set (24x24 outline, currentColor) — no external library.
// Each entry is an array of `d` path strings rendered as <path> elements.
const icons = {
    dashboard: ['M3.75 3.75h6v6h-6z', 'M14.25 3.75h6v6h-6z', 'M3.75 14.25h6v6h-6z', 'M14.25 14.25h6v6h-6z'],
    customers: ['M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0z', 'M4.5 20.25a7.5 7.5 0 0 1 15 0'],
    orders: ['M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5', 'M15.75 10.5a3.75 3.75 0 0 1 2.947 1.506l2.186 2.98A2.25 2.25 0 0 1 17.47 19.5H6.53a2.25 2.25 0 0 1-1.413-4.515l2.186-2.98A3.75 3.75 0 0 1 8.25 10.5h7.5z'],
    vouchers: ['M15 5v2m0 4v2m0 4v2M5 5a2 2 0 0 0-2 2v3a2 2 0 1 0 0 4v3a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-3a2 2 0 1 0 0-4V7a2 2 0 0 0-2-2H5z'],
    plans: ['M12 2.25 21 7l-9 4.75L3 7l9-4.75z', 'M3 12l9 4.75L21 12', 'M3 17l9 4.75L21 17'],
    bandwidth: ['M4.5 15a7.5 7.5 0 1 1 15 0', 'M12 15l4-4'],
    income: ['M3 3v18h18', 'M7 15l3-4 4 3 4-6'],
    logs: ['M6.75 3h6.5L19.5 9v11.25a.75.75 0 0 1-.75.75H6.75a.75.75 0 0 1-.75-.75V3.75A.75.75 0 0 1 6.75 3z', 'M9.5 9h4', 'M9.5 13h6', 'M9.5 17h4'],
    documentation: ['M4 4h5a3 3 0 0 1 3 3v13a3 3 0 0 0-3-3H4V4z', 'M20 4h-5a3 3 0 0 0-3 3v13a3 3 0 0 1 3-3h5V4z'],
    settings: ['M3.75 6h16.5', 'M3.75 12h16.5', 'M3.75 18h16.5', 'M6 4.5v3', 'M12 10.5v3', 'M18 16.5v3'],
    payment: ['M2.25 6.75A2.25 2.25 0 0 1 4.5 4.5h15a2.25 2.25 0 0 1 2.25 2.25v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75z', 'M2.25 9h19.5'],
    notification: ['M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0'],
    sun: ['M12 3v2m0 14v2m9-9h-2M5 12H3m14.5-6.5-1.5 1.5M8 8 6.5 6.5m11 11L16.5 16M8 16l-1.5 1.5', 'M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8z'],
    moon: ['M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z'],
    logout: ['M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-7.5A2.25 2.25 0 0 0 3.75 5.25v13.5A2.25 2.25 0 0 0 6 21h7.5a2.25 2.25 0 0 0 2.25-2.25V15', 'M3 12h12', 'M11 8l4 4-4 4'],
};

const paths = computed(() => icons[props.name] ?? []);
</script>

<template>
    <svg class="shrink-0" :class="size" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
        <path v-for="d in paths" :key="d" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="d" />
    </svg>
</template>
