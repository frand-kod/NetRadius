<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({ bandwidths: Object });
</script>

<template>
    <AdminLayout>
        <template #title>Bandwidth</template>

        <div class="mb-4">
            <Link href="/admin/bandwidths/create" class="bg-amber-600 text-white px-4 py-2 rounded hover:bg-amber-700">
                + Tambah Bandwidth
            </Link>
        </div>

        <div class="bg-white rounded shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-medium">Name</th>
                        <th class="px-4 py-2 text-left text-sm font-medium">Rate Down</th>
                        <th class="px-4 py-2 text-left text-sm font-medium">Rate Up</th>
                        <th class="px-4 py-2 text-left text-sm font-medium">Burst</th>
                        <th class="px-4 py-2 text-right text-sm font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="bw in bandwidths.data" :key="bw.id" class="border-t hover:bg-gray-50">
                        <td class="px-4 py-2">{{ bw.name_bw }}</td>
                        <td class="px-4 py-2">{{ bw.rate_down }} {{ bw.rate_down_unit }}</td>
                        <td class="px-4 py-2">{{ bw.rate_up }} {{ bw.rate_up_unit }}</td>
                        <td class="px-4 py-2">{{ bw.burst }}</td>
                        <td class="px-4 py-2 text-right">
                            <Link :href="`/admin/bandwidths/${bw.id}/edit`" class="text-blue-600 hover:underline mr-2">Edit</Link>
                            <Link :href="`/admin/bandwidths/${bw.id}`" method="delete" as="button" class="text-red-600 hover:underline">Delete</Link>
                        </td>
                    </tr>
                    <tr v-if="bandwidths.data.length === 0">
                        <td colspan="5" class="px-4 py-4 text-center text-gray-500">Belum ada bandwidth.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="bandwidths.links" class="mt-4 flex justify-center gap-2">
            <Link v-for="link in bandwidths.links" :key="link.label" :href="link.url || '#'"
                  v-html="link.label"
                  class="px-3 py-1 rounded border"
                  :class="{ 'bg-gray-200': link.active, 'opacity-50 pointer-events-none': !link.url }" />
        </div>
    </AdminLayout>
</template>
