<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ qrPath: String, paymentInstructions: String, invoiceTemplate: String, invoiceVars: Array });

const form = useForm({
    payment_qr: null,
    payment_instructions: props.paymentInstructions || '',
    invoice_template: props.invoiceTemplate || '',
});

function submit() {
    form.post('/admin/settings/payment', { forceFormData: true });
}
</script>

<template>
    <AdminLayout>
        <template #title>Pengaturan Pembayaran</template>

        <form @submit.prevent="submit"
              class="max-w-4xl space-y-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800 transition-colors sm:p-8">

            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">QR Pembayaran</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">QRIS / QR pembayaran yang tampil pada invoice. Kosongkan field untuk membiarkan QR lama.</p>
            </div>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                <img v-if="qrPath" :src="`/storage/${qrPath}`" alt="QR"
                     class="w-40 shrink-0 rounded-lg border border-gray-200 dark:border-gray-700 object-cover" />
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Upload QR Baru</label>
                    <input type="file" accept="image/*" @input="form.payment_qr = $event.target.files[0]"
                           class="mt-1 block w-full text-sm text-gray-700 dark:text-gray-300" />
                    <p v-if="form.errors.payment_qr" class="mt-1 text-xs text-red-600">{{ form.errors.payment_qr }}</p>
                    <p v-if="!qrPath" class="mt-2 text-xs text-gray-500 dark:text-gray-400">Belum ada QR. Silakan upload.</p>
                </div>
            </div>

            <div class="border-t border-gray-100 dark:border-gray-700 pt-5">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Instruksi Pembayaran</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Teks yang ditampilkan di halaman invoice untuk pelanggan.</p>
            </div>

            <div>
                <textarea v-model="form.payment_instructions" rows="4"
                          class="mt-1 block w-full rounded-lg border border-gray-300 bg-white dark:bg-gray-700 dark:border-gray-600 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm transition placeholder-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25"
                          placeholder="Contoh: Transfer ke rekening 1234 an. Nama, lalu konfirmasi ke WA 0812..."></textarea>
                <p v-if="form.errors.payment_instructions" class="mt-1 text-xs text-red-600">{{ form.errors.payment_instructions }}</p>
            </div>

            <!-- Invoice template -->
            <div class="border-t border-gray-100 dark:border-gray-700 pt-5">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Template Invoice</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Susun tampilan invoice publik (markdown). Gunakan variabel untuk menyisipkan data
                    per order; blok <code class="rounded bg-amber-50 px-1 py-0.5 font-mono text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">{payment_section}</code>
                    diisi otomatis sesuai status (QR + instruksi jika pending, konfirmasi jika paid).
                </p>
            </div>

            <div>
                <textarea v-model="form.invoice_template" rows="10"
                          class="mt-1 block w-full rounded-lg border border-gray-300 bg-white dark:bg-gray-700 dark:border-gray-600 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm transition placeholder-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25 font-mono text-xs"></textarea>
                <p v-if="form.errors.invoice_template" class="mt-1 text-xs text-red-600">{{ form.errors.invoice_template }}</p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900/40">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Variabel tersedia</p>
                <ul class="grid grid-cols-1 gap-1.5 sm:grid-cols-2">
                    <li v-for="v in invoiceVars" :key="v.var" class="text-xs">
                        <code class="rounded bg-amber-50 px-1.5 py-0.5 font-mono text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">{{ '{' + v.var + '}' }}</code>
                        <span class="ml-1 text-gray-600 dark:text-gray-400">{{ v.desc }}</span>
                    </li>
                </ul>
            </div>

            <button type="submit" :disabled="form.processing"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500/40 disabled:cursor-not-allowed disabled:opacity-60">
                {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
            </button>
        </form>
    </AdminLayout>
</template>
