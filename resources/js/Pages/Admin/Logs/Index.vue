<script setup>
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, watch } from 'vue';

const props = defineProps({
    tab: String,
    logs: Object,
    types: Array,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const typeFilter = ref(props.filters?.type || '');
const activeTab = ref(props.tab);

watch([activeTab, search, typeFilter], () => {
    router.get('/admin/logs', {
        tab: activeTab.value,
        search: search.value,
        type: typeFilter.value,
    }, { preserveState: true, replace: true });
});

function truncate(text, len = 80) {
    if (!text) return '';
    return text.length > len ? text.slice(0, len) + '…' : text;
}
</script>

<template>
    <AdminLayout>
        <template #title>Logs</template>

        <!-- Tabs -->
        <div class="mb-4 flex gap-2">
            <button @click="activeTab = 'activity'"
                    class="rounded-lg px-4 py-2 text-sm font-medium transition"
                    :class="activeTab === 'activity' ? 'bg-amber-600 text-white shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'">
                Activity Log
            </button>
            <button @click="activeTab = 'message'"
                    class="rounded-lg px-4 py-2 text-sm font-medium transition"
                    :class="activeTab === 'message' ? 'bg-amber-600 text-white shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'">
                Message Log
            </button>
        </div>

        <!-- Filters -->
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <input v-model="search" type="text" placeholder="Cari..."
                   class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25 sm:w-72" />
            <select v-model="typeFilter" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25">
                <option value="">Semua Jenis</option>
                <option v-for="t in types" :key="t" :value="t">{{ t }}</option>
            </select>
        </div>

        <!-- Activity Log table -->
        <div v-if="activeTab === 'activity'" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 transition-colors">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 transition-colors">
                        <tr>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tanggal</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tipe</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Deskripsi</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">User ID</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="log in logs.data" :key="log.id"
                            class="border-b border-gray-100 dark:border-gray-700 transition hover:bg-amber-50/40 dark:hover:bg-gray-700/50"
                            :class="{ 'border-b-0': log === logs.data[logs.data.length - 1] }">
                            <td class="px-4 py-3 align-top text-xs text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ log.date }}</td>
                            <td class="px-4 py-3 align-top text-gray-700 dark:text-gray-300">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-blue-100 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400">{{ log.type }}</span>
                            </td>
                            <td class="px-4 py-3 align-top text-gray-700 dark:text-gray-300">{{ log.description }}</td>
                            <td class="px-4 py-3 align-top font-mono text-gray-700 dark:text-gray-300">{{ log.userid }}</td>
                            <td class="px-4 py-3 align-top font-mono text-gray-700 dark:text-gray-300">{{ log.ip || '-' }}</td>
                        </tr>
                        <tr v-if="logs.data.length === 0">
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada activity log.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Message Log table -->
        <div v-else class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 transition-colors">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 transition-colors">
                        <tr>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Waktu</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Jenis</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Penerima</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Pesan</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="log in logs.data" :key="log.id"
                            class="border-b border-gray-100 dark:border-gray-700 transition hover:bg-amber-50/40 dark:hover:bg-gray-700/50"
                            :class="{ 'border-b-0': log === logs.data[logs.data.length - 1] }">
                            <td class="px-4 py-3 align-top text-xs text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ log.sent_at }}</td>
                            <td class="px-4 py-3 align-top text-gray-700 dark:text-gray-300">
                                <span :class="log.message_type === 'WhatsApp' ? 'bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400' : 'bg-blue-100 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400'"
                                      class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold">{{ log.message_type }}</span>
                            </td>
                            <td class="px-4 py-3 align-top text-xs text-gray-700 dark:text-gray-300">{{ log.recipient }}</td>
                            <td class="px-4 py-3 align-top text-gray-700 dark:text-gray-300" :title="log.message_content">{{ truncate(log.message_content) }}</td>
                            <td class="px-4 py-3 align-top text-gray-700 dark:text-gray-300">
                                <span :class="log.status === 'Success' ? 'bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400' : 'bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-400'"
                                      class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold" :title="log.error_message">
                                    {{ log.status }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="logs.data.length === 0">
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada message log.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="logs.links" class="mt-6 flex items-center justify-center gap-2">
            <Link v-for="link in logs.links" :key="link.label" :href="link.url || '#'"
                  v-html="link.label"
                  class="rounded-lg border border-gray-200 dark:border-gray-700 px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300 transition hover:bg-gray-50 dark:hover:bg-gray-700"
                  :class="link.active ? 'border-amber-600 bg-amber-600 text-white' : 'border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300', !link.url && 'pointer-events-none opacity-40'" />
        </div>
    </AdminLayout>
</template>

