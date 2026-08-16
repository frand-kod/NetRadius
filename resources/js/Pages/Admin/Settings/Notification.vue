<script setup>
import { useForm } from '@inertiajs/vue3';
import { push } from 'notivue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { onMounted, ref } from 'vue';

const props = defineProps({ settings: Object });

const form = useForm({
    telegram_bot: props.settings?.telegram_bot || '',
    telegram_target_id: props.settings?.telegram_target_id || '',
    alt_wga_server_url: props.settings?.alt_wga_server_url || '',
    alt_wga_device_id: props.settings?.alt_wga_device_id || '',
    alt_wga_username: props.settings?.alt_wga_username || '',
    alt_wga_password: props.settings?.alt_wga_password || '',
    country_code_phone: props.settings?.country_code_phone || '62',
    test_phone: '',
});

const activeSection = ref('telegram');
const testing = ref(null);
const status = ref(null);

function toggle(section) {
    activeSection.value = activeSection.value === section ? null : section;
}

// fetch kecil untuk GET status / POST test (JSON) dengan penanganan CSRF untuk mutasi.
async function api(path, options = {}) {
    const headers = new Headers(options.headers || {});
    headers.set('Accept', 'application/json');
    if (options.body) headers.set('Content-Type', 'application/json');
    if (options.method && options.method.toUpperCase() !== 'GET') {
        const xsrf = document.cookie.split('; ').find((r) => r.startsWith('XSRF-TOKEN='));
        if (xsrf) headers.set('X-XSRF-TOKEN', decodeURIComponent(xsrf.split('=')[1]));
    }
    const res = await fetch(path, { credentials: 'same-origin', ...options, headers });
    return res.json();
}

async function loadStatus() {
    try {
        status.value = await api('/admin/settings/notification/status');
    } catch {
        status.value = null;
    }
}

async function testConnection(channel) {
    testing.value = channel;
    try {
        const res = await api('/admin/settings/notification/test', {
            method: 'POST',
            body: JSON.stringify({
                channel,
                telegram_bot: form.telegram_bot,
                telegram_target_id: form.telegram_target_id,
                alt_wga_server_url: form.alt_wga_server_url,
                alt_wga_device_id: form.alt_wga_device_id,
                alt_wga_username: form.alt_wga_username,
                alt_wga_password: form.alt_wga_password,
                test_phone: form.test_phone,
            }),
        });
        if (res.success) push.success(res.message);
        else push.error(res.message);
    } catch {
        push.error('Gagal menghubungi server.');
    } finally {
        testing.value = null;
    }
}

function statusBadge(s) {
    if (!s) return { label: 'Memuat...', cls: 'bg-gray-100 text-gray-600' };
    switch (s.status) {
        case 'connected':
            return { label: 'Terhubung', cls: 'bg-green-100 text-green-700' };
        case 'server-up':
            return { label: 'Server aktif', cls: 'bg-amber-100 text-amber-700' };
        case 'not-configured':
            return { label: 'Belum dikonfigurasi', cls: 'bg-gray-100 text-gray-500' };
        default:
            return { label: 'Gagal', cls: 'bg-red-100 text-red-700' };
    }
}

function submit() {
    form.post('/admin/settings/notification');
}

onMounted(loadStatus);
</script>

<template>
    <AdminLayout>
        <template #title>Pengaturan Notifikasi</template>

        <form @submit.prevent="submit" class="max-w-2xl space-y-6">

            <!-- Telegram Section -->
            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
                <button type="button" @click="toggle('telegram')"
                        class="flex w-full items-center justify-between gap-3 p-5 text-left">
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="min-w-0">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Integrasi Telegram</h3>
                            <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ status?.telegram?.info || 'Konfigurasi bot untuk notifikasi instan.' }}</p>
                        </div>
                        <span class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-medium" :class="statusBadge(status?.telegram).cls">
                            {{ statusBadge(status?.telegram).label }}
                        </span>
                    </div>
                    <span class="shrink-0 text-gray-400">{{ activeSection === 'telegram' ? '▲' : '▼' }}</span>
                </button>

                <div v-if="activeSection === 'telegram'" class="border-t border-gray-100 dark:border-gray-700 p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1">Bot Token</label>
                        <input v-model="form.telegram_bot" type="password"
                               class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1">Chat ID Tujuan</label>
                        <input v-model="form.telegram_target_id" type="text"
                               class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition" />
                    </div>
                    <button type="button" @click="testConnection('telegram')" :disabled="testing === 'telegram'"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700">
                        {{ testing === 'telegram' ? 'Menguji...' : 'Test Koneksi' }}
                    </button>
                </div>
            </div>

            <!-- WhatsApp Section -->
            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
                <button type="button" @click="toggle('whatsapp')"
                        class="flex w-full items-center justify-between gap-3 p-5 text-left">
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="min-w-0">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">WhatsApp Gateway (GOWA)</h3>
                            <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ status?.whatsapp?.info || 'Pengaturan server self-hosted untuk notifikasi WA.' }}</p>
                        </div>
                        <span class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-medium" :class="statusBadge(status?.whatsapp).cls">
                            {{ statusBadge(status?.whatsapp).label }}
                        </span>
                    </div>
                    <span class="shrink-0 text-gray-400">{{ activeSection === 'whatsapp' ? '▲' : '▼' }}</span>
                </button>

                <div v-if="activeSection === 'whatsapp'" class="border-t border-gray-100 dark:border-gray-700 p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1">Server URL</label>
                        <input v-model="form.alt_wga_server_url" type="text" placeholder="https://api.wa.domain.com"
                               class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1">Device ID</label>
                            <input v-model="form.alt_wga_device_id" type="text"
                                   class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1">Username</label>
                            <input v-model="form.alt_wga_username" type="text"
                                   class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1">Password</label>
                        <input v-model="form.alt_wga_password" type="password"
                               class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1">Nomor Tujuan Uji</label>
                        <input v-model="form.test_phone" type="text" placeholder="08xxxxxxxxxx"
                               class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition" />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Nomor WhatsApp yang akan menerima pesan uji saat menekan tombol Test Koneksi.</p>
                    </div>
                    <button type="button" @click="testConnection('whatsapp')" :disabled="testing === 'whatsapp'"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700">
                        {{ testing === 'whatsapp' ? 'Menguji...' : 'Test Koneksi' }}
                    </button>
                </div>
            </div>

            <!-- Global Settings -->
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1">Kode Negara Default</label>
                <input v-model="form.country_code_phone" type="text" maxlength="5"
                       class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition" />
            </div>

            <button type="submit" :disabled="form.processing"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500/40 disabled:cursor-not-allowed disabled:opacity-60">
                {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
            </button>
        </form>
    </AdminLayout>
</template>
