<script setup>
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, watch } from 'vue';

const props = defineProps({ plans: Object, filters: Object });

const search = ref(props.filters?.search || '');

watch(search, (val) => {
    router.get('/admin/plans', { search: val }, { preserveState: true, replace: true });
});

function destroy(id) {
    if (confirm('Yakin hapus plan ini?')) {
        router.delete(`/admin/plans/${id}`);
    }
}
</script>

<template>
    <AdminLayout>
        <template #title>Plans</template>

        <div class="mb-4 flex gap-4">
            <Link href="/admin/plans/create"
                  class="bg-amber-600 text-white px-4 py-2 rounded hover:bg-amber-700">
                + Tambah Plan
            </Link>
            <input v-model="search" type="text" placeholder="Cari nama plan, type, device..."
                   class="flex-1 max-w-md rounded border px-3 py-2" />
        </div>

        <div class="bg-white rounded shadow overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left">Name</th>
                        <th class="px-3 py-2 text-left">Bandwidth</th>
                        <th class="px-3 py-2 text-left">Type</th>
                        <th class="px-3 py-2 text-left">Price</th>
                        <th class="px-3 py-2 text-left">Validity</th>
                        <th class="px-3 py-2 text-left">Routers</th>
                        <th class="px-3 py-2 text-left">Device</th>
                        <th class="px-3 py-2 text-left">Radius</th>
                        <th class="px-3 py-2 text-left">Enabled</th>
                        <th class="px-3 py-2 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="p in plans.data" :key="p.id" class="border-t hover:bg-gray-50">
                        <td class="px-3 py-2">{{ p.name_plan }}</td>
                        <td class="px-3 py-2">{{ p.bandwidth?.name_bw ?? p.id_bw }}</td>
                        <td class="px-3 py-2">{{ p.type }}</td>
                        <td class="px-3 py-2">Rp {{ Number(p.price).toLocaleString('id-ID') }}</td>
                        <td class="px-3 py-2">{{ p.validity }} {{ p.validity_unit }}</td>
                        <td class="px-3 py-2">{{ p.routers }}</td>
                        <td class="px-3 py-2">{{ p.device }}</td>
                        <td class="px-3 py-2">
                            <span :class="p.is_radius ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'"
                                  class="px-2 py-0.5 rounded-full text-xs font-medium">{{ p.is_radius ? 'Yes' : 'No' }}</span>
                        </td>
                        <td class="px-3 py-2">
                            <span :class="p.enabled ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                                  class="px-2 py-0.5 rounded-full text-xs font-medium">{{ p.enabled ? 'Yes' : 'No' }}</span>
                        </td>
                        <td class="px-3 py-2 text-right space-x-1">
                            <Link :href="`/admin/plans/${p.id}/edit`"
                                  class="text-blue-600 hover:underline text-xs">Edit</Link>
                            <button @click="destroy(p.id)" class="text-red-600 hover:underline text-xs">Delete</button>
                        </td>
                    </tr>
                    <tr v-if="plans.data.length === 0">
                        <td colspan="10" class="px-4 py-4 text-center text-gray-500">Belum ada plan.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="plans.links" class="mt-4 flex justify-center gap-2">
            <Link v-for="link in plans.links" :key="link.label" :href="link.url || '#'"
                  v-html="link.label"
                  class="px-3 py-1 rounded border text-sm"
                  :class="{ 'bg-gray-200': link.active, 'opacity-50 pointer-events-none': !link.url }" />
        </div>
    </AdminLayout>
</template>
