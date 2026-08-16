<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import FieldHelp from '@/Components/FieldHelp.vue';
import { computed } from 'vue';

const props = defineProps({ plan: Object, bandwidths: Array });

const form = useForm({
    name_plan: props.plan.name_plan,
    id_bw: props.plan.id_bw,
    price: props.plan.price,
    type: props.plan.type,
    typebp: props.plan.typebp || 'Unlimited',
    limit_type: props.plan.limit_type || '',
    time_limit: props.plan.time_limit ?? null,
    time_unit: props.plan.time_unit || 'Hrs',
    data_limit: props.plan.data_limit ?? null,
    data_unit: props.plan.data_unit || 'MB',
    validity: Number(props.plan.validity),
    validity_unit: props.plan.validity_unit || 'Days',
    shared_users: props.plan.shared_users ?? null,
    enabled: Boolean(props.plan.enabled),
    device: props.plan.device || 'RadiusRest',
});

// Aplikasi hanya memakai satu jalur device: FreeRADIUS REST.
const showLimit = computed(() => form.typebp === 'Limited');
const showTimeLimit = computed(() => showLimit.value && (form.limit_type === '' || form.limit_type === 'Time_Limit' || form.limit_type === 'Both_Limit'));
const showDataLimit = computed(() => showLimit.value && (form.limit_type === '' || form.limit_type === 'Data_Limit' || form.limit_type === 'Both_Limit'));

function submit() { form.put(`/admin/plans/${props.plan.id}`); }
</script>

<template>
    <AdminLayout>
        <template #title>Edit Plan: {{ plan.name_plan }}</template>
        <form @submit.prevent="submit" class="max-w-4xl space-y-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8 dark:border-gray-700 dark:bg-gray-800 transition-colors">

            <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-900/50 dark:bg-amber-900/20 dark:text-amber-200">
                Aplikasi berjalan dengan <strong>FreeRADIUS REST</strong> untuk layanan <strong>Hotspot</strong>.
                Tipe paket dan device diatur otomatis.
            </div>

            <!-- Status -->
            <div class="border-b border-gray-100 pb-2 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Status</h3>
            </div>
            <div>
                <label class="block text-sm font-medium dark:text-gray-300">Status
                    <FieldHelp text="Customer tidak bisa membeli paket non-aktif, tetapi admin tetap bisa recharge." />
                </label>
                <div class="mt-2 flex gap-5">
                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input v-model="form.enabled" type="radio" :value="true" class="accent-amber-600" /> Active
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input v-model="form.enabled" type="radio" :value="false" class="accent-amber-600" /> Not Active
                    </label>
                </div>
            </div>

            <!-- Identitas -->
            <div class="border-b border-gray-100 pt-2 pb-2 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Identitas Paket</h3>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300">Name Plan *</label>
                    <input v-model="form.name_plan" required class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25" />
                </div>
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300">Bandwidth *</label>
                    <select v-model="form.id_bw" required class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25">
                        <option value="">-- Pilih Bandwidth --</option>
                        <option v-for="bw in bandwidths" :key="bw.id" :value="bw.id">{{ bw.name_bw }}</option>
                    </select>
                </div>
            </div>

            <!-- Batasan & Kuota -->
            <div class="border-b border-gray-100 pt-2 pb-2 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Batasan &amp; Kuota</h3>
            </div>
            <div>
                <label class="block text-sm font-medium dark:text-gray-300">Tipe Batasan</label>
                <div class="mt-2 flex gap-5">
                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input v-model="form.typebp" type="radio" value="Unlimited" class="accent-amber-600" /> Unlimited
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input v-model="form.typebp" type="radio" value="Limited" class="accent-amber-600" /> Limited
                    </label>
                </div>
            </div>

            <div v-if="showLimit" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300">Limit Type</label>
                    <div class="mt-2 flex flex-wrap gap-4">
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input v-model="form.limit_type" type="radio" value="Time_Limit" class="accent-amber-600" /> Time
                        </label>
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input v-model="form.limit_type" type="radio" value="Data_Limit" class="accent-amber-600" /> Data
                        </label>
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input v-model="form.limit_type" type="radio" value="Both_Limit" class="accent-amber-600" /> Both
                        </label>
                    </div>
                </div>
            </div>

            <div v-if="showTimeLimit" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300">Time Limit</label>
                    <input v-model.number="form.time_limit" type="number" min="0" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25" />
                </div>
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300">Time Unit</label>
                    <select v-model="form.time_unit" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25">
                        <option value="Hrs">Hrs</option>
                        <option value="Mins">Mins</option>
                    </select>
                </div>
            </div>

            <div v-if="showDataLimit" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300">Data Limit</label>
                    <input v-model.number="form.data_limit" type="number" min="0" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25" />
                </div>
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300">Data Unit</label>
                    <select v-model="form.data_unit" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25">
                        <option value="MB">MB</option>
                        <option value="GB">GB</option>
                    </select>
                </div>
            </div>

            <!-- Harga -->
            <div class="border-b border-gray-100 pt-2 pb-2 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Harga</h3>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300">Price *</label>
                    <input v-model="form.price" required class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25" />
                </div>
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300">Shared Users
                        <FieldHelp text="Berapa perangkat yang bisa online dalam satu akun customer." />
                    </label>
                    <input v-model.number="form.shared_users" type="number" min="0" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25" />
                </div>
            </div>

            <!-- Masa Aktif -->
            <div class="border-b border-gray-100 pt-2 pb-2 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Masa Aktif</h3>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300">Validity *</label>
                    <input v-model.number="form.validity" type="number" min="0" required class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25" />
                </div>
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300">Validity Unit *</label>
                    <select v-model="form.validity_unit" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25">
                        <option value="Mins">Mins</option>
                        <option value="Hrs">Hrs</option>
                        <option value="Days">Days</option>
                        <option value="Months">Months</option>
                    </select>
                </div>
            </div>

            <button type="submit" :disabled="form.processing"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500/40 disabled:cursor-not-allowed disabled:opacity-60">
                Update
            </button>
        </form>
    </AdminLayout>
</template>
