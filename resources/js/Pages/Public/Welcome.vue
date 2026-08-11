<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100">
        <h1 class="text-3xl font-bold mb-4">PHPNuxBill</h1>
        <p class="text-gray-600 mb-6">Billing Hotspot Mikrotik</p>
        <div class="flex gap-4">
            <Link href="/admin/login" class="bg-amber-600 text-white px-6 py-2 rounded hover:bg-amber-700">
                Admin Panel
            </Link>
            <Link href="/customer/login" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                Customer Login
            </Link>
        </div>
    </div>
</template>
