<script setup>
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
</script>

<template>
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-800 text-white p-4">
            <h2 class="text-xl font-bold mb-6">PHPNuxBill</h2>
            <nav class="space-y-1">
                <Link href="/admin" class="block px-3 py-2 rounded hover:bg-gray-700"
                      :class="{ 'bg-gray-700': page.url === '/admin' || page.url.startsWith('/admin/') }">
                    Dashboard
                </Link>
                <Link href="/admin/customers" class="block px-3 py-2 rounded hover:bg-gray-700"
                      :class="{ 'bg-gray-700': page.url.startsWith('/admin/customers') }">
                    Customers
                </Link>
                <Link href="/admin/plans" class="block px-3 py-2 rounded hover:bg-gray-700"
                      :class="{ 'bg-gray-700': page.url.startsWith('/admin/plans') }">
                    Plans
                </Link>
                <Link href="/admin/orders" class="block px-3 py-2 rounded hover:bg-gray-700"
                      :class="{ 'bg-gray-700': page.url.startsWith('/admin/orders') }">
                    Orders
                </Link>
                <Link href="/admin/vouchers" class="block px-3 py-2 rounded hover:bg-gray-700"
                      :class="{ 'bg-gray-700': page.url.startsWith('/admin/vouchers') }">
                    Vouchers
                </Link>
                <Link href="/admin/routers" class="block px-3 py-2 rounded hover:bg-gray-700"
                      :class="{ 'bg-gray-700': page.url.startsWith('/admin/routers') }">
                    Routers
                </Link>
                <Link href="/admin/bandwidths" class="block px-3 py-2 rounded hover:bg-gray-700"
                      :class="{ 'bg-gray-700': page.url.startsWith('/admin/bandwidths') }">
                    Bandwidth
                </Link>
                <Link href="/admin/settings/payment" class="block px-3 py-2 rounded hover:bg-gray-700"
                      :class="{ 'bg-gray-700': page.url.startsWith('/admin/settings/payment') }">
                    Payment Settings
                </Link>
                <Link href="/admin/settings/notification" class="block px-3 py-2 rounded hover:bg-gray-700"
                      :class="{ 'bg-gray-700': page.url.startsWith('/admin/settings/notification') }">
                    Notification Settings
                </Link>
                <Link href="/admin/income-report" class="block px-3 py-2 rounded hover:bg-gray-700"
                      :class="{ 'bg-gray-700': page.url.startsWith('/admin/income-report') }">
                    Income Report
                </Link>
                <Link href="/admin/logs" class="block px-3 py-2 rounded hover:bg-gray-700"
                      :class="{ 'bg-gray-700': page.url.startsWith('/admin/logs') }">
                    Logs
                </Link>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6">
            <header class="mb-6 flex justify-between items-center">
                <h1 class="text-2xl font-semibold">
                    <slot name="title">Dashboard</slot>
                </h1>
                <div class="flex items-center gap-4">
                    <span v-if="page.props.auth?.user?.fullname">{{ page.props.auth.user.fullname }}</span>
                    <Link href="/admin/logout" method="post" as="button"
                          class="text-red-600 hover:underline">Logout</Link>
                </div>
            </header>
            <div v-if="page.props.flash?.success" class="mb-4 bg-green-100 text-green-700 p-3 rounded">
                {{ page.props.flash.success }}
            </div>
            <div v-if="page.props.flash?.error" class="mb-4 bg-red-100 text-red-700 p-3 rounded">
                {{ page.props.flash.error }}
            </div>
            <slot />
        </main>
    </div>
</template>
