# 15-settings-pages.md — Halaman Settings (General, Payment, Notification)

Backend **SUDAH SELESAI** (controller + route + seeder). Task ini **HANYA bagian tampilan** (Vue).
Kerjakan SETELAH membaca `12-styling-system.md` (gunakan recipe R1, R2, R6, R7, R8, R16).

3 halaman + 1 penambahan sidebar:

1. `Pages/Admin/Settings/General.vue` — **BARU** (buat)
2. `Pages/Admin/Settings/Payment.vue` — **GANTI** (edit) — tambah field instruksi
3. `Pages/Admin/Settings/Notification.vue` — **GANTI** (edit) — rapikan tampilan
4. `Layouts/AdminLayout.vue` — **EDIT** — tambah link "Pengaturan Umum"

> JANGAN ubah controller/route/backend. Hanya `.vue` file.

---

## 0. Kontrak Backend (ini yang diterima tiap halaman)

| Halaman | Props yang diterima | Endpoint POST |
|---------|---------------------|---------------|
| General | `settings: Object` → `company_name, company_address, company_phone, company_email, currency_symbol, currency_code` | `/admin/settings/general` |
| Payment | `qrPath: String`, `paymentInstructions: String` | `/admin/settings/payment` (wajib `forceFormData: true` karena ada upload file) |
| Notification | `settings: Object` → `telegram_bot, telegram_target_id, alt_wga_server_url, alt_wga_device_id, alt_wga_username, alt_wga_password, country_code_phone` | `/admin/settings/notification` |

Semua halaman pakai `useForm` dari `@inertiajs/vue3`. Inisialisasi form dari prop (fallback `|| ''`).

---

## 1. `resources/js/Pages/Admin/Settings/General.vue` (BUAT BARU)

```vue
<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ settings: Object });

const form = useForm({
    company_name: props.settings?.company_name || '',
    company_address: props.settings?.company_address || '',
    company_phone: props.settings?.company_phone || '',
    company_email: props.settings?.company_email || '',
    currency_symbol: props.settings?.currency_symbol || 'Rp',
    currency_code: props.settings?.currency_code || 'IDR',
});

function submit() {
    form.post('/admin/settings/general');
}
</script>

<template>
    <AdminLayout>
        <template #title>Pengaturan Umum</template>

        <form @submit.prevent="submit"
              class="max-w-2xl space-y-5 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

            <div>
                <h3 class="text-sm font-semibold text-gray-900">Identitas Perusahaan</h3>
                <p class="mt-1 text-xs text-gray-500">Ditampilkan pada invoice dan portal publik.</p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Nama Perusahaan</label>
                    <input v-model="form.company_name" type="text"
                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25" />
                    <p v-if="form.errors.company_name" class="mt-1 text-xs text-red-600">{{ form.errors.company_name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Telepon</label>
                    <input v-model="form.company_phone" type="text"
                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25" />
                    <p v-if="form.errors.company_phone" class="mt-1 text-xs text-red-600">{{ form.errors.company_phone }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input v-model="form.company_email" type="email"
                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25" />
                    <p v-if="form.errors.company_email" class="mt-1 text-xs text-red-600">{{ form.errors.company_email }}</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Alamat</label>
                <textarea v-model="form.company_address" rows="3"
                          class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25"></textarea>
                <p v-if="form.errors.company_address" class="mt-1 text-xs text-red-600">{{ form.errors.company_address }}</p>
            </div>

            <div class="border-t border-gray-100 pt-5">
                <h3 class="text-sm font-semibold text-gray-900">Mata Uang</h3>
                <p class="mt-1 text-xs text-gray-500">Digunakan untuk format harga di seluruh aplikasi.</p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Simbol (contoh: Rp)</label>
                    <input v-model="form.currency_symbol" type="text" maxlength="10"
                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25" />
                    <p v-if="form.errors.currency_symbol" class="mt-1 text-xs text-red-600">{{ form.errors.currency_symbol }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Kode (contoh: IDR)</label>
                    <input v-model="form.currency_code" type="text" maxlength="10"
                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25" />
                    <p v-if="form.errors.currency_code" class="mt-1 text-xs text-red-600">{{ form.errors.currency_code }}</p>
                </div>
            </div>

            <button type="submit" :disabled="form.processing"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500/40 disabled:cursor-not-allowed disabled:opacity-60">
                {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
            </button>
        </form>
    </AdminLayout>
</template>
```

---

## 2. `resources/js/Pages/Admin/Settings/Payment.vue` (GANTI — timpa penuh)

```vue
<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ qrPath: String, paymentInstructions: String });

const form = useForm({
    payment_qr: null,
    payment_instructions: props.paymentInstructions || '',
});

function submit() {
    form.post('/admin/settings/payment', { forceFormData: true });
}
</script>

<template>
    <AdminLayout>
        <template #title>Pengaturan Pembayaran</template>

        <form @submit.prevent="submit"
              class="max-w-2xl space-y-5 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

            <div>
                <h3 class="text-sm font-semibold text-gray-900">QR Pembayaran</h3>
                <p class="mt-1 text-xs text-gray-500">QRIS / QR pembayaran yang tampil pada invoice. Kosongkan field untuk membiarkan QR lama.</p>
            </div>

            <div class="flex items-start gap-4">
                <img v-if="qrPath" :src="`/storage/${qrPath}`" alt="QR"
                     class="w-40 rounded-lg border border-gray-200 object-cover" />
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700">Upload QR Baru</label>
                    <input type="file" accept="image/*" @input="form.payment_qr = $event.target.files[0]"
                           class="mt-1 block w-full text-sm text-gray-700" />
                    <p v-if="form.errors.payment_qr" class="mt-1 text-xs text-red-600">{{ form.errors.payment_qr }}</p>
                    <p v-if="!qrPath" class="mt-2 text-xs text-gray-500">Belum ada QR. Silakan upload.</p>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-5">
                <h3 class="text-sm font-semibold text-gray-900">Instruksi Pembayaran</h3>
                <p class="mt-1 text-xs text-gray-500">Teks yang ditampilkan di halaman invoice untuk pelanggan.</p>
            </div>

            <div>
                <textarea v-model="form.payment_instructions" rows="4"
                          class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25"
                          placeholder="Contoh: Transfer ke rekening 1234 an. Nama, lalu konfirmasi ke WA 0812..."></textarea>
                <p v-if="form.errors.payment_instructions" class="mt-1 text-xs text-red-600">{{ form.errors.payment_instructions }}</p>
            </div>

            <button type="submit" :disabled="form.processing"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500/40 disabled:cursor-not-allowed disabled:opacity-60">
                {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
            </button>
        </form>
    </AdminLayout>
</template>
```

---

## 3. `resources/js/Pages/Admin/Settings/Notification.vue` (GANTI — timpa penuh)

```vue
<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ settings: Object });

const form = useForm({
    telegram_bot: props.settings?.telegram_bot || '',
    telegram_target_id: props.settings?.telegram_target_id || '',
    alt_wga_server_url: props.settings?.alt_wga_server_url || '',
    alt_wga_device_id: props.settings?.alt_wga_device_id || '',
    alt_wga_username: props.settings?.alt_wga_username || '',
    alt_wga_password: props.settings?.alt_wga_password || '',
    country_code_phone: props.settings?.country_code_phone || '62',
});

function submit() {
    form.post('/admin/settings/notification');
}

const telegramKeys = ['telegram_bot', 'telegram_target_id'];
const gowaKeys = ['alt_wga_server_url', 'alt_wga_device_id', 'alt_wga_username', 'alt_wga_password'];
</script>

<template>
    <AdminLayout>
        <template #title>Pengaturan Notifikasi</template>

        <form @submit.prevent="submit"
              class="max-w-2xl space-y-5 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

            <!-- Telegram -->
            <div>
                <h3 class="text-sm font-semibold text-gray-900">Telegram</h3>
                <p class="mt-1 text-xs text-gray-500">Notifikasi dikirim ke bot Telegram. Kosongkan untuk menonaktifkan.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Bot Token</label>
                <input v-model="form.telegram_bot" type="password" autocomplete="off"
                       class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25" />
                <p v-if="form.errors.telegram_bot" class="mt-1 text-xs text-red-600">{{ form.errors.telegram_bot }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Chat ID Tujuan</label>
                <input v-model="form.telegram_target_id" type="text"
                       class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25" />
                <p v-if="form.errors.telegram_target_id" class="mt-1 text-xs text-red-600">{{ form.errors.telegram_target_id }}</p>
            </div>

            <!-- GOWA WhatsApp -->
            <div class="border-t border-gray-100 pt-5">
                <h3 class="text-sm font-semibold text-gray-900">WhatsApp Gateway (GOWA)</h3>
                <p class="mt-1 text-xs text-gray-500">Self-hosted gateway. Kosongkan URL server untuk menonaktifkan.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Server URL</label>
                <input v-model="form.alt_wga_server_url" type="text"
                       class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25" />
                <p v-if="form.errors.alt_wga_server_url" class="mt-1 text-xs text-red-600">{{ form.errors.alt_wga_server_url }}</p>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Device ID</label>
                    <input v-model="form.alt_wga_device_id" type="text"
                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25" />
                    <p v-if="form.errors.alt_wga_device_id" class="mt-1 text-xs text-red-600">{{ form.errors.alt_wga_device_id }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Username</label>
                    <input v-model="form.alt_wga_username" type="text"
                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25" />
                    <p v-if="form.errors.alt_wga_username" class="mt-1 text-xs text-red-600">{{ form.errors.alt_wga_username }}</p>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Password</label>
                <input v-model="form.alt_wga_password" type="password" autocomplete="off"
                       class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25" />
                <p v-if="form.errors.alt_wga_password" class="mt-1 text-xs text-red-600">{{ form.errors.alt_wga_password }}</p>
            </div>

            <!-- Lainnya -->
            <div class="border-t border-gray-100 pt-5">
                <h3 class="text-sm font-semibold text-gray-900">Format Nomor</h3>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Kode Negara (default: 62)</label>
                <input v-model="form.country_code_phone" type="text" maxlength="5"
                       class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/25" />
                <p v-if="form.errors.country_code_phone" class="mt-1 text-xs text-red-600">{{ form.errors.country_code_phone }}</p>
            </div>

            <button type="submit" :disabled="form.processing"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500/40 disabled:cursor-not-allowed disabled:opacity-60">
                {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
            </button>
        </form>
    </AdminLayout>
</template>
```

> Variabel `telegramKeys` dan `gowaKeys` di atas TIDAK dipakai di template (tidak wajib). Boleh dihapus jika membuat error lint — hanya contoh struktur. Pastikan form menyertakan SEMUA 7 field.

---

## 4. `resources/js/Layouts/AdminLayout.vue` — Tambah Link "Pengaturan Umum"

Di sidebar, **sebelum** link "Payment Settings", sisipkan link General. Cari blok ini:

```html
<Link href="/admin/settings/payment" ...>
    Payment Settings
</Link>
```

Sisipkan **di atasnya**:

```html
<Link href="/admin/settings/general"
      class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition"
      :class="page.url.startsWith('/admin/settings/general') ? 'bg-amber-50 text-amber-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'">
    Pengaturan Umum
</Link>
```

Urutan akhir di sidebar bagian Settings:
1. Pengaturan Umum → `/admin/settings/general`
2. Payment Settings → `/admin/settings/payment`
3. Notification Settings → `/admin/settings/notification`

---

## 5. (Opsional) Gunakan Setting Global di Tampilan

Semua halaman punya `$page.props.settings` berisi `company_name` dan `currency_symbol`
(dishare backend). Contoh memakai di komponen apa pun:

```vue
<script setup>
import { usePage } from '@inertiajs/vue3';
const page = usePage();
const company = page.props.settings?.company_name || 'PHPNuxBill';
</script>
```

Bisa dipakai untuk mengganti teks "PHPNuxBill" di header/sidebar **jika diinginkan**, tapi tidak wajib.

---

## 6. Verifikasi

1. `npm run build` → berhasil.
2. Login admin, buka `/admin/settings/general` → form Identitas Perusahaan + Mata Uang tampil, Simpan bekerja, flash sukses muncul.
3. `/admin/settings/payment` → QR + instruksi tampil & tersimpan.
4. `/admin/settings/notification` → 7 field tampil & tersimpan.
5. Sidebar menampilkan 3 menu Settings.
6. Tidak ada error konsol.

## 7. Jangan Sentuh
- JANGAN ubah controller/route (`GeneralSettingsController`, `PaymentSettingsController`, `NotificationSettingsController`, `routes/web.php`).
- JANGAN ubah backend service.
- Hanya `.vue` file + AdminLayout.
