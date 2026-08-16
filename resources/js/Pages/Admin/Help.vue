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

        <div class="rounded-lg border border-neutral-200 bg-white transition-colors dark:border-neutral-700 dark:bg-neutral-900">
            <!-- Doc switcher -->
            <div class="flex flex-wrap gap-2 border-b border-neutral-200 px-6 py-4 dark:border-neutral-700">
                <Link v-for="d in docs" :key="d.key" :href="`/admin/help/${d.key}`"
                      class="rounded-md px-4 py-2 text-sm font-medium tracking-tight transition"
                      :class="d.active
                          ? 'bg-neutral-900 text-white dark:bg-neutral-100 dark:text-neutral-900'
                          : 'border border-neutral-200 bg-white text-neutral-500 hover:bg-neutral-100 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-400 dark:hover:bg-neutral-700'">
                    {{ d.title }}
                </Link>
            </div>

            <div class="markdown-body mx-auto max-w-4xl px-6 py-10">
                <!-- eslint-disable-next-line vue/no-v-html -->
                <div v-html="content" />
            </div>
        </div>
    </AdminLayout>
</template>

<style>
/* Editorial warm-monochrome markdown styling (minimalist-ui directive).
   Rendered server-side via Str::markdown() and injected as raw HTML (v-html),
   so these selectors target the generated elements directly. Dark mode follows
   the app's class-based .dark toggle, not the OS media query. */
.markdown-body {
    font-size: 0.9375rem;
    line-height: 1.7;
    color: #2f3437;
}
.markdown-body h1,
.markdown-body h2,
.markdown-body h3,
.markdown-body h4,
.markdown-body strong {
    color: #111111;
    letter-spacing: -0.02em;
}
.markdown-body h1 {
    font-size: 1.75rem;
    font-weight: 700;
    line-height: 1.15;
    margin-bottom: 1rem;
}
.markdown-body h2 {
    font-size: 1.25rem;
    font-weight: 700;
    line-height: 1.2;
    margin-top: 2.25rem;
    margin-bottom: 0.5rem;
    padding-bottom: 0.375rem;
    border-bottom: 1px solid #eaeaea;
}
.markdown-body h3 {
    font-size: 1.0625rem;
    font-weight: 600;
    margin-top: 1.75rem;
    margin-bottom: 0.375rem;
}
.markdown-body h4 {
    font-size: 0.9375rem;
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
    color: #111111;
    text-decoration: underline;
    text-underline-offset: 2px;
}
.markdown-body a:hover {
    opacity: 0.7;
}
.markdown-body code {
    font-family: ui-monospace, 'SF Mono', 'JetBrains Mono', Menlo, monospace;
    font-size: 0.8125rem;
    background: #fbf3db;
    color: #956400;
    padding: 0.125rem 0.375rem;
    border-radius: 0.25rem;
}
.markdown-body pre {
    background: #111111;
    color: #f7f6f3;
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
    border-left: 4px solid #111111;
    background: #f7f6f3;
    color: #2f3437;
    padding: 0.625rem 1rem;
    border-radius: 0 0.375rem 0.375rem 0;
    margin: 0.75rem 0;
    font-size: 0.875rem;
}
.markdown-body kbd {
    font-family: ui-monospace, 'SF Mono', 'JetBrains Mono', Menlo, monospace;
    font-size: 0.75rem;
    border: 1px solid #eaeaea;
    border-radius: 0.25rem;
    background: #f7f6f3;
    padding: 0.125rem 0.375rem;
    color: #2f3437;
}
.markdown-body hr {
    border-color: #eaeaea;
    margin: 1.75rem 0;
}
.markdown-body table {
    display: block;
    width: 100%;
    overflow-x: auto;
    white-space: nowrap;
    border-collapse: collapse;
    margin: 0.75rem 0;
    font-size: 0.8125rem;
    border: 1px solid #eaeaea;
    border-radius: 0.375rem;
}
.markdown-body th,
.markdown-body td {
    padding: 0.5rem 0.75rem;
    border: 1px solid #eaeaea;
    text-align: left;
    vertical-align: top;
}
.markdown-body th {
    background: #f7f6f3;
    font-weight: 600;
    color: #111111;
}
.markdown-body td {
    color: #2f3437;
}

/* Dark mode (class-based, driven by useTheme .dark on <html>) */
.dark .markdown-body {
    color: #d6d3d1;
}
.dark .markdown-body h1,
.dark .markdown-body h2,
.dark .markdown-body h3,
.dark .markdown-body h4,
.dark .markdown-body strong,
.dark .markdown-body th {
    color: #fafaf9;
}
.dark .markdown-body h2,
.dark .markdown-body hr,
.dark .markdown-body table,
.dark .markdown-body th,
.dark .markdown-body td {
    border-color: #292524;
}
.dark .markdown-body a {
    color: #fafaf9;
}
.dark .markdown-body code {
    background: #292524;
    color: #e7c66b;
}
.dark .markdown-body pre {
    background: #0c0a09;
    color: #e7e5e4;
}
.dark .markdown-body blockquote {
    background: #1c1917;
    border-color: #fafaf9;
    color: #d6d3d1;
}
.dark .markdown-body kbd {
    background: #1c1917;
    border-color: #292524;
    color: #d6d3d1;
}
.dark .markdown-body th {
    background: #1c1917;
}
.dark .markdown-body td {
    color: #d6d3d1;
}
</style>
