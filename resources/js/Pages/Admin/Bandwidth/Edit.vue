<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ bandwidth: Object });

const form = useForm({
    name_bw: props.bandwidth.name_bw,
    rate_down: Number(props.bandwidth.rate_down),
    rate_down_unit: props.bandwidth.rate_down_unit,
    rate_up: Number(props.bandwidth.rate_up),
    rate_up_unit: props.bandwidth.rate_up_unit,
    burst: props.bandwidth.burst,
});

function submit() { form.put(`/admin/bandwidths/${props.bandwidth.id}`); }
</script>

<template>
    <AdminLayout>
        <template #title>Edit Bandwidth: {{ bandwidth.name_bw }}</template>
        <form @submit.prevent="submit" class="max-w-4xl space-y-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8 dark:border-gray-700 dark:bg-gray-800 transition-colors">
            <div>
                <label class="block text-sm font-medium dark:text-gray-300">Nama Bandwidth *</label>
                <input v-model="form.name_bw" required
                       class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25" />
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300">Rate Down (download) *</label>
                    <div class="mt-1 flex gap-2">
                        <input v-model.number="form.rate_down" type="number" min="0" required
                               class="w-full flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25" />
                        <select v-model="form.rate_down_unit" class="w-28 shrink-0 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                            <option value="Kbps">Kbps</option>
                            <option value="Mbps">Mbps</option>
                        </select>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Kecepatan download pelanggan.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300">Rate Up (upload) *</label>
                    <div class="mt-1 flex gap-2">
                        <input v-model.number="form.rate_up" type="number" min="0" required
                               class="w-full flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25" />
                        <select v-model="form.rate_up_unit" class="w-28 shrink-0 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                            <option value="Kbps">Kbps</option>
                            <option value="Mbps">Mbps</option>
                        </select>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Kecepatan upload pelanggan.</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium dark:text-gray-300">Burst <span class="font-normal text-gray-400 dark:text-gray-500">(opsional)</span></label>
                <input v-model="form.burst" placeholder="Contoh: 4M/2M 1500k/1000k 16/16"
                       class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25" />
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Kosongkan jika tidak memakai burst. Format Mikrotik: <code class="rounded bg-gray-100 px-1 dark:bg-gray-700">burst-rate burst-threshold burst-time</code>,
                    tiap grup pasangan <code class="rounded bg-gray-100 px-1 dark:bg-gray-700">rx/tx</code> (down/up).
                    Contoh <code class="rounded bg-gray-100 px-1 dark:bg-gray-700">4M/2M 1500k/1000k 16/16</code> = burst-rate down 4M / up 2M,
                    threshold down 1500k / up 1000k, durasi 16 detik. Bila satu nilai per grup (mis. <code class="rounded bg-gray-100 px-1 dark:bg-gray-700">4M</code>) berlaku untuk kedua arah.
                </p>
            </div>

            <button type="submit" :disabled="form.processing"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500/40 disabled:cursor-not-allowed disabled:opacity-60">
                Update
            </button>
        </form>
    </AdminLayout>
</template>
