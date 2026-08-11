<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ settings: Object });

const form = useForm({
    telegram_bot: props.settings.telegram_bot || '',
    telegram_target_id: props.settings.telegram_target_id || '',
    alt_wga_server_url: props.settings.alt_wga_server_url || '',
    alt_wga_device_id: props.settings.alt_wga_device_id || '',
    alt_wga_username: props.settings.alt_wga_username || '',
    alt_wga_password: '',
    country_code_phone: props.settings.country_code_phone || '62',
});

function submit() { form.post('/admin/settings/notification'); }
</script>

<template>
    <AdminLayout>
        <template #title>Notification Settings</template>

        <form @submit.prevent="submit" class="bg-white rounded shadow p-6 max-w-lg space-y-4">
            <div>
                <label class="block text-sm font-medium">Telegram Bot Token</label>
                <input v-model="form.telegram_bot" class="mt-1 block w-full rounded border px-3 py-2" />
                <p class="text-xs text-gray-500 mt-1">Token dari @BotFather.</p>
            </div>
            <div>
                <label class="block text-sm font-medium">Telegram Chat ID (admin)</label>
                <input v-model="form.telegram_target_id" class="mt-1 block w-full rounded border px-3 py-2" />
            </div>
            <div>
                <label class="block text-sm font-medium">GOWA Server URL</label>
                <input v-model="form.alt_wga_server_url" class="mt-1 block w-full rounded border px-3 py-2" />
                <p class="text-xs text-gray-500 mt-1">Contoh: http://127.0.0.1:3030</p>
            </div>
            <div>
                <label class="block text-sm font-medium">GOWA Device ID</label>
                <input v-model="form.alt_wga_device_id" class="mt-1 block w-full rounded border px-3 py-2" />
            </div>
            <div>
                <label class="block text-sm font-medium">GOWA Basic Auth Username</label>
                <input v-model="form.alt_wga_username" class="mt-1 block w-full rounded border px-3 py-2" />
            </div>
            <div>
                <label class="block text-sm font-medium">GOWA Basic Auth Password</label>
                <input v-model="form.alt_wga_password" type="password" class="mt-1 block w-full rounded border px-3 py-2" />
            </div>
            <div>
                <label class="block text-sm font-medium">Kode Negara Telepon</label>
                <input v-model="form.country_code_phone" class="mt-1 block w-full rounded border px-3 py-2" />
                <p class="text-xs text-gray-500 mt-1">Dipakai mengganti 0 pada nomor pelanggan. Default: 62.</p>
            </div>
            <button type="submit" :disabled="form.processing"
                    class="bg-amber-600 text-white px-6 py-2 rounded hover:bg-amber-700 disabled:opacity-50">
                Simpan
            </button>
        </form>
    </AdminLayout>
</template>
