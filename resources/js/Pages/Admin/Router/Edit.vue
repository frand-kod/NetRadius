<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ router: Object });

const form = useForm({
    name: props.router.name,
    ip_address: props.router.ip_address,
    username: props.router.username,
    password: '',
    description: props.router.description || '',
    coordinates: props.router.coordinates,
    status: props.router.status,
    last_seen: props.router.last_seen || '',
    coverage: props.router.coverage,
    enabled: Boolean(props.router.enabled),
});

function submit() {
    form.put(`/admin/routers/${props.router.id}`);
}
</script>

<template>
    <AdminLayout>
        <template #title>Edit Router: {{ router.name }}</template>
        <form @submit.prevent="submit" class="bg-white rounded shadow p-6 max-w-2xl space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium">Name *</label>
                    <input v-model="form.name" required class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">IP Address *</label>
                    <input v-model="form.ip_address" required class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Username *</label>
                    <input v-model="form.username" required class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Password (kosongkan jika tidak berubah)</label>
                    <input v-model="form.password" type="password" class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Coordinates *</label>
                    <input v-model="form.coordinates" required class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Status</label>
                    <select v-model="form.status" class="mt-1 block w-full rounded border px-3 py-2">
                        <option value="Online">Online</option>
                        <option value="Offline">Offline</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium">Coverage *</label>
                    <input v-model="form.coverage" required class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div class="flex items-center gap-2">
                    <input v-model="form.enabled" type="checkbox" class="rounded" />
                    <label class="text-sm font-medium">Enabled</label>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium">Description</label>
                <textarea v-model="form.description" rows="3" class="mt-1 block w-full rounded border px-3 py-2"></textarea>
            </div>
            <button type="submit" :disabled="form.processing"
                    class="bg-amber-600 text-white px-6 py-2 rounded hover:bg-amber-700 disabled:opacity-50">
                Update
            </button>
        </form>
    </AdminLayout>
</template>
