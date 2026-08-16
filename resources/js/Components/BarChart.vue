<script setup>
import { computed } from 'vue';

const props = defineProps({
    labels: { type: Array, default: () => [] },
    values: { type: Array, default: () => [] },
    color: { type: String, default: '#f59e0b' },
});

const W = 600, H = 220, PAD_L = 40, PAD_B = 30, PAD_T = 14, PAD_R = 12;
const chartW = computed(() => W - PAD_L - PAD_R);
const chartH = computed(() => H - PAD_T - PAD_B);
const max = computed(() => Math.max(...props.values, 1));
const isEmpty = computed(() => props.values.length === 0 || props.values.every((v) => Number(v) === 0));
const n = computed(() => Math.max(props.values.length, 1));
const slotW = computed(() => chartW.value / n.value);
const barW = computed(() => Math.max(slotW.value * 0.6, 4));

function barX(i) {
    return PAD_L + i * slotW.value + (slotW.value - barW.value) / 2;
}
function barY(v) {
    return PAD_T + chartH.value - (v / max.value) * chartH.value;
}
function barH(v) {
    return (v / max.value) * chartH.value;
}
</script>

<template>
    <div v-if="isEmpty" class="flex h-56 items-center justify-center text-sm text-gray-400 dark:text-gray-500">
        Belum ada data
    </div>
    <svg v-else :viewBox="`0 0 ${W} ${H}`" class="h-auto w-full" role="img">
        <line v-for="i in 4" :key="'g' + i"
              :x1="PAD_L" :x2="W - PAD_R"
              :y1="PAD_T + (chartH / 4) * i" :y2="PAD_T + (chartH / 4) * i"
              stroke="#e5e7eb" class="dark:stroke-gray-700" stroke-width="1" />
        <rect v-for="(v, i) in values" :key="'b' + i"
              :x="barX(i)" :y="barY(v)" :width="barW" :height="barH(v)"
              :fill="color" rx="3" />
        <text v-for="(v, i) in values" :key="'vl' + i"
              :x="barX(i) + barW / 2" :y="barY(v) - 4"
              text-anchor="middle" class="fill-gray-400 dark:fill-gray-500 text-[10px]">{{ v }}</text>
        <text v-for="(l, i) in labels" :key="'lb' + i"
              :x="PAD_L + i * slotW + slotW / 2" :y="H - 8"
              text-anchor="middle" class="fill-gray-400 dark:fill-gray-500 text-[10px]">{{ l }}</text>
    </svg>
</template>
