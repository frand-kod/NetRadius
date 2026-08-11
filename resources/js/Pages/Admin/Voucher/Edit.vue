<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ voucher: Object, plans: Array });

const form = useForm({
    type: props.voucher.type,
    routers: props.voucher.routers,
    id_plan: props.voucher.id_plan,
    code: props.voucher.code,
    user: props.voucher.user || '',
    status: props.voucher.status,
    used_date: props.voucher.used_date || '',
    generated_by: Number(props.voucher.generated_by),
});

function submit() { form.put(`/admin/vouchers/${props.voucher.id}`); }
</script>

<template>
    <AdminLayout>
        <template #title>Edit Voucher: {{ voucher.code }}</template>
        <form @submit.prevent="submit" class="bg-white rounded shadow p-6 max-w-lg space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium">Type *</label>
                    <select v-model="form.type" class="mt-1 block w-full rounded border px-3 py-2">
                        <option value="Hotspot">Hotspot</option>
                        <option value="PPPOE">PPPOE</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium">Routers *</label>
                    <input v-model="form.routers" required class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Plan *</label>
                    <select v-model="form.id_plan" required class="mt-1 block w-full rounded border px-3 py-2">
                        <option value="">-- Pilih Plan --</option>
                        <option v-for="p in plans" :key="p.id" :value="p.id">{{ p.name_plan }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium">Code *</label>
                    <input v-model="form.code" required class="mt-1 block w-full rounded border px-3 py-2" />
                    <p v-if="form.errors.code" class="text-red-500 text-xs mt-1">{{ form.errors.code }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium">User</label>
                    <input v-model="form.user" class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Status</label>
                    <select v-model="form.status" class="mt-1 block w-full rounded border px-3 py-2">
                        <option value="0">Unused</option>
                        <option value="1">Used</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium">Used Date</label>
                <input v-model="form.used_date" type="datetime-local" class="mt-1 block w-full rounded border px-3 py-2" />
            </div>
            <button type="submit" :disabled="form.processing"
                    class="bg-amber-600 text-white px-6 py-2 rounded hover:bg-amber-700 disabled:opacity-50">
                Update
            </button>
        </form>
    </AdminLayout>
</template>
