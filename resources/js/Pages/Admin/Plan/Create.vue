<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ bandwidths: Array });

const form = useForm({
    name_plan: '', id_bw: '', price: '', price_old: '',
    type: 'Hotspot', typebp: 'Unlimited', limit_type: '',
    time_limit: null, time_unit: 'Hrs', data_limit: null, data_unit: 'MB',
    validity: 30, validity_unit: 'Days', shared_users: null,
    routers: '', is_radius: false, pool: '',
    plan_expired: 0, expired_date: 20, enabled: true,
    allow_purchase: 'yes', prepaid: 'yes', plan_type: 'Personal',
    device: '', on_login: '', on_logout: '',
});

function submit() { form.post('/admin/plans'); }
</script>

<template>
    <AdminLayout>
        <template #title>Add Plan</template>
        <form @submit.prevent="submit" class="bg-white rounded shadow p-6 max-w-3xl space-y-4">
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium">Name Plan *</label>
                    <input v-model="form.name_plan" required class="mt-1 block w-full rounded border px-3 py-2" />
                    <p v-if="form.errors.name_plan" class="text-red-500 text-xs mt-1">{{ form.errors.name_plan }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium">Bandwidth *</label>
                    <select v-model="form.id_bw" required class="mt-1 block w-full rounded border px-3 py-2">
                        <option value="">-- Pilih Bandwidth --</option>
                        <option v-for="bw in bandwidths" :key="bw.id" :value="bw.id">{{ bw.name_bw }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium">Price *</label>
                    <input v-model="form.price" required class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium">Price Old *</label>
                    <input v-model="form.price_old" required class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Type *</label>
                    <select v-model="form.type" class="mt-1 block w-full rounded border px-3 py-2">
                        <option value="Hotspot">Hotspot</option>
                        <option value="PPPOE">PPPOE</option>
                        <option value="VPN">VPN</option>
                        <option value="Balance">Balance</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium">Type BP</label>
                    <select v-model="form.typebp" class="mt-1 block w-full rounded border px-3 py-2">
                        <option value="">--</option>
                        <option value="Unlimited">Unlimited</option>
                        <option value="Limited">Limited</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium">Limit Type</label>
                    <select v-model="form.limit_type" class="mt-1 block w-full rounded border px-3 py-2">
                        <option value="">--</option>
                        <option value="Time_Limit">Time Limit</option>
                        <option value="Data_Limit">Data Limit</option>
                        <option value="Both_Limit">Both Limit</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium">Time Limit</label>
                    <input v-model.number="form.time_limit" type="number" min="0" class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Time Unit</label>
                    <select v-model="form.time_unit" class="mt-1 block w-full rounded border px-3 py-2">
                        <option value="Mins">Mins</option>
                        <option value="Hrs">Hrs</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium">Data Limit</label>
                    <input v-model.number="form.data_limit" type="number" min="0" class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Data Unit</label>
                    <select v-model="form.data_unit" class="mt-1 block w-full rounded border px-3 py-2">
                        <option value="MB">MB</option>
                        <option value="GB">GB</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium">Validity *</label>
                    <input v-model.number="form.validity" type="number" min="0" required class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium">Validity Unit *</label>
                    <select v-model="form.validity_unit" class="mt-1 block w-full rounded border px-3 py-2">
                        <option value="Mins">Mins</option>
                        <option value="Hrs">Hrs</option>
                        <option value="Days">Days</option>
                        <option value="Months">Months</option>
                        <option value="Period">Period</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium">Shared Users</label>
                    <input v-model.number="form.shared_users" type="number" min="0" class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Routers *</label>
                    <input v-model="form.routers" required class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div class="flex items-center pt-6">
                    <input v-model="form.is_radius" type="checkbox" class="rounded" />
                    <label class="ml-2 text-sm">Is Radius</label>
                </div>
                <div>
                    <label class="block text-sm font-medium">Pool</label>
                    <input v-model="form.pool" class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Plan Expired *</label>
                    <input v-model.number="form.plan_expired" type="number" required class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium">Expired Date *</label>
                    <input v-model.number="form.expired_date" type="number" required class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Allow Purchase *</label>
                    <select v-model="form.allow_purchase" class="mt-1 block w-full rounded border px-3 py-2">
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium">Prepaid *</label>
                    <select v-model="form.prepaid" class="mt-1 block w-full rounded border px-3 py-2">
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium">Plan Type *</label>
                    <select v-model="form.plan_type" class="mt-1 block w-full rounded border px-3 py-2">
                        <option value="Business">Business</option>
                        <option value="Personal">Personal</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium">Device *</label>
                    <input v-model="form.device" required class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div class="flex items-center pt-6">
                    <input v-model="form.enabled" type="checkbox" class="rounded" />
                    <label class="ml-2 text-sm">Enabled</label>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium">On Login</label>
                <textarea v-model="form.on_login" rows="2" class="mt-1 block w-full rounded border px-3 py-2"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium">On Logout</label>
                <textarea v-model="form.on_logout" rows="2" class="mt-1 block w-full rounded border px-3 py-2"></textarea>
            </div>

            <button type="submit" :disabled="form.processing"
                    class="bg-amber-600 text-white px-6 py-2 rounded hover:bg-amber-700 disabled:opacity-50">
                Simpan
            </button>
        </form>
    </AdminLayout>
</template>
