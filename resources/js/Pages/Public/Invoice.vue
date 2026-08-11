<script setup>
defineProps({
    order: Object,
    qrPath: String,
});
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center p-6">
        <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6">
            <h1 class="text-xl font-bold mb-4">Invoice #{{ order.id }}</h1>

            <div class="space-y-2 text-sm mb-4">
                <div class="flex justify-between">
                    <span class="text-gray-600">Customer</span>
                    <span class="font-medium">{{ order.customer?.fullname }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Paket</span>
                    <span class="font-medium">{{ order.plan?.name_plan }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Harga</span>
                    <span class="font-medium">Rp {{ Number(order.price).toLocaleString('id-ID') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Status</span>
                    <span :class="{
                        'text-amber-600': order.status === 'pending',
                        'text-green-600': order.status === 'paid',
                        'text-red-600': order.status === 'cancelled',
                    }" class="font-medium capitalize">{{ order.status }}</span>
                </div>
            </div>

            <div v-if="order.status === 'pending'" class="border-t pt-4">
                <img v-if="qrPath" :src="`/storage/${qrPath}`" alt="QR Pembayaran"
                     class="mx-auto w-64 rounded border mb-3" />
                <p v-else class="text-center text-gray-500">QR pembayaran belum diatur oleh admin.</p>
                <p class="text-center text-sm text-gray-500 mt-2">
                    Silakan lakukan pembayaran, admin akan konfirmasi secara manual.
                </p>
            </div>

            <div v-else-if="order.status === 'paid'" class="border-t pt-4 text-center text-green-600 font-medium">
                ✓ Pembayaran sudah dikonfirmasi
            </div>
        </div>
    </div>
</template>
