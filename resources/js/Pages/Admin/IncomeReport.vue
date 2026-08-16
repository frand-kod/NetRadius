<script setup>
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    rows: Array,
    total: Number,
    from: String,
    to: String,
});

const fromDate = ref(props.from);
const toDate = ref(props.to);

function generate() {
    router.get('/admin/income-report', { from: fromDate.value, to: toDate.value },
        { preserveState: true, replace: true });
}

function formatCurrency(amount) {
    return 'Rp ' + Number(amount).toLocaleString('id-ID');
}
</script>

<template>
    <AdminLayout>
        <template #title>Income Report</template>

        <div class="mb-6 flex items-end gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800 transition-colors">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Dari Tanggal</label>
                <input v-model="fromDate" type="date" class="mt-1 rounded-lg border border-gray-300 bg-white dark:bg-gray-700 dark:border-gray-600 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sampai Tanggal</label>
                <input v-model="toDate" type="date" class="mt-1 rounded-lg border border-gray-300 bg-white dark:bg-gray-700 dark:border-gray-600 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25" />
            </div>
            <button @click="generate"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500/40">
                Tampilkan
            </button>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 transition-colors">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 transition-colors">
                        <tr>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tanggal</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 text-right">Jumlah Transaksi</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in rows" :key="row.date" class="border-b border-gray-100 dark:border-gray-700 transition hover:bg-amber-50/40 dark:hover:bg-gray-700/50 last:border-b-0">
                            <td class="px-4 py-3 align-top text-gray-700 dark:text-gray-300">{{ row.date }}</td>
                            <td class="px-4 py-3 align-top text-right text-gray-700 dark:text-gray-300">{{ row.count }}</td>
                            <td class="px-4 py-3 align-top text-right font-medium text-gray-700 dark:text-gray-300">{{ formatCurrency(row.total) }}</td>
                        </tr>
                        <tr v-if="rows.length === 0">
                            <td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                Tidak ada transaksi pada periode ini.
                            </td>
                        </tr>
                    </tbody>
                    <tfoot v-if="rows.length > 0" class="bg-gray-100 dark:bg-gray-700 font-bold text-gray-900 dark:text-gray-100">
                        <tr>
                            <td class="px-4 py-3">TOTAL</td>
                            <td class="px-4 py-3 text-right">{{ rows.reduce((s, r) => s + Number(r.count), 0) }}</td>
                            <td class="px-4 py-3 text-right">{{ formatCurrency(total) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>

