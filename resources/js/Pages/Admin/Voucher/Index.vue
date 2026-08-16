<script setup>
import { Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { ref, watch } from 'vue';

const props = defineProps({ vouchers: Object, filters: Object, plans: Array });

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');
const showGenerateModal = ref(false);
const selectedIds = ref([]);
const showDelete = ref(false);
const deletingId = ref(null);
const deleting = ref(false);

const generateForm = useForm({
    id_plan: '',
    quantity: 10,
    code_length: 8,
});

watch([search, statusFilter], () => {
    router.get('/admin/vouchers', { search: search.value, status: statusFilter.value },
        { preserveState: true, replace: true });
});

function toggleSelectAll(e) {
    selectedIds.value = e.target.checked ? props.vouchers.data.map(v => v.id) : [];
}

function toggleSelect(id) {
    const idx = selectedIds.value.indexOf(id);
    if (idx > -1) selectedIds.value.splice(idx, 1);
    else selectedIds.value.push(id);
}

function printSelected() {
    if (selectedIds.value.length === 0) return alert('Pilih minimal satu voucher.');
    const ids = selectedIds.value.join(',');
    window.open(`/admin/vouchers/print?ids=${ids}`, '_blank');
}

function openDelete(id) {
    deletingId.value = id;
    showDelete.value = true;
}

function closeDelete() {
    if (deleting.value) return;
    showDelete.value = false;
    deletingId.value = null;
}

function confirmDelete() {
    deleting.value = true;
    router.delete(`/admin/vouchers/${deletingId.value}`, {
        onFinish: () => { deleting.value = false; closeDelete(); },
    });
}

function submitGenerate() {
    generateForm.post('/admin/vouchers/generate', {
        onSuccess: () => showGenerateModal.value = false,
    });
}
</script>

<template>
    <AdminLayout>
        <template #title>Vouchers</template>

        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap gap-2">
                <button @click="showGenerateModal = true"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500/40">
                    + Generate Vouchers
                </button>
                <button @click="printSelected" :disabled="selectedIds.length === 0"
                        class="text-sm font-medium text-gray-600 transition hover:underline disabled:opacity-50">
                    Print Selected ({{ selectedIds.length }})
                </button>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row">
                <input v-model="search" type="text" placeholder="Cari kode atau plan..."
                       class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25 sm:w-72" />
                <select v-model="statusFilter" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25">
                    <option value="">Semua</option>
                    <option value="0">Unused</option>
                    <option value="1">Used</option>
                </select>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 transition-colors">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 transition-colors">
                        <tr>
                            <th class="px-4 py-3"><input type="checkbox" @change="toggleSelectAll" class="rounded border-gray-300 dark:border-gray-600 text-amber-600 focus:ring-amber-500" /></th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Code</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Plan</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Type</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Created</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Used Date</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="v in vouchers.data" :key="v.id"
                            class="border-b border-gray-100 dark:border-gray-700 transition hover:bg-amber-50/40 dark:hover:bg-gray-700/50"
                            :class="{ 'border-b-0': v === vouchers.data[vouchers.data.length - 1] }">
                            <td class="px-4 py-3 align-top"><input type="checkbox" :checked="selectedIds.includes(v.id)" @change="toggleSelect(v.id)" class="rounded border-gray-300 dark:border-gray-600 text-amber-600 focus:ring-amber-500" /></td>
                            <td class="px-4 py-3 align-top font-mono text-xs text-gray-700 dark:text-gray-300">{{ v.code }}</td>
                            <td class="px-4 py-3 align-top text-gray-700 dark:text-gray-300">{{ v.plan?.name_plan }}</td>
                            <td class="px-4 py-3 align-top text-gray-700 dark:text-gray-300">{{ v.type }}</td>
                            <td class="px-4 py-3 align-top text-gray-700 dark:text-gray-300">
                                <StatusBadge :status="v.status === '0' ? 'Unused' : 'Used'" />
                            </td>
                            <td class="px-4 py-3 align-top text-xs text-gray-700 dark:text-gray-300">{{ v.created_at }}</td>
                            <td class="px-4 py-3 align-top text-xs text-gray-700 dark:text-gray-300">{{ v.used_date || '-' }}</td>
                            <td class="px-4 py-3 align-top text-right text-gray-700 dark:text-gray-300 space-x-1">
                                <Link :href="`/admin/vouchers/${v.id}/edit`" class="text-xs font-medium text-blue-600 dark:text-blue-400 transition hover:underline">Edit</Link>
                                <button @click="openDelete(v.id)" class="text-xs font-medium text-red-600 dark:text-red-400 transition hover:underline">Delete</button>
                            </td>
                        </tr>
                        <tr v-if="vouchers.data.length === 0">
                            <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Tidak ada voucher.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="vouchers.links" class="mt-6 flex items-center justify-center gap-2">
            <Link v-for="link in vouchers.links" :key="link.label" :href="link.url || '#'"
                  v-html="link.label"
                  class="rounded-lg border border-gray-200 dark:border-gray-700 px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300 transition hover:bg-gray-50 dark:hover:bg-gray-700"
                  :class="link.active ? 'border-amber-600 bg-amber-600 text-white' : 'border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300', !link.url && 'pointer-events-none opacity-40'" />
        </div>

        <!-- Generate Modal -->
        <div v-if="showGenerateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md rounded-xl border border-gray-200 bg-white p-6 shadow-xl">
                <h2 class="mb-5 text-lg font-bold text-gray-900">Generate Vouchers</h2>
                <form @submit.prevent="submitGenerate" class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Plan *</label>
                        <select v-model="generateForm.id_plan" required class="mt-1.5 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25">
                            <option value="">-- Pilih Plan --</option>
                            <option v-for="p in plans" :key="p.id" :value="p.id">{{ p.name_plan }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Jumlah (1-500)</label>
                        <input v-model.number="generateForm.quantity" type="number" min="1" max="500"
                               class="mt-1.5 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Panjang Kode (4-20)</label>
                        <input v-model.number="generateForm.code_length" type="number" min="4" max="20"
                               class="mt-1.5 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25" />
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="showGenerateModal = false"
                                class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400/30">Batal</button>
                        <button type="submit" :disabled="generateForm.processing"
                                class="inline-flex items-center justify-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500/40 disabled:cursor-not-allowed disabled:opacity-60">
                            Generate
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <ConfirmModal :open="showDelete" title="Hapus Voucher" message="Yakin ingin menghapus voucher ini? Tindakan ini tidak bisa dibatalkan."
                      confirm-text="Hapus" :loading="deleting" @confirm="confirmDelete" @close="closeDelete" />
    </AdminLayout>
</template>

