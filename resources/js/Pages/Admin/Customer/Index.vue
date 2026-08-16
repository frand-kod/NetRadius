<script setup>
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { ref, watch } from 'vue';

const props = defineProps({ customers: Object, filters: Object });

const search = ref(props.filters?.search || '');

watch(search, (val) => {
    router.get('/admin/customers', { search: val }, { preserveState: true, replace: true });
});

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
    router.delete(`/admin/customers/${deletingId.value}`, {
        onFinish: () => { deleting.value = false; closeDelete(); },
    });
}
</script>

<template>
    <AdminLayout>
        <template #title>Customers</template>

        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <Link href="/admin/customers/create"
                  class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500/40">
                + Tambah Customer
            </Link>
            <input v-model="search" type="text" placeholder="Cari username, nama, telepon..."
                   class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25 sm:w-72" />
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 transition-colors">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 transition-colors">
                    <tr>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Username</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Fullname</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Phone</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Last Login</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="c in customers.data" :key="c.id" class="border-b border-gray-100 dark:border-gray-700 transition hover:bg-amber-50/40 dark:hover:bg-gray-700/50 last:border-b-0">
                        <td class="px-4 py-3 align-top font-mono text-gray-700 dark:text-gray-300">{{ c.username }}</td>
                        <td class="px-4 py-3 align-top text-gray-700 dark:text-gray-300">{{ c.fullname }}</td>
                        <td class="px-4 py-3 align-top text-gray-700 dark:text-gray-300">{{ c.phonenumber }}</td>
                        <td class="px-4 py-3 align-top text-gray-700 dark:text-gray-300">
                            <span :class="{
                                'bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400': c.status === 'Active',
                                'bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-400': c.status === 'Banned' || c.status === 'Disabled',
                                'bg-amber-100 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400': c.status === 'Inactive' || c.status === 'Suspended',
                                'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300': c.status === 'Limited',
                            }" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold">{{ c.status }}</span>
                        </td>
                        <td class="px-4 py-3 align-top text-xs text-gray-700 dark:text-gray-300">{{ c.last_login || '-' }}</td>
                        <td class="px-4 py-3 align-top text-right text-gray-700 dark:text-gray-300 space-x-1">
                            <Link :href="`/admin/customers/${c.id}/edit`"
                                  class="text-xs font-medium text-blue-600 dark:text-blue-400 transition hover:underline">Edit</Link>
                            <button @click="openDelete(c.id)" class="text-xs font-medium text-red-600 dark:text-red-400 transition hover:underline">Delete</button>
                        </td>
                    </tr>
                    <tr v-if="customers.data.length === 0">
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Tidak ada customer.</td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>

        <div v-if="customers.links" class="mt-6 flex items-center justify-center gap-2">
            <Link v-for="link in customers.links" :key="link.label" :href="link.url || '#'"
                  v-html="link.label"
                  class="rounded-lg border border-gray-200 px-3 py-1.5 text-sm text-gray-700 transition hover:bg-gray-50"
                  :class="link.active ? 'border-amber-600 bg-amber-600 text-white' : 'border-gray-200 text-gray-700', !link.url && 'pointer-events-none opacity-40'" />
        </div>

        <ConfirmModal :open="showDelete" title="Hapus Customer" message="Yakin ingin menghapus customer ini? Tindakan ini tidak bisa dibatalkan."
                      confirm-text="Hapus" :loading="deleting" @confirm="confirmDelete" @close="closeDelete" />
    </AdminLayout>
</template>

