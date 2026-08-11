<script setup>
import { Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, watch } from 'vue';

const props = defineProps({ orders: Object, filters: Object });

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');

watch([search, statusFilter], () => {
    router.get('/admin/orders', { search: search.value, status: statusFilter.value },
        { preserveState: true, replace: true });
});

const markPaidForm = useForm({});
const cancelForm = useForm({});

function markAsPaid(id) {
    if (!confirm('Approve order ini? Customer akan langsung di-recharge.')) return;
    markPaidForm.post(`/admin/orders/${id}/mark-as-paid`, { preserveScroll: true });
}

function cancelOrder(id) {
    if (!confirm('Cancel order ini?')) return;
    cancelForm.post(`/admin/orders/${id}/cancel`, { preserveScroll: true });
}
</script>

<template>
    <AdminLayout>
        <template #title>Orders</template>

        <div class="mb-4 flex gap-4">
            <Link href="/admin/orders/create" class="bg-amber-600 text-white px-4 py-2 rounded hover:bg-amber-700">
                + Buat Order
            </Link>
            <input v-model="search" type="text" placeholder="Cari customer atau plan..."
                   class="flex-1 max-w-md rounded border px-3 py-2" />
            <select v-model="statusFilter" class="rounded border px-3 py-2">
                <option value="">Semua Status</option>
                <option value="pending">Pending</option>
                <option value="paid">Paid</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>

        <div class="bg-white rounded shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left">ID</th>
                        <th class="px-3 py-2 text-left">Customer</th>
                        <th class="px-3 py-2 text-left">Plan</th>
                        <th class="px-3 py-2 text-left">Price</th>
                        <th class="px-3 py-2 text-left">Status</th>
                        <th class="px-3 py-2 text-left">Created</th>
                        <th class="px-3 py-2 text-left">Paid</th>
                        <th class="px-3 py-2 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="o in orders.data" :key="o.id" class="border-t hover:bg-gray-50">
                        <td class="px-3 py-2 font-mono">#{{ o.id }}</td>
                        <td class="px-3 py-2">{{ o.customer?.fullname }}</td>
                        <td class="px-3 py-2">{{ o.plan?.name_plan }}</td>
                        <td class="px-3 py-2">Rp {{ Number(o.price).toLocaleString('id-ID') }}</td>
                        <td class="px-3 py-2">
                            <span :class="{
                                'bg-green-100 text-green-700': o.status === 'paid',
                                'bg-red-100 text-red-700': o.status === 'cancelled',
                                'bg-amber-100 text-amber-700': o.status === 'pending',
                            }" class="px-2 py-0.5 rounded-full text-xs font-medium">{{ o.status }}</span>
                        </td>
                        <td class="px-3 py-2 text-xs">{{ o.created_at }}</td>
                        <td class="px-3 py-2 text-xs">{{ o.paid_at || '-' }}</td>
                        <td class="px-3 py-2 text-right space-x-1">
                            <Link :href="`/invoice/${o.invoice_token}`" target="_blank"
                                  class="text-gray-600 hover:underline text-xs">Invoice</Link>
                            <button v-if="o.status === 'pending'" @click="markAsPaid(o.id)"
                                    class="text-green-600 hover:underline text-xs">Approve</button>
                            <button v-if="o.status === 'pending'" @click="cancelOrder(o.id)"
                                    class="text-red-600 hover:underline text-xs">Cancel</button>
                        </td>
                    </tr>
                    <tr v-if="orders.data.length === 0">
                        <td colspan="8" class="px-4 py-4 text-center text-gray-500">Belum ada order.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="orders.links" class="mt-4 flex justify-center gap-2">
            <Link v-for="link in orders.links" :key="link.label" :href="link.url || '#'"
                  v-html="link.label"
                  class="px-3 py-1 rounded border text-sm"
                  :class="{ 'bg-gray-200': link.active, 'opacity-50 pointer-events-none': !link.url }" />
        </div>
    </AdminLayout>
</template>
