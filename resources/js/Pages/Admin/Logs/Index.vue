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
                    class="px-4 py-2 rounded border"
                    :class="activeTab === 'activity' ? 'bg-amber-600 text-white border-amber-600' : 'bg-white hover:bg-gray-50'">
                Activity Log
            </button>
            <button @click="activeTab = 'message'"
                    class="px-4 py-2 rounded border"
                    :class="activeTab === 'message' ? 'bg-amber-600 text-white border-amber-600' : 'bg-white hover:bg-gray-50'">
                Message Log
            </button>
        </div>

        <!-- Filters -->
        <div class="mb-4 flex gap-4">
            <input v-model="search" type="text" placeholder="Cari..."
                   class="flex-1 max-w-md rounded border px-3 py-2" />
            <select v-model="typeFilter" class="rounded border px-3 py-2">
                <option value="">Semua Jenis</option>
                <option v-for="t in types" :key="t" :value="t">{{ t }}</option>
            </select>
        </div>

        <!-- Activity Log table -->
        <div v-if="activeTab === 'activity'" class="bg-white rounded shadow overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left">Tanggal</th>
                        <th class="px-3 py-2 text-left">Tipe</th>
                        <th class="px-3 py-2 text-left">Deskripsi</th>
                        <th class="px-3 py-2 text-left">User ID</th>
                        <th class="px-3 py-2 text-left">IP</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="log in logs.data" :key="log.id" class="border-t hover:bg-gray-50">
                        <td class="px-3 py-2 text-xs whitespace-nowrap">{{ log.date }}</td>
                        <td class="px-3 py-2">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">{{ log.type }}</span>
                        </td>
                        <td class="px-3 py-2">{{ log.description }}</td>
                        <td class="px-3 py-2 font-mono text-xs">{{ log.userid }}</td>
                        <td class="px-3 py-2 font-mono text-xs">{{ log.ip || '-' }}</td>
                    </tr>
                    <tr v-if="logs.data.length === 0">
                        <td colspan="5" class="px-4 py-4 text-center text-gray-500">Belum ada activity log.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Message Log table -->
        <div v-else class="bg-white rounded shadow overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left">Waktu</th>
                        <th class="px-3 py-2 text-left">Jenis</th>
                        <th class="px-3 py-2 text-left">Penerima</th>
                        <th class="px-3 py-2 text-left">Pesan</th>
                        <th class="px-3 py-2 text-left">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="log in logs.data" :key="log.id" class="border-t hover:bg-gray-50">
                        <td class="px-3 py-2 text-xs whitespace-nowrap">{{ log.sent_at }}</td>
                        <td class="px-3 py-2">
                            <span :class="log.message_type === 'WhatsApp' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'"
                                  class="px-2 py-0.5 rounded-full text-xs font-medium">{{ log.message_type }}</span>
                        </td>
                        <td class="px-3 py-2 text-xs">{{ log.recipient }}</td>
                        <td class="px-3 py-2" :title="log.message_content">{{ truncate(log.message_content) }}</td>
                        <td class="px-3 py-2">
                            <span :class="log.status === 'Success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                                  class="px-2 py-0.5 rounded-full text-xs font-medium" :title="log.error_message">
                                {{ log.status }}
                            </span>
                        </td>
                    </tr>
                    <tr v-if="logs.data.length === 0">
                        <td colspan="5" class="px-4 py-4 text-center text-gray-500">Belum ada message log.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="logs.links" class="mt-4 flex justify-center gap-2">
            <Link v-for="link in logs.links" :key="link.label" :href="link.url || '#'"
                  v-html="link.label"
                  class="px-3 py-1 rounded border text-sm"
                  :class="{ 'bg-gray-200': link.active, 'opacity-50 pointer-events-none': !link.url }" />
        </div>
    </AdminLayout>
</template>
