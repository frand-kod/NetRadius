# Task 06 — Router Resource (CRUD)

**Tujuan**: CRUD admin untuk `tbl_routers`. Resource paling simpel — pemanasan.

**Dependensi**: `00-setup.md`, `01-admin-auth.md`

**Waktu estimasi**: 30-45 menit

---

## Skema DB

Tabel `tbl_routers`:
```
id, name(32), ip_address(128), username(50), password(60),
description(256) nullable, coordinates(50),
status enum[Online,Offline] default Online, last_seen datetime nullable,
coverage(8), enabled boolean default true
```

> Password router di-hidden (`$hidden = ['password']` di model).

---

## Step 1: Controller

Buat `app/Http/Controllers/Admin/RouterController.php`:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Router;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RouterController extends Controller
{
    public function index(): \Inertia\Response
    {
        return Inertia::render('Admin/Router/Index', [
            'routers' => Router::orderBy('id')->paginate(15),
        ]);
    }

    public function create(): \Inertia\Response
    {
        return Inertia::render('Admin/Router/Create');
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:32'],
            'ip_address' => ['required', 'string', 'max:128'],
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:256'],
            'coordinates' => ['required', 'string', 'max:50'],
            'status' => ['required', 'in:Online,Offline'],
            'last_seen' => ['nullable', 'date'],
            'coverage' => ['required', 'string', 'max:8'],
            'enabled' => ['boolean'],
        ]);

        Router::create($data);

        return redirect()->route('admin.routers.index')->with('success', 'Router berhasil dibuat.');
    }

    public function edit(Router $router): \Inertia\Response
    {
        return Inertia::render('Admin/Router/Edit', [
            'router' => $router,
        ]);
    }

    public function update(Request $request, Router $router): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:32'],
            'ip_address' => ['required', 'string', 'max:128'],
            'username' => ['required', 'string', 'max:50'],
            'password' => ['nullable', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:256'],
            'coordinates' => ['required', 'string', 'max:50'],
            'status' => ['required', 'in:Online,Offline'],
            'last_seen' => ['nullable', 'date'],
            'coverage' => ['required', 'string', 'max:8'],
            'enabled' => ['boolean'],
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $router->update($data);

        return redirect()->route('admin.routers.index')->with('success', 'Router berhasil diperbarui.');
    }

    public function destroy(Router $router): \Illuminate\Http\RedirectResponse
    {
        $router->delete();
        return redirect()->route('admin.routers.index')->with('success', 'Router berhasil dihapus.');
    }
}
```

## Step 2: Routes

Di `routes/web.php`:

```php
use App\Http\Controllers\Admin\RouterController;

Route::middleware('auth:web')->prefix('admin/routers')->name('admin.routers.')->group(function () {
    Route::get('/', [RouterController::class, 'index'])->name('index');
    Route::get('/create', [RouterController::class, 'create'])->name('create');
    Route::post('/', [RouterController::class, 'store'])->name('store');
    Route::get('/{router}/edit', [RouterController::class, 'edit'])->name('edit');
    Route::put('/{router}', [RouterController::class, 'update'])->name('update');
    Route::delete('/{router}', [RouterController::class, 'destroy'])->name('destroy');
});
```

## Step 3: Vue Pages

### Index Page

Buat `resources/js/Pages/Admin/Router/Index.vue`:

```vue
<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({ routers: Object });
</script>

<template>
    <AdminLayout>
        <template #title>Routers</template>

        <div class="mb-4">
            <Link href="/admin/routers/create" class="bg-amber-600 text-white px-4 py-2 rounded hover:bg-amber-700">
                + Tambah Router
            </Link>
        </div>

        <div class="bg-white rounded shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-medium">Name</th>
                        <th class="px-4 py-2 text-left text-sm font-medium">IP Address</th>
                        <th class="px-4 py-2 text-left text-sm font-medium">Status</th>
                        <th class="px-4 py-2 text-left text-sm font-medium">Coverage</th>
                        <th class="px-4 py-2 text-left text-sm font-medium">Enabled</th>
                        <th class="px-4 py-2 text-right text-sm font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="router in routers.data" :key="router.id" class="border-t hover:bg-gray-50">
                        <td class="px-4 py-2">{{ router.name }}</td>
                        <td class="px-4 py-2">{{ router.ip_address }}</td>
                        <td class="px-4 py-2">
                            <span :class="router.status === 'Online' ? 'text-green-600' : 'text-red-600'">
                                {{ router.status }}
                            </span>
                        </td>
                        <td class="px-4 py-2">{{ router.coverage }}</td>
                        <td class="px-4 py-2">{{ router.enabled ? 'Yes' : 'No' }}</td>
                        <td class="px-4 py-2 text-right">
                            <Link :href="`/admin/routers/${router.id}/edit`"
                                  class="text-blue-600 hover:underline mr-2">Edit</Link>
                            <Link :href="`/admin/routers/${router.id}`" method="delete" as="button"
                                  class="text-red-600 hover:underline">Delete</Link>
                        </td>
                    </tr>
                    <tr v-if="routers.data.length === 0">
                        <td colspan="6" class="px-4 py-4 text-center text-gray-500">Belum ada router.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="routers.links" class="mt-4 flex justify-center gap-2">
            <Link v-for="link in routers.links" :key="link.label" :href="link.url || '#'"
                  v-html="link.label"
                  class="px-3 py-1 rounded border"
                  :class="{ 'bg-gray-200': link.active, 'opacity-50 pointer-events-none': !link.url }" />
        </div>
    </AdminLayout>
</template>
```

### Create Page

Buat `resources/js/Pages/Admin/Router/Create.vue`:

```vue
<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const form = useForm({
    name: '',
    ip_address: '',
    username: '',
    password: '',
    description: '',
    coordinates: '',
    status: 'Online',
    last_seen: '',
    coverage: '',
    enabled: true,
});

function submit() {
    form.post('/admin/routers');
}
</script>

<template>
    <AdminLayout>
        <template #title>Add Router</template>
        <form @submit.prevent="submit" class="bg-white rounded shadow p-6 max-w-2xl space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium">Name *</label>
                    <input v-model="form.name" required class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">IP Address *</label>
                    <input v-model="form.ip_address" required class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Username *</label>
                    <input v-model="form.username" required class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Password *</label>
                    <input v-model="form.password" type="password" required class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Coordinates *</label>
                    <input v-model="form.coordinates" required class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Status</label>
                    <select v-model="form.status" class="mt-1 block w-full rounded border px-3 py-2">
                        <option value="Online">Online</option>
                        <option value="Offline">Offline</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium">Coverage *</label>
                    <input v-model="form.coverage" required class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div class="flex items-center gap-2">
                    <input v-model="form.enabled" type="checkbox" class="rounded" />
                    <label class="text-sm font-medium">Enabled</label>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium">Description</label>
                <textarea v-model="form.description" rows="3" class="mt-1 block w-full rounded border px-3 py-2"></textarea>
            </div>
            <button type="submit" :disabled="form.processing"
                    class="bg-amber-600 text-white px-6 py-2 rounded hover:bg-amber-700 disabled:opacity-50">
                Simpan
            </button>
        </form>
    </AdminLayout>
</template>
```

### Edit Page

Buat `resources/js/Pages/Admin/Router/Edit.vue`:

```vue
<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ router: Object });

const form = useForm({
    name: props.router.name,
    ip_address: props.router.ip_address,
    username: props.router.username,
    password: '',
    description: props.router.description || '',
    coordinates: props.router.coordinates,
    status: props.router.status,
    last_seen: props.router.last_seen || '',
    coverage: props.router.coverage,
    enabled: Boolean(props.router.enabled),
});

function submit() {
    form.put(`/admin/routers/${props.router.id}`);
}
</script>

<template>
    <AdminLayout>
        <template #title>Edit Router: {{ router.name }}</template>
        <form @submit.prevent="submit" class="bg-white rounded shadow p-6 max-w-2xl space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium">Name *</label>
                    <input v-model="form.name" required class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">IP Address *</label>
                    <input v-model="form.ip_address" required class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Username *</label>
                    <input v-model="form.username" required class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Password (kosongkan jika tidak berubah)</label>
                    <input v-model="form.password" type="password" class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Coordinates *</label>
                    <input v-model="form.coordinates" required class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Status</label>
                    <select v-model="form.status" class="mt-1 block w-full rounded border px-3 py-2">
                        <option value="Online">Online</option>
                        <option value="Offline">Offline</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium">Coverage *</label>
                    <input v-model="form.coverage" required class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div class="flex items-center gap-2">
                    <input v-model="form.enabled" type="checkbox" class="rounded" />
                    <label class="text-sm font-medium">Enabled</label>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium">Description</label>
                <textarea v-model="form.description" rows="3" class="mt-1 block w-full rounded border px-3 py-2"></textarea>
            </div>
            <button type="submit" :disabled="form.processing"
                    class="bg-amber-600 text-white px-6 py-2 rounded hover:bg-amber-700 disabled:opacity-50">
                Update
            </button>
        </form>
    </AdminLayout>
</template>
```

## Verifikasi

1. Buka `/admin/routers` → list router
2. Klik "Tambah Router" → form create tampil
3. Isi form, submit → redirect ke list, router baru muncul
4. Klik "Edit" → form edit tampil dengan data existing
5. Ubah data, submit → list terupdate
6. Klik "Delete" → router hilang dari list
