<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { Notivue, Notification, darkTheme, lightTheme, push } from 'notivue';
import { computed, ref, watch } from 'vue';
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

const isSectionActive = (path) => page.url.startsWith(path);
</script>

<template>
    <div class="flex min-h-screen bg-gray-100 dark:bg-gray-900 transition-colors">
        <!-- Sidebar Toggle Overlay -->
        <div v-if="isOpen" @click="isOpen = false" class="fixed inset-0 z-20 bg-black/50 lg:hidden"></div>

        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-30 w-64 shrink-0 flex-col border-r border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 transition-transform lg:static lg:translate-x-0"
               :class="isOpen ? 'translate-x-0' : '-translate-x-full'">
            <div class="flex h-16 items-center gap-2 border-b border-gray-200 dark:border-gray-700 px-5">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-600 text-sm font-bold text-white">B</span>
                <span class="text-lg font-bold text-gray-900 dark:text-white">PHPNuxBill</span>
                <button @click="isOpen = false" aria-label="Tutup menu"
                        class="ml-auto block text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white lg:hidden">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <nav class="flex-1 space-y-6 overflow-y-auto p-3">
                <!-- Overview -->
                <div>
                    <Link href="/admin"
                          class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition"
                          :class="page.url === '/admin' ? 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-900'">
                        Dashboard
                    </Link>
                </div>

                <!-- Operations -->
                <div>
                    <p class="px-3 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Operations</p>
                    <div class="space-y-1">
                        <Link href="/admin/customers"
                              class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition"
                              :class="isSectionActive('/admin/customers') ? 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50'">
                            Customers
                        </Link>
                        <Link href="/admin/orders"
                              class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition"
                              :class="isSectionActive('/admin/orders') ? 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50'">
                            Orders
                        </Link>
                        <Link href="/admin/vouchers"
                              class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition"
                              :class="isSectionActive('/admin/vouchers') ? 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50'">
                            Vouchers
                        </Link>
                    </div>
                </div>

                <!-- Network -->
                <div>
                    <p class="px-3 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Network</p>
                    <div class="space-y-1">
                        <Link href="/admin/plans"
                              class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition"
                              :class="isSectionActive('/admin/plans') ? 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50'">
                            Plans
                        </Link>
                        <Link href="/admin/bandwidths"
                              class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition"
                              :class="isSectionActive('/admin/bandwidths') ? 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50'">
                            Bandwidth
                        </Link>
                    </div>
                </div>

                <!-- Finance -->
                <div>
                    <p class="px-3 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Finance</p>
                    <div class="space-y-1">
                        <Link href="/admin/income-report"
                              class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition"
                              :class="isSectionActive('/admin/income-report') ? 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50'">
                            Income Report
                        </Link>
                    </div>
                </div>

                <!-- System -->
                <div>
                    <p class="px-3 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">System</p>
                    <div class="space-y-1">
                        <Link href="/admin/logs"
                              class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition"
                              :class="isSectionActive('/admin/logs') ? 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50'">
                            Logs
                        </Link>
                        <Link href="/admin/help"
                              class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition"
                              :class="isSectionActive('/admin/help') ? 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50'">
                            Dokumentasi
                        </Link>
                    </div>
                </div>

                <!-- Settings -->
                <div>
                    <p class="px-3 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Settings</p>
                    <div class="space-y-1">
                        <Link href="/admin/settings/general"
                              class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition"
                              :class="isSectionActive('/admin/settings/general') ? 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50'">
                            Pengaturan Umum
                        </Link>
                        <Link href="/admin/settings/payment"
                              class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition"
                              :class="isSectionActive('/admin/settings/payment') ? 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50'">
                            Payment Settings
                        </Link>
                        <Link href="/admin/settings/notification"
                              class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition"
                              :class="isSectionActive('/admin/settings/notification') ? 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50'">
                            Notification Settings
                        </Link>
                    </div>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="flex h-16 shrink-0 items-center justify-between border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-6 transition-colors">
                <div class="flex items-center gap-4">
                    <button @click="isOpen = true" class="block text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white lg:hidden">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                        <slot name="title">Dashboard</slot>
                    </h1>
                </div>
                <div class="flex items-center gap-4">
                    <span v-if="page.props.auth?.user?.fullname" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ page.props.auth.user.fullname }}
                    </span>
                    <Link href="/admin/logout" method="post" as="button"
                          class="text-sm font-medium text-red-600 transition hover:text-red-700">
                        Logout
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
