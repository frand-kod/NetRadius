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

<style>
/* Markdown content styling. Rendered server-side via Str::markdown() and injected
   as raw HTML (v-html), so these selectors target the generated elements directly.
   Dark mode follows the app's class-based .dark toggle, not the OS media query. */
.markdown-body {
    font-size: 0.875rem;
    line-height: 1.7;
    color: rgb(55 65 81);
}
.markdown-body h1,
.markdown-body h2,
.markdown-body h3,
.markdown-body h4,
.markdown-body strong {
    color: rgb(17 24 39);
}
.markdown-body h1 {
    font-size: 1.875rem;
    font-weight: 700;
    margin-bottom: 1rem;
}
.markdown-body h2 {
    font-size: 1.25rem;
    font-weight: 700;
    margin-top: 2rem;
    margin-bottom: 0.5rem;
    padding-bottom: 0.375rem;
    border-bottom: 1px solid rgb(229 231 235);
}
.markdown-body h3 {
    font-size: 1.05rem;
    font-weight: 600;
    margin-top: 1.5rem;
    margin-bottom: 0.375rem;
}
.markdown-body h4 {
    font-size: 0.95rem;
    font-weight: 600;
    margin-top: 1.25rem;
    margin-bottom: 0.375rem;
}
.markdown-body p {
    margin: 0.5rem 0;
}
.markdown-body ul,
.markdown-body ol {
    margin: 0.375rem 0 0.75rem;
    padding-left: 1.5rem;
}
.markdown-body ul {
    list-style: disc;
}
.markdown-body ol {
    list-style: decimal;
}
.markdown-body li {
    margin-top: 0.25rem;
}
.markdown-body a {
    color: rgb(217 119 6);
    text-decoration: underline;
}
.markdown-body code {
    font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
    font-size: 0.8125rem;
    background: rgb(243 244 246);
    color: rgb(180 83 9);
    padding: 0.125rem 0.375rem;
    border-radius: 0.25rem;
}
.markdown-body pre {
    background: rgb(17 24 39);
    color: rgb(243 244 246);
    padding: 0.875rem 1rem;
    border-radius: 0.5rem;
    overflow-x: auto;
    margin: 0.75rem 0;
    font-size: 0.8125rem;
    line-height: 1.6;
}
.markdown-body pre code {
    background: transparent;
    color: inherit;
    padding: 0;
}
.markdown-body blockquote {
    border-left: 4px solid rgb(251 191 36);
    background: rgb(255 251 235);
    color: rgb(120 53 15);
    padding: 0.625rem 1rem;
    border-radius: 0 0.375rem 0.375rem 0;
    margin: 0.75rem 0;
    font-size: 0.875rem;
}
.markdown-body hr {
    border-color: rgb(229 231 235);
    margin: 1.5rem 0;
}
.markdown-body table {
    display: block;
    width: 100%;
    overflow-x: auto;
    white-space: nowrap;
    border-collapse: collapse;
    margin: 0.75rem 0;
    font-size: 0.8125rem;
    border: 1px solid rgb(229 231 235);
    border-radius: 0.5rem;
}
.markdown-body th,
.markdown-body td {
    padding: 0.5rem 0.75rem;
    border: 1px solid rgb(229 231 235);
    text-align: left;
    vertical-align: top;
}
.markdown-body th {
    background: rgb(249 250 251);
    font-weight: 600;
    color: rgb(17 24 39);
}
.markdown-body td {
    color: rgb(55 65 81);
}

/* Dark mode (class-based, driven by useTheme .dark on <html>) */
.dark .markdown-body {
    color: rgb(209 213 219);
}
.dark .markdown-body h1,
.dark .markdown-body h2,
.dark .markdown-body h3,
.dark .markdown-body h4,
.dark .markdown-body strong,
.dark .markdown-body th {
    color: rgb(243 244 246);
}
.dark .markdown-body h2,
.dark .markdown-body hr,
.dark .markdown-body table,
.dark .markdown-body th,
.dark .markdown-body td {
    border-color: rgb(55 65 81);
}
.dark .markdown-body code {
    background: rgb(55 65 81);
    color: rgb(251 191 36);
}
.dark .markdown-body blockquote {
    background: rgb(69 26 3);
    color: rgb(254 215 170);
    border-color: rgb(217 119 6);
}
.dark .markdown-body th {
    background: rgb(31 41 55);
}
.dark .markdown-body td {
    color: rgb(209 213 219);
}
</style>
