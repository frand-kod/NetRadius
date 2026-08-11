<script setup>
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, watch } from 'vue';

const props = defineProps({ customers: Object, filters: Object });

const search = ref(props.filters?.search || '');

watch(search, (val) => {
    router.get('/admin/customers', { search: val }, { preserveState: true, replace: true });
});

function destroy(id) {
    if (confirm('Yakin hapus customer ini?')) {
        router.delete(`/admin/customers/${id}`);
    }
}
</script>

<template>
    <AdminLayout>
        <template #title>Customers</template>

        <div class="mb-4 flex gap-4">
            <Link href="/admin/customers/create"
                  class="bg-amber-600 text-white px-4 py-2 rounded hover:bg-amber-700">
                + Tambah Customer
            </Link>
            <input v-model="search" type="text" placeholder="Cari username, nama, email, telepon..."
                   class="flex-1 max-w-md rounded border px-3 py-2" />
        </div>

        <div class="bg-white rounded shadow overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left">Username</th>
                        <th class="px-3 py-2 text-left">Fullname</th>
                        <th class="px-3 py-2 text-left">Phone</th>
                        <th class="px-3 py-2 text-left">Email</th>
                        <th class="px-3 py-2 text-left">Account Type</th>
                        <th class="px-3 py-2 text-left">Service Type</th>
                        <th class="px-3 py-2 text-left">Balance</th>
                        <th class="px-3 py-2 text-left">Status</th>
                        <th class="px-3 py-2 text-left">Last Login</th>
                        <th class="px-3 py-2 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="c in customers.data" :key="c.id" class="border-t hover:bg-gray-50">
                        <td class="px-3 py-2 font-mono">{{ c.username }}</td>
                        <td class="px-3 py-2">{{ c.fullname }}</td>
                        <td class="px-3 py-2">{{ c.phonenumber }}</td>
                        <td class="px-3 py-2">{{ c.email }}</td>
                        <td class="px-3 py-2">{{ c.account_type }}</td>
                        <td class="px-3 py-2">{{ c.service_type }}</td>
                        <td class="px-3 py-2">Rp {{ Number(c.balance).toLocaleString('id-ID') }}</td>
                        <td class="px-3 py-2">
                            <span :class="{
                                'bg-green-100 text-green-700': c.status === 'Active',
                                'bg-red-100 text-red-700': c.status === 'Banned' || c.status === 'Disabled',
                                'bg-yellow-100 text-yellow-700': c.status === 'Inactive' || c.status === 'Suspended',
                                'bg-gray-100 text-gray-700': c.status === 'Limited',
                            }" class="px-2 py-0.5 rounded-full text-xs font-medium">{{ c.status }}</span>
                        </td>
                        <td class="px-3 py-2 text-xs">{{ c.last_login || '-' }}</td>
                        <td class="px-3 py-2 text-right space-x-1">
                            <Link :href="`/admin/customers/${c.id}/edit`"
                                  class="text-blue-600 hover:underline text-xs">Edit</Link>
                            <button @click="destroy(c.id)" class="text-red-600 hover:underline text-xs">Delete</button>
                        </td>
                    </tr>
                    <tr v-if="customers.data.length === 0">
                        <td colspan="10" class="px-4 py-4 text-center text-gray-500">Tidak ada customer.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="customers.links" class="mt-4 flex justify-center gap-2">
            <Link v-for="link in customers.links" :key="link.label" :href="link.url || '#'"
                  v-html="link.label"
                  class="px-3 py-1 rounded border text-sm"
                  :class="{ 'bg-gray-200': link.active, 'opacity-50 pointer-events-none': !link.url }" />
        </div>
    </AdminLayout>
</template>
