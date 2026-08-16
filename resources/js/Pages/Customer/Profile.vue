<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({ profile: Object });

const form = useForm({
    fullname: props.profile?.fullname || '',
    phonenumber: props.profile?.phonenumber || '',
    current_password: '',
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post('/customer/profile');
}

function fieldClass(field) {
    const base = 'block w-full rounded-lg border bg-white px-3 py-2 text-sm text-gray-900 transition dark:bg-gray-900 dark:text-white focus:ring-1';
    return form.errors[field]
        ? `${base} border-red-400 focus:border-red-500 focus:ring-red-500`
        : `${base} border-gray-300 dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500`;
}
</script>

<template>
    <div class="min-h-screen bg-gray-100 p-6">
        <div class="mx-auto max-w-2xl">
            <div class="mb-4 flex items-center justify-between rounded bg-white p-4 shadow">
                <h1 class="text-xl font-bold text-gray-900">Profil Saya</h1>
                <a href="/customer/dashboard" class="text-sm text-blue-600 hover:underline">Kembali ke Dashboard</a>
            </div>

            <form @submit.prevent="submit" class="space-y-6">

                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 p-5">
                        <h3 class="text-sm font-semibold text-gray-900">Informasi Akun</h3>
                        <p class="text-xs text-gray-500">Perbarui nama dan nomor telepon Anda.</p>
                    </div>
                    <div class="space-y-4 p-5">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 uppercase tracking-wider mb-1">Nama Lengkap</label>
                            <input v-model="form.fullname" type="text" :class="fieldClass('fullname')" />
                            <p v-if="form.errors.fullname" class="mt-1 text-xs text-red-600">{{ form.errors.fullname }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 uppercase tracking-wider mb-1">Nomor Telepon</label>
                            <input v-model="form.phonenumber" type="text" :class="fieldClass('phonenumber')" />
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 p-5">
                        <h3 class="text-sm font-semibold text-gray-900">Ganti Password</h3>
                        <p class="text-xs text-gray-500">Kosongkan jika tidak ingin mengubah password.</p>
                    </div>
                    <div class="space-y-4 p-5">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 uppercase tracking-wider mb-1">Password Saat Ini</label>
                            <input v-model="form.current_password" type="password" :class="fieldClass('current_password')" autocomplete="current-password" />
                            <p v-if="form.errors.current_password" class="mt-1 text-xs text-red-600">{{ form.errors.current_password }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 uppercase tracking-wider mb-1">Password Baru</label>
                                <input v-model="form.password" type="password" :class="fieldClass('password')" autocomplete="new-password" />
                                <p v-if="form.errors.password" class="mt-1 text-xs text-red-600">{{ form.errors.password }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 uppercase tracking-wider mb-1">Konfirmasi Password</label>
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
        </div>
    </div>
</template>
