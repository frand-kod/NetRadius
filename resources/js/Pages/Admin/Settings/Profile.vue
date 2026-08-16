<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ profile: Object });

const form = useForm({
    fullname: props.profile?.fullname || '',
    phone: props.profile?.phone || '',
    email: props.profile?.email || '',
    current_password: '',
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post('/admin/profile');
}

function fieldClass(field) {
    const base = 'block w-full rounded-lg border bg-white px-3 py-2 text-sm text-gray-900 transition dark:bg-gray-900 dark:text-white focus:ring-1';
    return form.errors[field]
        ? `${base} border-red-400 focus:border-red-500 focus:ring-red-500`
        : `${base} border-gray-300 dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500`;
}
</script>

<template>
    <AdminLayout>
        <template #title>Profil Saya</template>

        <form @submit.prevent="submit" class="max-w-2xl space-y-6">

            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
                <div class="border-b border-gray-100 dark:border-gray-700 p-5">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Informasi Akun</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Perbarui nama dan data kontak Anda.</p>
                </div>
                <div class="space-y-4 p-5">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1">Nama Lengkap</label>
                        <input v-model="form.fullname" type="text" :class="fieldClass('fullname')" />
                        <p v-if="form.errors.fullname" class="mt-1 text-xs text-red-600">{{ form.errors.fullname }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1">Telepon</label>
                            <input v-model="form.phone" type="text" :class="fieldClass('phone')" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1">Email</label>
                            <input v-model="form.email" type="email" :class="fieldClass('email')" />
                            <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
                <div class="border-b border-gray-100 dark:border-gray-700 p-5">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Ganti Password</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Kosongkan jika tidak ingin mengubah password.</p>
                </div>
                <div class="space-y-4 p-5">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1">Password Saat Ini</label>
                        <input v-model="form.current_password" type="password" :class="fieldClass('current_password')" autocomplete="current-password" />
                        <p v-if="form.errors.current_password" class="mt-1 text-xs text-red-600">{{ form.errors.current_password }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1">Password Baru</label>
                            <input v-model="form.password" type="password" :class="fieldClass('password')" autocomplete="new-password" />
                            <p v-if="form.errors.password" class="mt-1 text-xs text-red-600">{{ form.errors.password }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1">Konfirmasi Password</label>
                            <input v-model="form.password_confirmation" type="password" :class="fieldClass('password_confirmation')" autocomplete="new-password" />
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" :disabled="form.processing"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500/40 disabled:cursor-not-allowed disabled:opacity-60">
                {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
            </button>
        </form>
    </AdminLayout>
</template>
