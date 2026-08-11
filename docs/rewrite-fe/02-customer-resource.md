# Task 02 — Customer Resource (CRUD)

**Tujuan**: CRUD admin untuk `tbl_customers`. Resource terbesar — 22 form fields, 20 table columns.

**Dependensi**: `00-setup.md`, `01-admin-auth.md`, `06-router-resource.md` (DataTable pattern)

**Waktu estimasi**: 1.5 jam

---

## ⚠️ KRITIS: Password Customer PLAINTEXT

Password customer disimpan **plaintext** (bukan hash) karena RADIUS PAP/CHAP butuh password asli. Saat create, simpan apa adanya. Saat edit, kalau field password kosong → jangan overwrite.

---

## Skema DB

Tabel `tbl_customers`:
```
id, username(45) unique, password(255) -- PLAINTEXT!,
photo(128) default '/user.default.jpg', pppoe_username(32),
pppoe_password(45), pppoe_ip(32), fullname(45),
address text nullable, city, district, state, zip(10),
phonenumber(20) default '0', email(128), coordinates(50),
account_type enum[Business,Personal], balance decimal(15,2),
service_type enum[Hotspot,PPPoE,VPN,Others],
auto_renewal boolean, status enum[Active,Banned,Disabled,Inactive,Limited,Suspended],
created_by uint, created_at, last_login datetime nullable
```

---

## Step 1: Controller

Buat `app/Http/Controllers/Admin/CustomerController.php`:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index(Request $request): \Inertia\Response
    {
        $query = Customer::query();

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('username', 'like', "%{$s}%")
                  ->orWhere('fullname', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('phonenumber', 'like', "%{$s}%");
            });
        }

        if ($request->filled('sort')) {
            $query->orderBy($request->input('sort'), $request->input('direction', 'asc'));
        } else {
            $query->orderBy('id', 'desc');
        }

        return Inertia::render('Admin/Customer/Index', [
            'customers' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['search', 'sort', 'direction']),
        ]);
    }

    public function create(): \Inertia\Response
    {
        return Inertia::render('Admin/Customer/Create');
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:45', 'unique:tbl_customers,username'],
            'password' => ['required', 'string', 'max:255'], // PLAINTEXT - jangan hash!
            'photo' => ['required', 'string', 'max:128'],
            'pppoe_username' => ['required', 'string', 'max:32'],
            'pppoe_password' => ['required', 'string', 'max:45'],
            'pppoe_ip' => ['required', 'string', 'max:32'],
            'fullname' => ['required', 'string', 'max:45'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
            'district' => ['nullable', 'string'],
            'state' => ['nullable', 'string'],
            'zip' => ['nullable', 'string', 'max:10'],
            'phonenumber' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:128'],
            'coordinates' => ['required', 'string', 'max:50'],
            'account_type' => ['required', 'in:Business,Personal'],
            'balance' => ['required', 'numeric', 'min:0'],
            'service_type' => ['required', 'in:Hotspot,PPPoE,VPN,Others'],
            'auto_renewal' => ['boolean'],
            'status' => ['required', 'in:Active,Banned,Disabled,Inactive,Limited,Suspended'],
            'created_by' => ['required', 'integer'],
        ]);

        // JANGAN hash password
        Customer::create($data);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer berhasil dibuat.');
    }

    public function edit(Customer $customer): \Inertia\Response
    {
        return Inertia::render('Admin/Customer/Edit', [
            'customer' => $customer,
        ]);
    }

    public function update(Request $request, Customer $customer): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:45', 'unique:tbl_customers,username,'.$customer->id],
            'password' => ['nullable', 'string', 'max:255'], // optional on update
            'photo' => ['required', 'string', 'max:128'],
            'pppoe_username' => ['required', 'string', 'max:32'],
            'pppoe_password' => ['nullable', 'string', 'max:45'],
            'pppoe_ip' => ['required', 'string', 'max:32'],
            'fullname' => ['required', 'string', 'max:45'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
            'district' => ['nullable', 'string'],
            'state' => ['nullable', 'string'],
            'zip' => ['nullable', 'string', 'max:10'],
            'phonenumber' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:128'],
            'coordinates' => ['required', 'string', 'max:50'],
            'account_type' => ['required', 'in:Business,Personal'],
            'balance' => ['required', 'numeric', 'min:0'],
            'service_type' => ['required', 'in:Hotspot,PPPoE,VPN,Others'],
            'auto_renewal' => ['boolean'],
            'status' => ['required', 'in:Active,Banned,Disabled,Inactive,Limited,Suspended'],
            'created_by' => ['required', 'integer'],
        ]);

        // JANGAN overwrite password kalau field kosong
        if (empty($data['password'])) {
            unset($data['password']);
        }
        if (empty($data['pppoe_password'])) {
            unset($data['pppoe_password']);
        }

        $customer->update($data);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer berhasil diperbarui.');
    }

    public function destroy(Customer $customer): \Illuminate\Http\RedirectResponse
    {
        $customer->delete();
        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer berhasil dihapus.');
    }
}
```

## Step 2: Routes

Di `routes/web.php`:

```php
use App\Http\Controllers\Admin\CustomerController;

Route::middleware('auth:web')->prefix('admin/customers')->name('admin.customers.')->group(function () {
    Route::get('/', [CustomerController::class, 'index'])->name('index');
    Route::get('/create', [CustomerController::class, 'create'])->name('create');
    Route::post('/', [CustomerController::class, 'store'])->name('store');
    Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('edit');
    Route::put('/{customer}', [CustomerController::class, 'update'])->name('update');
    Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('destroy');
});
```

## Step 3: Vue Pages

### Index — `resources/js/Pages/Admin/Customer/Index.vue`

```vue
<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { watch, ref, computed } from 'vue';

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

        <!-- Pagination links (same pattern as Router/Index.vue) -->
        <div v-if="customers.links" class="mt-4 flex justify-center gap-2">
            <Link v-for="link in customers.links" :key="link.label" :href="link.url || '#'"
                  v-html="link.label"
                  class="px-3 py-1 rounded border text-sm"
                  :class="{ 'bg-gray-200': link.active, 'opacity-50 pointer-events-none': !link.url }" />
        </div>
    </AdminLayout>
</template>
```

### Create — `resources/js/Pages/Admin/Customer/Create.vue`

```vue
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
            <!-- Row 1 -->
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

            <!-- Row 2 -->
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

            <!-- Row 3: PPPoE -->
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

            <!-- Row 4: Location -->
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

            <!-- Row 5: Account -->
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

            <!-- Row 6 -->
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

            <!-- Address -->
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
```

### Edit — `resources/js/Pages/Admin/Customer/Edit.vue`

```vue
<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ customer: Object });

const form = useForm({
    username: props.customer.username,
    password: '', // KOSONGKAN — hanya diisi kalau mau reset
    photo: props.customer.photo,
    pppoe_username: props.customer.pppoe_username,
    pppoe_password: '', // KOSONGKAN
    pppoe_ip: props.customer.pppoe_ip,
    fullname: props.customer.fullname,
    address: props.customer.address || '',
    city: props.customer.city || '',
    district: props.customer.district || '',
    state: props.customer.state || '',
    zip: props.customer.zip || '',
    phonenumber: props.customer.phonenumber,
    email: props.customer.email,
    coordinates: props.customer.coordinates,
    account_type: props.customer.account_type,
    balance: Number(props.customer.balance),
    service_type: props.customer.service_type,
    auto_renewal: Boolean(props.customer.auto_renewal),
    status: props.customer.status,
    created_by: Number(props.customer.created_by),
});

function submit() { form.put(`/admin/customers/${props.customer.id}`); }
</script>

<template>
    <AdminLayout>
        <template #title>Edit Customer: {{ customer.username }}</template>
        <!-- Same form fields as Create, but pre-filled. Password fields show placeholder text -->
        <form @submit.prevent="submit" class="bg-white rounded shadow p-6 max-w-3xl space-y-4">
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium">Username *</label>
                    <input v-model="form.username" required class="mt-1 block w-full rounded border px-3 py-2" />
                    <p v-if="form.errors.username" class="text-red-500 text-xs mt-1">{{ form.errors.username }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium">Password (kosongkan jika tidak berubah)</label>
                    <input v-model="form.password" type="password" class="mt-1 block w-full rounded border px-3 py-2"
                           placeholder="Biarkan kosong..." />
                </div>
                <div>
                    <label class="block text-sm font-medium">Fullname *</label>
                    <input v-model="form.fullname" required class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
            </div>
            <!-- ... copy remaining fields from Create.vue, all pre-filled from form ... -->
            <!-- (Same grid rows as Create — email/phone/photo, PPPoE, location, account/service/status, balance/coord/auto_renewal, address) -->

            <button type="submit" :disabled="form.processing"
                    class="bg-amber-600 text-white px-6 py-2 rounded hover:bg-amber-700 disabled:opacity-50">
                Update
            </button>
        </form>
    </AdminLayout>
</template>
```

> **NOTE**: Untuk Edit.vue, copy SEMUA field rows dari Create.vue dan ganti `v-model` ke `form.*`. Field password dan pppoe_password dikosongkan dengan placeholder.

## Verifikasi

1. `/admin/customers` → list customer dengan pagination + search
2. Create → isi semua field, submit → redirect ke list, customer baru muncul
3. Cek DB: `SELECT password FROM tbl_customers WHERE username = '...'` → password TERSIMPAN PLAINTEXT (bukan hash)
4. Edit → password dikosongkan, submit → password TIDAK berubah (cek DB)
5. Delete → customer hilang
