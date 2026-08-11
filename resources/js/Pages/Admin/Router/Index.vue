<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({ routers: Object });
</script>

<template>
    <AdminLayout>
        <template #title>Routers</template>

        <div class="mb-4">
            <Link href="/admin/routers/create" class="bg-amber-600 text-white px-4 py-2 rounded hover:bg-amber-700">
                + Tambah Router
            </Link>
        </div>

        <div class="bg-white rounded shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-medium">Name</th>
                        <th class="px-4 py-2 text-left text-sm font-medium">IP Address</th>
                        <th class="px-4 py-2 text-left text-sm font-medium">Status</th>
                        <th class="px-4 py-2 text-left text-sm font-medium">Coverage</th>
                        <th class="px-4 py-2 text-left text-sm font-medium">Enabled</th>
                        <th class="px-4 py-2 text-right text-sm font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="router in routers.data" :key="router.id" class="border-t hover:bg-gray-50">
                        <td class="px-4 py-2">{{ router.name }}</td>
                        <td class="px-4 py-2">{{ router.ip_address }}</td>
                        <td class="px-4 py-2">
                            <span :class="router.status === 'Online' ? 'text-green-600' : 'text-red-600'">
                                {{ router.status }}
                            </span>
                        </td>
                        <td class="px-4 py-2">{{ router.coverage }}</td>
                        <td class="px-4 py-2">{{ router.enabled ? 'Yes' : 'No' }}</td>
                        <td class="px-4 py-2 text-right">
                            <Link :href="`/admin/routers/${router.id}/edit`"
                                  class="text-blue-600 hover:underline mr-2">Edit</Link>
                            <Link :href="`/admin/routers/${router.id}`" method="delete" as="button"
                                  class="text-red-600 hover:underline">Delete</Link>
                        </td>
                    </tr>
                    <tr v-if="routers.data.length === 0">
                        <td colspan="6" class="px-4 py-4 text-center text-gray-500">Belum ada router.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="routers.links" class="mt-4 flex justify-center gap-2">
            <Link v-for="link in routers.links" :key="link.label" :href="link.url || '#'"
                  v-html="link.label"
                  class="px-3 py-1 rounded border"
                  :class="{ 'bg-gray-200': link.active, 'opacity-50 pointer-events-none': !link.url }" />
        </div>
    </AdminLayout>
</template>
