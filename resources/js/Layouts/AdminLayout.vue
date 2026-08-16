<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { Notivue, Notification, darkTheme, lightTheme, push } from 'notivue';
import { computed, ref, watch } from 'vue';
import Icon from '@/Components/Icon.vue';
import ThemeToggler from '@/Components/ThemeToggler.vue';
import { useTheme } from '@/Composables/useTheme.js';

const page = usePage();
const isOpen = ref(false);
const { isDark } = useTheme();

// Tema toast mengikuti dark mode aplikasi.
const toastTheme = computed(() => (isDark.value ? darkTheme : lightTheme));

// Tutup drawer otomatis saat navigasi (klik menu) terjadi di layar mobile.
watch(() => page.url, () => { isOpen.value = false; });

// Tampilkan flash success/error sebagai toast notifikasi.
// immediate: true agar toast muncul bila flash sudah ada saat layout pertama kali dirender.
watch([() => page.props.flash?.success, () => page.props.flash?.error], ([s, e]) => {
    if (s) push.success(s);
    if (e) push.error(e);
}, { immediate: true });

// Navigasi sidebar — data-driven agar setiap item punya ikon + states yang konsisten.
const navSections = [
    {
        label: 'Overview',
        items: [
            { label: 'Dashboard', href: '/admin', icon: 'dashboard', exact: true },
        ],
    },
    {
        label: 'Operations',
        items: [
            { label: 'Customers', href: '/admin/customers', icon: 'customers' },
            { label: 'Orders', href: '/admin/orders', icon: 'orders' },
            { label: 'Vouchers', href: '/admin/vouchers', icon: 'vouchers' },
        ],
    },
    {
        label: 'Network',
        items: [
            { label: 'Plans', href: '/admin/plans', icon: 'plans' },
            { label: 'Bandwidth', href: '/admin/bandwidths', icon: 'bandwidth' },
        ],
    },
    {
        label: 'Finance',
        items: [
            { label: 'Income Report', href: '/admin/income-report', icon: 'income' },
        ],
    },
    {
        label: 'System',
        items: [
            { label: 'Logs', href: '/admin/logs', icon: 'logs' },
            { label: 'Dokumentasi', href: '/admin/help', icon: 'documentation' },
        ],
    },
    {
        label: 'Settings',
        items: [
            { label: 'Pengaturan Umum', href: '/admin/settings/general', icon: 'settings' },
            { label: 'Payment Settings', href: '/admin/settings/payment', icon: 'payment' },
            { label: 'Notification Settings', href: '/admin/settings/notification', icon: 'notification' },
            { label: 'Profil Saya', href: '/admin/profile', icon: 'customers' },
        ],
    },
];

const isItemActive = (item) => (item.exact ? page.url === item.href : page.url.startsWith(item.href));
</script>

<template>
    <div class="flex min-h-screen bg-gray-100 dark:bg-gray-900 transition-colors">
        <!-- Sidebar Toggle Overlay -->
        <div v-if="isOpen" @click="isOpen = false" class="fixed inset-0 z-20 bg-black/50 lg:hidden"></div>

        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-30 w-64 shrink-0 flex-col border-r border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 transition-transform lg:static lg:translate-x-0"
               :class="isOpen ? 'translate-x-0' : '-translate-x-full'">
            <div class="flex h-16 items-center gap-2 border-b border-gray-200 dark:border-gray-700 px-5">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-600 text-sm font-bold text-white">N</span>
                <span class="text-lg font-bold text-gray-900 dark:text-white">NuxBill</span>
                <button @click="isOpen = false" aria-label="Tutup menu"
                        class="ml-auto block rounded p-1 text-gray-500 hover:text-gray-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-600 dark:text-gray-400 dark:hover:text-white lg:hidden">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <nav class="flex-1 space-y-6 overflow-y-auto p-3">
                <div v-for="section in navSections" :key="section.label">
                    <p class="px-3 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">{{ section.label }}</p>
                    <div class="space-y-1">
                        <Link v-for="item in section.items" :key="item.href" :href="item.href"
                              class="group flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-600"
                              :class="isItemActive(item)
                                  ? 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400'
                                  : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-900'">
                            <Icon :name="item.icon" />
                            {{ item.label }}
                        </Link>
                    </div>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="flex h-16 shrink-0 items-center justify-between border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-6 transition-colors">
                <div class="flex items-center gap-4">
                    <button @click="isOpen = true" aria-label="Buka menu"
                            class="block rounded p-1 text-gray-500 hover:text-gray-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-600 dark:text-gray-400 dark:hover:text-white lg:hidden">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                        <slot name="title">Dashboard</slot>
                    </h1>
                </div>
                <div class="flex items-center gap-4">
                    <ThemeToggler />
                    <span v-if="page.props.auth?.user?.fullname" class="hidden text-sm font-medium text-gray-700 dark:text-gray-300 sm:inline">
                        {{ page.props.auth.user.fullname }}
                    </span>
                    <Link href="/admin/logout" method="post" as="button"
                          class="flex items-center gap-1.5 text-sm font-medium text-red-600 transition hover:text-red-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">
                        <Icon name="logout" />
                        <span class="hidden sm:inline">Logout</span>
                    </Link>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden p-6">
                <slot />
            </main>
        </div>

        <!-- Toast notifications -->
        <Notivue v-slot="item">
            <Notification :item="item" :theme="toastTheme" />
        </Notivue>
    </div>
</template>
