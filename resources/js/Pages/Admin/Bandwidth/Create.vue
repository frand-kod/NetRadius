<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const form = useForm({
    name_bw: '',
    rate_down: 0,
    rate_down_unit: 'Kbps',
    rate_up: 0,
    rate_up_unit: 'Kbps',
    burst: '',
});

function submit() { form.post('/admin/bandwidths'); }
</script>

<template>
    <AdminLayout>
        <template #title>Add Bandwidth</template>
        <form @submit.prevent="submit" class="bg-white rounded shadow p-6 max-w-lg space-y-4">
            <div>
                <label class="block text-sm font-medium">Name *</label>
                <input v-model="form.name_bw" required class="mt-1 block w-full rounded border px-3 py-2" />
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium">Rate Down *</label>
                    <input v-model.number="form.rate_down" type="number" min="0" required class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Unit</label>
                    <select v-model="form.rate_down_unit" class="mt-1 block w-full rounded border px-3 py-2">
                        <option value="Kbps">Kbps</option>
                        <option value="Mbps">Mbps</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium">Rate Up *</label>
                    <input v-model.number="form.rate_up" type="number" min="0" required class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Unit</label>
                    <select v-model="form.rate_up_unit" class="mt-1 block w-full rounded border px-3 py-2">
                        <option value="Kbps">Kbps</option>
                        <option value="Mbps">Mbps</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium">Burst *</label>
                <input v-model="form.burst" required class="mt-1 block w-full rounded border px-3 py-2" />
            </div>
            <button type="submit" :disabled="form.processing"
                    class="bg-amber-600 text-white px-6 py-2 rounded hover:bg-amber-700 disabled:opacity-50">
                Simpan
            </button>
        </form>
    </AdminLayout>
</template>
