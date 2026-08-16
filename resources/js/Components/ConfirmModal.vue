<script setup>
defineProps({
    open: Boolean,
    title: { type: String, default: 'Konfirmasi' },
    message: String,
    confirmText: { type: String, default: 'Hapus' },
    loading: { type: Boolean, default: false },
    danger: { type: Boolean, default: true },
});

const emit = defineEmits(['confirm', 'close']);
</script>

<template>
    <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-sm rounded-xl border border-gray-200 bg-white p-6 shadow-xl dark:border-gray-700 dark:bg-gray-800 transition-colors">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ title }}</h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ message }}</p>
            <div class="mt-6 flex justify-end gap-2">
                <button @click="emit('close')" :disabled="loading"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400/30 disabled:cursor-not-allowed disabled:opacity-60">
                    Batal
                </button>
                <button @click="emit('confirm')" :disabled="loading"
                        :class="danger
                            ? 'inline-flex items-center justify-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500/40 disabled:cursor-not-allowed disabled:opacity-60'
                            : 'inline-flex items-center justify-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500/40 disabled:cursor-not-allowed disabled:opacity-60'">
                    {{ loading ? 'Memproses...' : confirmText }}
                </button>
            </div>
        </div>
    </div>
</template>
