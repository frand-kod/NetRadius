<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ qrPath: String });

const form = useForm({
    payment_qr: null,
});

function submit() {
    form.post('/admin/settings/payment', {
        forceFormData: true,
    });
}
</script>

<template>
    <AdminLayout>
        <template #title>Payment Settings</template>

        <div class="bg-white rounded shadow p-6 max-w-lg space-y-4">
            <div>
                <h3 class="text-sm font-medium mb-2">QR Saat Ini</h3>
                <img v-if="qrPath" :src="`/storage/${qrPath}`" alt="QR" class="w-48 rounded border" />
                <p v-else class="text-gray-500">Belum ada QR yang diupload.</p>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium">Upload QR Baru</label>
                    <input type="file" accept="image/*" @input="form.payment_qr = $event.target.files[0]"
                           class="mt-1 block w-full" />
                    <p v-if="form.errors.payment_qr" class="text-red-500 text-sm mt-1">{{ form.errors.payment_qr }}</p>
                </div>
                <button type="submit" :disabled="form.processing"
                        class="bg-amber-600 text-white px-6 py-2 rounded hover:bg-amber-700 disabled:opacity-50">
                    Simpan
                </button>
            </form>
        </div>
    </AdminLayout>
</template>
