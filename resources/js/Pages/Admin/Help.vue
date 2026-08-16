<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
    docs: Array,
    title: String,
    content: String,
});
</script>

<template>
    <AdminLayout>
        <template #title>Dokumentasi</template>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 transition-colors">
            <!-- Doc switcher -->
            <div class="flex flex-wrap gap-2 border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <Link v-for="d in docs" :key="d.key" :href="`/admin/help/${d.key}`"
                      class="rounded-lg px-4 py-2 text-sm font-medium transition"
                      :class="d.active
                          ? 'bg-amber-600 text-white shadow-sm'
                          : 'border border-gray-200 bg-white text-gray-600 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700'">
                    {{ d.title }}
                </Link>
            </div>

            <div class="markdown-body mx-auto max-w-3xl px-6 py-8">
                <!-- eslint-disable-next-line vue/no-v-html -->
                <div v-html="content" />
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.markdown-body :deep(h1) {
    font-size: 1.875rem;
    font-weight: 700;
    color: rgb(17 24 39);
    margin-bottom: 1rem;
}
.markdown-body :deep(h2) {
    font-size: 1.25rem;
    font-weight: 700;
    color: rgb(17 24 39);
    margin-top: 2rem;
    margin-bottom: 0.5rem;
    padding-bottom: 0.375rem;
    border-bottom: 1px solid rgb(229 231 235);
}
.markdown-body :deep(h3) {
    font-size: 1.05rem;
    font-weight: 600;
    color: rgb(17 24 39);
    margin-top: 1.5rem;
    margin-bottom: 0.375rem;
}
.markdown-body :deep(p) {
    font-size: 0.875rem;
    line-height: 1.6;
    color: rgb(55 65 81);
    margin-top: 0.5rem;
    margin-bottom: 0.5rem;
}
.markdown-body :deep(ul),
.markdown-body :deep(ol) {
    font-size: 0.875rem;
    line-height: 1.6;
    color: rgb(55 65 81);
    margin-top: 0.375rem;
    margin-bottom: 0.75rem;
    padding-left: 1.5rem;
    list-style: disc;
}
.markdown-body :deep(ol) {
    list-style: decimal;
}
.markdown-body :deep(li) {
    margin-top: 0.25rem;
}
.markdown-body :deep(code) {
    font-family: ui-monospace, monospace;
    font-size: 0.8125rem;
    background: rgb(243 244 246);
    color: rgb(180 83 9);
    padding: 0.125rem 0.375rem;
    border-radius: 0.25rem;
}
.markdown-body :deep(pre) {
    background: rgb(17 24 39);
    color: rgb(243 244 246);
    padding: 0.875rem 1rem;
    border-radius: 0.5rem;
    overflow-x: auto;
    margin-top: 0.5rem;
    margin-bottom: 0.75rem;
    font-size: 0.8125rem;
    line-height: 1.5;
}
.markdown-body :deep(pre code) {
    background: transparent;
    color: inherit;
    padding: 0;
}
.markdown-body :deep(blockquote) {
    border-left: 4px solid rgb(251 191 36);
    background: rgb(255 251 235);
    color: rgb(120 53 15);
    padding: 0.625rem 1rem;
    border-radius: 0 0.375rem 0.375rem 0;
    margin: 0.625rem 0;
    font-size: 0.875rem;
}
.markdown-body :deep(a) {
    color: rgb(217 119 6);
    text-decoration: underline;
}
.markdown-body :deep(hr) {
    border-color: rgb(229 231 235);
    margin: 1.5rem 0;
}
.markdown-body :deep(strong) {
    font-weight: 600;
    color: rgb(17 24 39);
}

@media (prefers-color-scheme: dark) {
    .markdown-body :deep(h1),
    .markdown-body :deep(h2),
    .markdown-body :deep(h3),
    .markdown-body :deep(strong) {
        color: rgb(243 244 246);
    }
    .markdown-body :deep(h2),
    .markdown-body :deep(hr) {
        border-color: rgb(55 65 81);
    }
    .markdown-body :deep(p),
    .markdown-body :deep(ul),
    .markdown-body :deep(ol) {
        color: rgb(209 213 219);
    }
    .markdown-body :deep(code) {
        background: rgb(55 65 81);
        color: rgb(251 191 36);
    }
    .markdown-body :deep(blockquote) {
        background: rgb(69 26 3);
        color: rgb(254 215 170);
        border-color: rgb(217 119 6);
    }
}
</style>
