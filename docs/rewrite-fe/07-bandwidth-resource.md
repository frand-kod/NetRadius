# Task 07 — Bandwidth Resource (CRUD)

**Tujuan**: CRUD admin untuk `tbl_bandwidth`. Resource simpel kedua.

**Dependensi**: `00-setup.md`, `01-admin-auth.md`

**Waktu estimasi**: 25-30 menit

---

## Skema DB

Tabel `tbl_bandwidth`:
```
id, name_bw, rate_down uint, rate_down_unit enum[Kbps,Mbps],
rate_up uint, rate_up_unit enum[Kbps,Mbps], burst(128) default ''
```

---

## Step 1: Controller

Buat `app/Http/Controllers/Admin/BandwidthController.php`:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bandwidth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BandwidthController extends Controller
{
    public function index(): \Inertia\Response
    {
        return Inertia::render('Admin/Bandwidth/Index', [
            'bandwidths' => Bandwidth::orderBy('id')->paginate(15),
        ]);
    }

    public function create(): \Inertia\Response
    {
        return Inertia::render('Admin/Bandwidth/Create');
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'name_bw' => ['required', 'string'],
            'rate_down' => ['required', 'integer', 'min:0'],
            'rate_down_unit' => ['required', 'in:Kbps,Mbps'],
            'rate_up' => ['required', 'integer', 'min:0'],
            'rate_up_unit' => ['required', 'in:Kbps,Mbps'],
            'burst' => ['required', 'string', 'max:128'],
        ]);

        Bandwidth::create($data);

        return redirect()->route('admin.bandwidths.index')->with('success', 'Bandwidth berhasil dibuat.');
    }

    public function edit(Bandwidth $bandwidth): \Inertia\Response
    {
        return Inertia::render('Admin/Bandwidth/Edit', [
            'bandwidth' => $bandwidth,
        ]);
    }

    public function update(Request $request, Bandwidth $bandwidth): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'name_bw' => ['required', 'string'],
            'rate_down' => ['required', 'integer', 'min:0'],
            'rate_down_unit' => ['required', 'in:Kbps,Mbps'],
            'rate_up' => ['required', 'integer', 'min:0'],
            'rate_up_unit' => ['required', 'in:Kbps,Mbps'],
            'burst' => ['required', 'string', 'max:128'],
        ]);

        $bandwidth->update($data);

        return redirect()->route('admin.bandwidths.index')->with('success', 'Bandwidth berhasil diperbarui.');
    }

    public function destroy(Bandwidth $bandwidth): \Illuminate\Http\RedirectResponse
    {
        $bandwidth->delete();
        return redirect()->route('admin.bandwidths.index')->with('success', 'Bandwidth berhasil dihapus.');
    }
}
```

## Step 2: Routes

Di `routes/web.php`:

```php
use App\Http\Controllers\Admin\BandwidthController;

Route::middleware('auth:web')->prefix('admin/bandwidths')->name('admin.bandwidths.')->group(function () {
    Route::get('/', [BandwidthController::class, 'index'])->name('index');
    Route::get('/create', [BandwidthController::class, 'create'])->name('create');
    Route::post('/', [BandwidthController::class, 'store'])->name('store');
    Route::get('/{bandwidth}/edit', [BandwidthController::class, 'edit'])->name('edit');
    Route::put('/{bandwidth}', [BandwidthController::class, 'update'])->name('update');
    Route::delete('/{bandwidth}', [BandwidthController::class, 'destroy'])->name('destroy');
});
```

## Step 3: Vue Pages

### Index

Buat `resources/js/Pages/Admin/Bandwidth/Index.vue`:

```vue
<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({ bandwidths: Object });
</script>

<template>
    <AdminLayout>
        <template #title>Bandwidth</template>

        <div class="mb-4">
            <Link href="/admin/bandwidths/create" class="bg-amber-600 text-white px-4 py-2 rounded hover:bg-amber-700">
                + Tambah Bandwidth
            </Link>
        </div>

        <div class="bg-white rounded shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-medium">Name</th>
                        <th class="px-4 py-2 text-left text-sm font-medium">Rate Down</th>
                        <th class="px-4 py-2 text-left text-sm font-medium">Rate Up</th>
                        <th class="px-4 py-2 text-left text-sm font-medium">Burst</th>
                        <th class="px-4 py-2 text-right text-sm font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="bw in bandwidths.data" :key="bw.id" class="border-t hover:bg-gray-50">
                        <td class="px-4 py-2">{{ bw.name_bw }}</td>
                        <td class="px-4 py-2">{{ bw.rate_down }} {{ bw.rate_down_unit }}</td>
                        <td class="px-4 py-2">{{ bw.rate_up }} {{ bw.rate_up_unit }}</td>
                        <td class="px-4 py-2">{{ bw.burst }}</td>
                        <td class="px-4 py-2 text-right">
                            <Link :href="`/admin/bandwidths/${bw.id}/edit`" class="text-blue-600 hover:underline mr-2">Edit</Link>
                            <Link :href="`/admin/bandwidths/${bw.id}`" method="delete" as="button" class="text-red-600 hover:underline">Delete</Link>
                        </td>
                    </tr>
                    <tr v-if="bandwidths.data.length === 0">
                        <td colspan="5" class="px-4 py-4 text-center text-gray-500">Belum ada bandwidth.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination (sama seperti di Router/Index.vue) -->
        <div v-if="bandwidths.links" class="mt-4 flex justify-center gap-2">
            <Link v-for="link in bandwidths.links" :key="link.label" :href="link.url || '#'"
                  v-html="link.label"
                  class="px-3 py-1 rounded border"
                  :class="{ 'bg-gray-200': link.active, 'opacity-50 pointer-events-none': !link.url }" />
        </div>
    </AdminLayout>
</template>
```

### Create

Buat `resources/js/Pages/Admin/Bandwidth/Create.vue`:

```vue
<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const form = useForm({
    name_bw: '',
    rate_down: 0,
    rate_down_unit: 'Kbps',
    rate_up: 0,
    rate_up_unit: 'Kbps',
    burst: '',
});

function submit() { form.post('/admin/bandwidths'); }
</script>

<template>
    <AdminLayout>
        <template #title>Add Bandwidth</template>
        <form @submit.prevent="submit" class="bg-white rounded shadow p-6 max-w-lg space-y-4">
            <div>
                <label class="block text-sm font-medium">Name *</label>
                <input v-model="form.name_bw" required class="mt-1 block w-full rounded border px-3 py-2" />
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium">Rate Down *</label>
                    <input v-model.number="form.rate_down" type="number" min="0" required class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Unit</label>
                    <select v-model="form.rate_down_unit" class="mt-1 block w-full rounded border px-3 py-2">
                        <option value="Kbps">Kbps</option>
                        <option value="Mbps">Mbps</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium">Rate Up *</label>
                    <input v-model.number="form.rate_up" type="number" min="0" required class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Unit</label>
                    <select v-model="form.rate_up_unit" class="mt-1 block w-full rounded border px-3 py-2">
                        <option value="Kbps">Kbps</option>
                        <option value="Mbps">Mbps</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium">Burst *</label>
                <input v-model="form.burst" required class="mt-1 block w-full rounded border px-3 py-2" />
            </div>
            <button type="submit" :disabled="form.processing"
                    class="bg-amber-600 text-white px-6 py-2 rounded hover:bg-amber-700 disabled:opacity-50">
                Simpan
            </button>
        </form>
    </AdminLayout>
</template>
```

### Edit

Buat `resources/js/Pages/Admin/Bandwidth/Edit.vue`:

```vue
<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ bandwidth: Object });

const form = useForm({
    name_bw: props.bandwidth.name_bw,
    rate_down: Number(props.bandwidth.rate_down),
    rate_down_unit: props.bandwidth.rate_down_unit,
    rate_up: Number(props.bandwidth.rate_up),
    rate_up_unit: props.bandwidth.rate_up_unit,
    burst: props.bandwidth.burst,
});

function submit() { form.put(`/admin/bandwidths/${props.bandwidth.id}`); }
</script>

<template>
    <AdminLayout>
        <template #title>Edit Bandwidth: {{ bandwidth.name_bw }}</template>
        <form @submit.prevent="submit" class="bg-white rounded shadow p-6 max-w-lg space-y-4">
            <!-- Same fields as Create, but pre-filled -->
            <div>
                <label class="block text-sm font-medium">Name *</label>
                <input v-model="form.name_bw" required class="mt-1 block w-full rounded border px-3 py-2" />
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium">Rate Down *</label>
                    <input v-model.number="form.rate_down" type="number" min="0" required class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Unit</label>
                    <select v-model="form.rate_down_unit" class="mt-1 block w-full rounded border px-3 py-2">
                        <option value="Kbps">Kbps</option>
                        <option value="Mbps">Mbps</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium">Rate Up *</label>
                    <input v-model.number="form.rate_up" type="number" min="0" required class="mt-1 block w-full rounded border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Unit</label>
                    <select v-model="form.rate_up_unit" class="mt-1 block w-full rounded border px-3 py-2">
                        <option value="Kbps">Kbps</option>
                        <option value="Mbps">Mbps</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium">Burst *</label>
                <input v-model="form.burst" required class="mt-1 block w-full rounded border px-3 py-2" />
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

1. `/admin/bandwidths` → list bandwidth
2. Create → form tampil, submit → redirect ke list
3. Edit → data existing tampil, ubah, submit → list terupdate
4. Delete → bandwidth hilang
