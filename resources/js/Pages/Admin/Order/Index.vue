<script setup>
import { Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { computed, ref, watch } from 'vue';

const props = defineProps({ orders: Object, filters: Object });

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');

watch([search, statusFilter], () => {
    router.get('/admin/orders', { search: search.value, status: statusFilter.value },
        { preserveState: true, replace: true });
});

const markPaidForm = useForm({});
const cancelForm = useForm({});

const showConfirm = ref(false);
const confirmAction = ref(null); // 'approve' | 'cancel'
const pendingId = ref(null);
const submitting = ref(false);

const isApprove = computed(() => confirmAction.value === 'approve');
const confirmTitle = computed(() => (isApprove.value ? 'Approve Order' : 'Cancel Order'));
const confirmMessage = computed(() => isApprove.value
    ? 'Approve order ini? Customer akan langsung di-recharge.'
    : 'Yakin ingin membatalkan (cancel) order ini?');

function openConfirm(id, action) {
    pendingId.value = id;
    confirmAction.value = action;
    showConfirm.value = true;
}

function closeConfirm() {
    if (submitting.value) return;
    showConfirm.value = false;
    pendingId.value = null;
    confirmAction.value = null;
}

function confirmGo() {
    const id = pendingId.value;
    const action = confirmAction.value;
    submitting.value = true;
    const form = action === 'approve' ? markPaidForm : cancelForm;
    form.post(`/admin/orders/${id}/${action === 'approve' ? 'mark-as-paid' : 'cancel'}`, {
        preserveScroll: true,
        onFinish: () => { submitting.value = false; closeConfirm(); },
    });
}
</script>

<template>
    <AdminLayout>
        <template #title>Orders</template>

        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <Link href="/admin/orders/create" class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500/40">
                + Buat Order
            </Link>
            <div class="flex flex-col gap-3 sm:flex-row">
                <input v-model="search" type="text" placeholder="Cari customer atau plan..."
                       class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25 sm:w-72" />
                <select v-model="statusFilter" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25">
                    <option value="">Semua Status</option>
                    <option value="pending">Pending</option>
                    <option value="paid">Paid</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 transition-colors">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 transition-colors">
                        <tr>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">ID</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Customer</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Plan</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Price</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Created</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Paid</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="o in orders.data" :key="o.id"
                            class="border-b border-gray-100 dark:border-gray-700 transition hover:bg-amber-50/40 dark:hover:bg-gray-700/50"
                            :class="{ 'border-b-0': o === orders.data[orders.data.length - 1] }">
                            <td class="px-4 py-3 align-top font-mono text-gray-700 dark:text-gray-300">#{{ o.id }}</td>
                            <td class="px-4 py-3 align-top text-gray-700 dark:text-gray-300">{{ o.customer?.fullname }}</td>
                            <td class="px-4 py-3 align-top text-gray-700 dark:text-gray-300">{{ o.plan?.name_plan }}</td>
                            <td class="px-4 py-3 align-top text-gray-700 dark:text-gray-300">Rp {{ Number(o.price).toLocaleString('id-ID') }}</td>
                            <td class="px-4 py-3 align-top text-gray-700 dark:text-gray-300">
                                <span :class="{
                                    'bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400': o.status === 'paid',
                                    'bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-400': o.status === 'cancelled',
                                    'bg-amber-100 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400': o.status === 'pending',
                                }" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold">{{ o.status }}</span>
                            </td>
                            <td class="px-4 py-3 align-top text-xs text-gray-700 dark:text-gray-300">{{ o.created_at }}</td>
                            <td class="px-4 py-3 align-top text-xs text-gray-700 dark:text-gray-300">{{ o.paid_at || '-' }}</td>
                            <td class="px-4 py-3 align-top text-right text-gray-700 dark:text-gray-300 space-x-1">
                                <Link :href="`/invoice/${o.invoice_token}`" target="_blank"
                                      class="text-xs font-medium text-gray-600 dark:text-gray-400 transition hover:underline">Invoice</Link>
                                <button v-if="o.status === 'pending'" @click="openConfirm(o.id, 'approve')"
                                        class="text-xs font-medium text-green-600 dark:text-green-400 transition hover:underline">Approve</button>
                                <button v-if="o.status === 'pending'" @click="openConfirm(o.id, 'cancel')"
                                        class="text-xs font-medium text-red-600 dark:text-red-400 transition hover:underline">Cancel</button>
                            </td>
                        </tr>
                        <tr v-if="orders.data.length === 0">
                            <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada order.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="orders.links" class="mt-6 flex items-center justify-center gap-2">
            <Link v-for="link in orders.links" :key="link.label" :href="link.url || '#'"
                  v-html="link.label"
                  class="rounded-lg border border-gray-200 dark:border-gray-700 px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300 transition hover:bg-gray-50 dark:hover:bg-gray-700"
                  :class="link.active ? 'border-amber-600 bg-amber-600 text-white' : 'border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300', !link.url && 'pointer-events-none opacity-40'" />
        </div>

        <ConfirmModal :open="showConfirm" :title="confirmTitle" :message="confirmMessage"
                      :confirm-text="isApprove ? 'Approve' : 'Cancel'"
                      :danger="!isApprove" :loading="submitting"
                      @confirm="confirmGo" @close="closeConfirm" />
    </AdminLayout>
</template>

