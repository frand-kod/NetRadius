<script setup>
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { ref } from 'vue';

defineProps({ bandwidths: Object });

const showDelete = ref(false);
const deletingId = ref(null);
const deleting = ref(false);

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
    router.delete(`/admin/bandwidths/${deletingId.value}`, {
        onFinish: () => { deleting.value = false; closeDelete(); },
    });
}
</script>

<template>
    <AdminLayout>
        <template #title>Bandwidth</template>

        <div class="mb-4">
            <Link href="/admin/bandwidths/create" class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500/40">
                + Tambah Bandwidth
            </Link>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 transition-colors">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 transition-colors">
                        <tr>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Name</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Rate Down</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Rate Up</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Burst</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="bw in bandwidths.data" :key="bw.id" class="border-b border-gray-100 dark:border-gray-700 transition hover:bg-amber-50/40 dark:hover:bg-gray-700/50 last:border-b-0">
                            <td class="px-4 py-3 align-top text-gray-700 dark:text-gray-300">{{ bw.name_bw }}</td>
                            <td class="px-4 py-3 align-top text-gray-700 dark:text-gray-300">{{ bw.rate_down }} {{ bw.rate_down_unit }}</td>
                            <td class="px-4 py-3 align-top text-gray-700 dark:text-gray-300">{{ bw.rate_up }} {{ bw.rate_up_unit }}</td>
                            <td class="px-4 py-3 align-top text-gray-700 dark:text-gray-300">{{ bw.burst }}</td>
                            <td class="px-4 py-3 align-top text-right text-gray-700 dark:text-gray-300 space-x-1">
                                <Link :href="`/admin/bandwidths/${bw.id}/edit`" class="text-xs font-medium text-blue-600 dark:text-blue-400 transition hover:underline">Edit</Link>
                                <button @click="openDelete(bw.id)" class="text-xs font-medium text-red-600 dark:text-red-400 transition hover:underline">Delete</button>
                            </td>
                        </tr>
                        <tr v-if="bandwidths.data.length === 0">
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada bandwidth.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="bandwidths.links" class="mt-6 flex items-center justify-center gap-2">
            <Link v-for="link in bandwidths.links" :key="link.label" :href="link.url || '#'"
                  v-html="link.label"
                  class="rounded-lg border border-gray-200 dark:border-gray-700 px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300 transition hover:bg-gray-50 dark:hover:bg-gray-700"
                  :class="link.active ? 'border-amber-600 bg-amber-600 text-white' : 'border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300', !link.url && 'pointer-events-none opacity-40'" />
        </div>

        <ConfirmModal :open="showDelete" title="Hapus Bandwidth" message="Yakin ingin menghapus bandwidth ini? Tindakan ini tidak bisa dibatalkan."
                      confirm-text="Hapus" :loading="deleting" @confirm="confirmDelete" @close="closeDelete" />
    </AdminLayout>
</template>

