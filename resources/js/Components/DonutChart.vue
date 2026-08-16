<script setup>
import { computed } from 'vue';

const props = defineProps({
    segments: { type: Array, default: () => [] }, // [{label, value, color}]
    size: { type: Number, default: 180 },
});

const R = 70, C = 2 * Math.PI * R;
const total = computed(() =>
    props.segments.reduce((s, x) => s + Number(x.value || 0), 0) || 1);

const arcs = computed(() => {
    let acc = 0;
    return props.segments.map((s) => {
        const frac = Number(s.value || 0) / total.value;
        const offset = acc * C;
        acc += frac;
        return { ...s, frac, offset };
    });
});
</script>

<template>
    <div class="flex flex-col items-center gap-3">
        <svg :width="size" :height="size" viewBox="0 0 180 180" class="-rotate-90">
            <circle cx="90" cy="90" :r="R" fill="none" stroke="#e5e7eb" class="dark:stroke-gray-700" stroke-width="24" />
            <circle v-for="(a, i) in arcs" :key="'d' + i"
                    cx="90" cy="90" :r="R" fill="none" :stroke="a.color" stroke-width="24"
                    :stroke-dasharray="`${a.frac * C} ${C}`" :stroke-dashoffset="a.offset" />
        </svg>
        <ul class="flex flex-wrap justify-center gap-x-4 gap-y-1">
            <li v-for="(a, i) in arcs" :key="'l' + i"
                class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400">
                <span class="inline-block h-2.5 w-2.5 rounded-full"
                      :style="{ backgroundColor: a.color }"></span>
                {{ a.label }} ({{ a.value }})
            </li>
        </ul>
    </div>
</template>
