<script setup>
import { Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, watch } from 'vue';

const props = defineProps({ vouchers: Object, filters: Object, plans: Array });

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');
const showGenerateModal = ref(false);
const selectedIds = ref([]);

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

function destroy(id) {
    if (!confirm('Hapus voucher ini?')) return;
    router.delete(`/admin/vouchers/${id}`);
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

        <div class="mb-4 flex gap-4 flex-wrap items-center">
            <Link href="/admin/vouchers/create" class="bg-amber-600 text-white px-4 py-2 rounded hover:bg-amber-700">
                + Tambah Voucher
            </Link>
            <button @click="showGenerateModal = true"
                    class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                + Generate Vouchers
            </button>
            <button @click="printSelected" :disabled="selectedIds.length === 0"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 disabled:opacity-50">
                Print Selected ({{ selectedIds.length }})
            </button>
            <input v-model="search" type="text" placeholder="Cari kode atau plan..."
                   class="flex-1 max-w-xs rounded border px-3 py-2" />
            <select v-model="statusFilter" class="rounded border px-3 py-2">
                <option value="">Semua</option>
                <option value="0">Unused</option>
                <option value="1">Used</option>
            </select>
        </div>

        <div class="bg-white rounded shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2"><input type="checkbox" @change="toggleSelectAll" /></th>
                        <th class="px-3 py-2 text-left">Code</th>
                        <th class="px-3 py-2 text-left">Plan</th>
                        <th class="px-3 py-2 text-left">Type</th>
                        <th class="px-3 py-2 text-left">Routers</th>
                        <th class="px-3 py-2 text-left">Status</th>
                        <th class="px-3 py-2 text-left">Created</th>
                        <th class="px-3 py-2 text-left">Used Date</th>
                        <th class="px-3 py-2 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="v in vouchers.data" :key="v.id" class="border-t hover:bg-gray-50">
                        <td class="px-3 py-2"><input type="checkbox" :checked="selectedIds.includes(v.id)" @change="toggleSelect(v.id)" /></td>
                        <td class="px-3 py-2 font-mono text-xs">{{ v.code }}</td>
                        <td class="px-3 py-2">{{ v.plan?.name_plan }}</td>
                        <td class="px-3 py-2">{{ v.type }}</td>
                        <td class="px-3 py-2">{{ v.routers }}</td>
                        <td class="px-3 py-2">
                            <span :class="v.status === '0' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'"
                                  class="px-2 py-0.5 rounded-full text-xs font-medium">
                                {{ v.status === '0' ? 'Unused' : 'Used' }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-xs">{{ v.created_at }}</td>
                        <td class="px-3 py-2 text-xs">{{ v.used_date || '-' }}</td>
                        <td class="px-3 py-2 text-right space-x-1">
                            <Link :href="`/admin/vouchers/${v.id}/edit`" class="text-blue-600 hover:underline text-xs">Edit</Link>
                            <button @click="destroy(v.id)" class="text-red-600 hover:underline text-xs">Delete</button>
                        </td>
                    </tr>
                    <tr v-if="vouchers.data.length === 0">
                        <td colspan="9" class="px-4 py-4 text-center text-gray-500">Tidak ada voucher.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="vouchers.links" class="mt-4 flex justify-center gap-2">
            <Link v-for="link in vouchers.links" :key="link.label" :href="link.url || '#'"
                  v-html="link.label"
                  class="px-3 py-1 rounded border text-sm"
                  :class="{ 'bg-gray-200': link.active, 'opacity-50 pointer-events-none': !link.url }" />
        </div>

        <!-- Generate Modal -->
        <div v-if="showGenerateModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                <h2 class="text-lg font-bold mb-4">Generate Vouchers</h2>
                <form @submit.prevent="submitGenerate" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium">Plan *</label>
                        <select v-model="generateForm.id_plan" required class="mt-1 block w-full rounded border px-3 py-2">
                            <option value="">-- Pilih Plan --</option>
                            <option v-for="p in plans" :key="p.id" :value="p.id">{{ p.name_plan }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Jumlah (1-500)</label>
                        <input v-model.number="generateForm.quantity" type="number" min="1" max="500"
                               class="mt-1 block w-full rounded border px-3 py-2" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Panjang Kode (4-20)</label>
                        <input v-model.number="generateForm.code_length" type="number" min="4" max="20"
                               class="mt-1 block w-full rounded border px-3 py-2" />
                    </div>
                    <div class="flex gap-2 justify-end">
                        <button type="button" @click="showGenerateModal = false"
                                class="px-4 py-2 border rounded text-gray-600 hover:bg-gray-50">Batal</button>
                        <button type="submit" :disabled="generateForm.processing"
                                class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 disabled:opacity-50">
                            Generate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
