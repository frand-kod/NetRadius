<script setup>
defineProps({ content: String });
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center p-6">
        <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full p-8">
            <div class="markdown-body">
                <!-- eslint-disable-next-line vue/no-v-html -->
                <div v-html="content" />
            </div>
        </div>
    </div>
</template>

<style scoped>
.markdown-body :deep(h1) {
    font-size: 1.5rem;
    font-weight: 700;
    color: rgb(17 24 39);
    margin-bottom: 1rem;
}
.markdown-body :deep(h2),
.markdown-body :deep(h3) {
    font-weight: 700;
    color: rgb(17 24 39);
    margin-top: 1.25rem;
    margin-bottom: 0.5rem;
}
.markdown-body :deep(p) {
    font-size: 0.875rem;
    line-height: 1.6;
    color: rgb(55 65 81);
    margin: 0.5rem 0;
}
.markdown-body :deep(img) {
    display: block;
    margin: 1rem auto;
    width: 12rem;
    height: 12rem;
    object-fit: contain;
}
.markdown-body :deep(table) {
    width: 100%;
    border-collapse: collapse;
    margin: 0.75rem 0;
}
.markdown-body :deep(th),
.markdown-body :deep(td) {
    border: 1px solid rgb(229 231 235);
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    text-align: left;
    color: rgb(55 65 81);
}
.markdown-body :deep(th) {
    background: rgb(249 250 251);
    font-weight: 600;
    color: rgb(17 24 39);
}
.markdown-body :deep(blockquote) {
    border-left: 4px solid rgb(251 191 36);
    background: rgb(255 251 235);
    padding: 0.625rem 1rem;
    border-radius: 0 0.375rem 0.375rem 0;
    margin: 0.625rem 0;
    font-size: 0.875rem;
    color: rgb(120 53 15);
}
.markdown-body :deep(hr) {
    border: none;
    border-top: 1px solid rgb(229 231 235);
    margin: 1.25rem 0;
}
.markdown-body :deep(strong) {
    font-weight: 600;
    color: rgb(17 24 39);
}
.markdown-body :deep(em) {
    color: rgb(107 114 128);
}
</style>
