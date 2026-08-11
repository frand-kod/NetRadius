<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const form = useForm({
    username: '', password: '', photo: '/user.default.jpg',
    pppoe_username: '', pppoe_password: '', pppoe_ip: '',
    fullname: '', address: '', city: '', district: '', state: '', zip: '',
    phonenumber: '0', email: '', coordinates: '',
    account_type: 'Personal', balance: 0, service_type: 'Hotspot',
    auto_renewal: false, status: 'Active', created_by: 0,
});

function submit() { form.post('/admin/customers'); }
</script>

<template>
    <AdminLayout>
        <template #title>Add Customer</template>
        <form @submit.prevent="submit" class="bg-white rounded shadow p-6 max-w-3xl space-y-4">
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium">Username *</label>
                    <input v-model="form.username" required class="mt-1 block w-full rounded border px-3 py-2" />
                    <p v-if="form.errors.username" class="text-red-500 text-xs mt-1">{{ form.errors.username }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium">Password *</label>
                    <input v-model="form.password" type="password" required class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Fullname *</label>
                    <input v-model="form.fullname" required class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium">Email *</label>
                    <input v-model="form.email" type="email" required class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Phone *</label>
                    <input v-model="form.phonenumber" type="tel" required class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Photo</label>
                    <input v-model="form.photo" class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium">PPPoE Username</label>
                    <input v-model="form.pppoe_username" class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">PPPoE Password</label>
                    <input v-model="form.pppoe_password" type="password" class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">PPPoE IP</label>
                    <input v-model="form.pppoe_ip" class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
            </div>

            <div class="grid grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium">City</label>
                    <input v-model="form.city" class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">District</label>
                    <input v-model="form.district" class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">State</label>
                    <input v-model="form.state" class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">ZIP</label>
                    <input v-model="form.zip" class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium">Account Type *</label>
                    <select v-model="form.account_type" class="mt-1 block w-full rounded border px-3 py-2">
                        <option value="Personal">Personal</option>
                        <option value="Business">Business</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium">Service Type *</label>
                    <select v-model="form.service_type" class="mt-1 block w-full rounded border px-3 py-2">
                        <option value="Hotspot">Hotspot</option>
                        <option value="PPPoE">PPPoE</option>
                        <option value="VPN">VPN</option>
                        <option value="Others">Others</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium">Status *</label>
                    <select v-model="form.status" class="mt-1 block w-full rounded border px-3 py-2">
                        <option value="Active">Active</option>
                        <option value="Banned">Banned</option>
                        <option value="Disabled">Disabled</option>
                        <option value="Inactive">Inactive</option>
                        <option value="Limited">Limited</option>
                        <option value="Suspended">Suspended</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium">Balance</label>
                    <input v-model.number="form.balance" type="number" step="0.01" min="0"
                           class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Coordinates *</label>
                    <input v-model="form.coordinates" required class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div class="flex items-center pt-6">
                    <input v-model="form.auto_renewal" type="checkbox" class="rounded" />
                    <label class="ml-2 text-sm">Auto Renewal</label>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium">Address</label>
                <textarea v-model="form.address" rows="2" class="mt-1 block w-full rounded border px-3 py-2"></textarea>
            </div>

            <button type="submit" :disabled="form.processing"
                    class="bg-amber-600 text-white px-6 py-2 rounded hover:bg-amber-700 disabled:opacity-50">
                Simpan
            </button>
        </form>
    </AdminLayout>
</template>
