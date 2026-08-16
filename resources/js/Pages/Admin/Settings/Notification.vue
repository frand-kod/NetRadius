<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';

const props = defineProps({ settings: Object });

const form = useForm({
    telegram_bot: props.settings?.telegram_bot || '',
    telegram_target_id: props.settings?.telegram_target_id || '',
    alt_wga_server_url: props.settings?.alt_wga_server_url || '',
    alt_wga_device_id: props.settings?.alt_wga_device_id || '',
    alt_wga_username: props.settings?.alt_wga_username || '',
    alt_wga_password: props.settings?.alt_wga_password || '',
    country_code_phone: props.settings?.country_code_phone || '62',
});

const activeSection = ref('telegram');

function submit() {
    form.post('/admin/settings/notification');
}
</script>

<template>
    <AdminLayout>
        <template #title>Pengaturan Notifikasi</template>

        <form @submit.prevent="submit" class="max-w-2xl space-y-6">
            
            <!-- Telegram Section -->
            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
                <button type="button" @click="activeSection = activeSection === 'telegram' ? null : 'telegram'" 
                        class="flex w-full items-center justify-between p-5 text-left">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Integrasi Telegram</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Konfigurasi bot untuk notifikasi instan.</p>
                    </div>
                    <span class="text-gray-400">{{ activeSection === 'telegram' ? '▲' : '▼' }}</span>
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
                </div>
            </div>

            <!-- WhatsApp Section -->
            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
                <button type="button" @click="activeSection = activeSection === 'whatsapp' ? null : 'whatsapp'" 
                        class="flex w-full items-center justify-between p-5 text-left">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">WhatsApp Gateway (GOWA)</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Pengaturan server self-hosted untuk notifikasi WA.</p>
                    </div>
                    <span class="text-gray-400">{{ activeSection === 'whatsapp' ? '▲' : '▼' }}</span>
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
