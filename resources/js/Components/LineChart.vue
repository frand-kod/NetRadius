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
const max = computed(() => Math.max(...props.values.map(Number), 1));
const isEmpty = computed(() => props.values.length === 0 || props.values.every((v) => Number(v) === 0));

function px(i) {
    if (props.values.length > 1) return PAD_L + i * (chartW.value / (props.values.length - 1));
    return PAD_L + chartW.value / 2;
}
function py(v) {
    return PAD_T + chartH.value - (Number(v) / max.value) * chartH.value;
}

const linePoints = computed(() => props.values.map((v, i) => `${px(i)},${py(v)}`).join(' '));
const areaPoints = computed(() =>
    `${PAD_L},${PAD_T + chartH} ${linePoints.value} ${PAD_L + chartW.value},${PAD_T + chartH}`);
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
        <polygon :points="areaPoints" :fill="color" fill-opacity="0.12" />
        <polyline :points="linePoints" fill="none" :stroke="color" stroke-width="2.5"
                  stroke-linejoin="round" stroke-linecap="round" />
        <circle v-for="(v, i) in values" :key="'c' + i"
                :cx="px(i)" :cy="py(v)" r="3.5" :fill="color"
                class="stroke-white dark:stroke-gray-800" stroke-width="1.5" />
        <text v-for="(l, i) in labels" :key="'lb' + i"
              :x="px(i)" :y="H - 8"
              text-anchor="middle" class="fill-gray-400 dark:fill-gray-500 text-[10px]">{{ l }}</text>
    </svg>
</template>
