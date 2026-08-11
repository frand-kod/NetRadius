# Task 05 — Voucher Resource (CRUD + Generate + Print)

**Tujuan**: CRUD voucher + header action "Generate Vouchers" (modal) + bulk action "Print Selected" (new tab).

**Dependensi**: `00-setup.md`, `01-admin-auth.md`

**Waktu estimasi**: 1 jam

---

## Skema DB

Tabel `tbl_voucher`:
```
id, type enum[Hotspot,PPPOE], routers(32), id_plan FK→tbl_plans,
code(55) unique, user(45), status(25), created_at,
used_date datetime nullable, generated_by uint
```

---

## Step 1: Controller

Buat `app/Http/Controllers/Admin/VoucherController.php`:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Voucher;
use App\Services\VoucherService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VoucherController extends Controller
{
    public function index(Request $request): \Inertia\Response
    {
        $query = Voucher::with('plan');

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where('code', 'like', "%{$s}%")
                  ->orWhereHas('plan', fn ($q) => $q->where('name_plan', 'like', "%{$s}%"));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $query->orderBy('id', 'desc');

        return Inertia::render('Admin/Voucher/Index', [
            'vouchers' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(): \Inertia\Response
    {
        return Inertia::render('Admin/Voucher/Create', [
            'plans' => Plan::orderBy('name_plan')->get(['id', 'name_plan']),
        ]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:Hotspot,PPPOE'],
            'routers' => ['required', 'string', 'max:32'],
            'id_plan' => ['required', 'exists:tbl_plans,id'],
            'code' => ['required', 'string', 'max:55', 'unique:tbl_voucher,code'],
            'user' => ['nullable', 'string', 'max:45'],
            'status' => ['required', 'in:0,1'],
            'used_date' => ['nullable', 'date'],
            'generated_by' => ['nullable', 'integer'],
        ]);

        Voucher::create($data);

        return redirect()->route('admin.vouchers.index')->with('success', 'Voucher berhasil dibuat.');
    }

    public function edit(Voucher $voucher): \Inertia\Response
    {
        return Inertia::render('Admin/Voucher/Edit', [
            'voucher' => $voucher,
            'plans' => Plan::orderBy('name_plan')->get(['id', 'name_plan']),
        ]);
    }

    public function update(Request $request, Voucher $voucher): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:Hotspot,PPPOE'],
            'routers' => ['required', 'string', 'max:32'],
            'id_plan' => ['required', 'exists:tbl_plans,id'],
            'code' => ['required', 'string', 'max:55', 'unique:tbl_voucher,code,'.$voucher->id],
            'user' => ['nullable', 'string', 'max:45'],
            'status' => ['required', 'in:0,1'],
            'used_date' => ['nullable', 'date'],
            'generated_by' => ['nullable', 'integer'],
        ]);

        $voucher->update($data);

        return redirect()->route('admin.vouchers.index')->with('success', 'Voucher berhasil diperbarui.');
    }

    public function destroy(Voucher $voucher): \Illuminate\Http\RedirectResponse
    {
        $voucher->delete();
        return redirect()->route('admin.vouchers.index')->with('success', 'Voucher berhasil dihapus.');
    }

    public function generate(Request $request, VoucherService $voucherService): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'id_plan' => ['required', 'exists:tbl_plans,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:500'],
            'code_length' => ['required', 'integer', 'min:4', 'max:20'],
        ]);

        $plan = Plan::findOrFail($data['id_plan']);
        $vouchers = $voucherService->generate($plan, (int) $data['quantity'], (int) $data['code_length'], auth()->id());

        return redirect()->route('admin.vouchers.index')
            ->with('success', $vouchers->count() . ' voucher berhasil dibuat.');
    }
}
```

## Step 2: Routes

```php
use App\Http\Controllers\Admin\VoucherController;

Route::middleware('auth:web')->prefix('admin/vouchers')->name('admin.vouchers.')->group(function () {
    Route::get('/', [VoucherController::class, 'index'])->name('index');
    Route::get('/create', [VoucherController::class, 'create'])->name('create');
    Route::post('/', [VoucherController::class, 'store'])->name('store');
    Route::get('/{voucher}/edit', [VoucherController::class, 'edit'])->name('edit');
    Route::put('/{voucher}', [VoucherController::class, 'update'])->name('update');
    Route::delete('/{voucher}', [VoucherController::class, 'destroy'])->name('destroy');
    Route::post('/generate', [VoucherController::class, 'generate'])->name('generate');
});
```

## Step 3: Vue Pages

### Index — `resources/js/Pages/Admin/Voucher/Index.vue`

Pola seperti resource lain, dengan tambahan:
- **Header action "Generate Vouchers"** — tombol di header yang membuka modal
- **Checkbox per row + "Print Selected"** bulk button
- Modal untuk generate

```vue
<script setup>
import { Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, watch } from 'vue';

const props = defineProps({ vouchers: Object, filters: Object });

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');
const showGenerateModal = ref(false);
const selectedIds = ref([]);

const generateForm = useForm({
    id_plan: '',
    quantity: 10,
    code_length: 8,
});

watch([search, statusFilter], () => {
    router.get('/admin/vouchers', { search: search.value, status: statusFilter.value },
        { preserveState: true, replace: true });
});

function toggleSelectAll(e) {
    selectedIds.value = e.target.checked ? props.vouchers.data.map(v => v.id) : [];
}

function toggleSelect(id) {
    const idx = selectedIds.value.indexOf(id);
    if (idx > -1) selectedIds.value.splice(idx, 1);
    else selectedIds.value.push(id);
}

function printSelected() {
    if (selectedIds.value.length === 0) return alert('Pilih minimal satu voucher.');
    const ids = selectedIds.value.join(',');
    window.open(`/admin/vouchers/print?ids=${ids}`, '_blank');
}

function destroy(id) {
    if (!confirm('Hapus voucher ini?')) return;
    router.delete(`/admin/vouchers/${id}`);
}

function submitGenerate() {
    generateForm.post('/admin/vouchers/generate', {
        onSuccess: () => showGenerateModal.value = false,
    });
}
</script>

<template>
    <AdminLayout>
        <template #title>Vouchers</template>

        <!-- Flash messages -->
        <div v-if="$page.props.flash?.success" class="mb-4 bg-green-100 text-green-700 p-3 rounded">
            {{ $page.props.flash.success }}
        </div>
        <div v-if="$page.props.flash?.error" class="mb-4 bg-red-100 text-red-700 p-3 rounded">
            {{ $page.props.flash.error }}
        </div>

        <div class="mb-4 flex gap-4 flex-wrap items-center">
            <Link href="/admin/vouchers/create" class="bg-amber-600 text-white px-4 py-2 rounded hover:bg-amber-700">
                + Tambah Voucher
            </Link>
            <button @click="showGenerateModal = true"
                    class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                + Generate Vouchers
            </button>
            <button @click="printSelected" :disabled="selectedIds.length === 0"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 disabled:opacity-50">
                Print Selected ({{ selectedIds.length }})
            </button>
            <input v-model="search" type="text" placeholder="Cari kode atau plan..."
                   class="flex-1 max-w-xs rounded border px-3 py-2" />
            <select v-model="statusFilter" class="rounded border px-3 py-2">
                <option value="">Semua</option>
                <option value="0">Unused</option>
                <option value="1">Used</option>
            </select>
        </div>

        <!-- Table -->
        <div class="bg-white rounded shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2"><input type="checkbox" @change="toggleSelectAll" /></th>
                        <th class="px-3 py-2 text-left">Code</th>
                        <th class="px-3 py-2 text-left">Plan</th>
                        <th class="px-3 py-2 text-left">Type</th>
                        <th class="px-3 py-2 text-left">Routers</th>
                        <th class="px-3 py-2 text-left">Status</th>
                        <th class="px-3 py-2 text-left">Created</th>
                        <th class="px-3 py-2 text-left">Used Date</th>
                        <th class="px-3 py-2 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="v in vouchers.data" :key="v.id" class="border-t hover:bg-gray-50">
                        <td class="px-3 py-2"><input type="checkbox" :checked="selectedIds.includes(v.id)" @change="toggleSelect(v.id)" /></td>
                        <td class="px-3 py-2 font-mono text-xs">{{ v.code }}</td>
                        <td class="px-3 py-2">{{ v.plan?.name_plan }}</td>
                        <td class="px-3 py-2">{{ v.type }}</td>
                        <td class="px-3 py-2">{{ v.routers }}</td>
                        <td class="px-3 py-2">
                            <span :class="v.status === '0' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'"
                                  class="px-2 py-0.5 rounded-full text-xs font-medium">
                                {{ v.status === '0' ? 'Unused' : 'Used' }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-xs">{{ v.created_at }}</td>
                        <td class="px-3 py-2 text-xs">{{ v.used_date || '-' }}</td>
                        <td class="px-3 py-2 text-right space-x-1">
                            <Link :href="`/admin/vouchers/${v.id}/edit`" class="text-blue-600 hover:underline text-xs">Edit</Link>
                            <button @click="destroy(v.id)" class="text-red-600 hover:underline text-xs">Delete</button>
                        </td>
                    </tr>
                    <tr v-if="vouchers.data.length === 0">
                        <td colspan="9" class="px-4 py-4 text-center text-gray-500">Tidak ada voucher.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="vouchers.links" class="mt-4 flex justify-center gap-2">
            <Link v-for="link in vouchers.links" :key="link.label" :href="link.url || '#'"
                  v-html="link.label"
                  class="px-3 py-1 rounded border text-sm"
                  :class="{ 'bg-gray-200': link.active, 'opacity-50 pointer-events-none': !link.url }" />
        </div>

        <!-- Generate Modal -->
        <div v-if="showGenerateModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                <h2 class="text-lg font-bold mb-4">Generate Vouchers</h2>
                <form @submit.prevent="submitGenerate" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium">Plan *</label>
                        <select v-model="generateForm.id_plan" required class="mt-1 block w-full rounded border px-3 py-2">
                            <option value="">-- Pilih Plan --</option>
                            <option v-for="p in plans" :key="p.id" :value="p.id">{{ p.name_plan }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Jumlah (1-500)</label>
                        <input v-model.number="generateForm.quantity" type="number" min="1" max="500"
                               class="mt-1 block w-full rounded border px-3 py-2" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Panjang Kode (4-20)</label>
                        <input v-model.number="generateForm.code_length" type="number" min="4" max="20"
                               class="mt-1 block w-full rounded border px-3 py-2" />
                    </div>
                    <div class="flex gap-2 justify-end">
                        <button type="button" @click="showGenerateModal = false"
                                class="px-4 py-2 border rounded text-gray-600 hover:bg-gray-50">Batal</button>
                        <button type="submit" :disabled="generateForm.processing"
                                class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 disabled:opacity-50">
                            Generate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
```

### Create & Edit

Ikuti pola yang sama seperti resource lain. Form fields:
- `type`: select Hotspot/PPPOE, default Hotspot
- `routers`: text, default `radius` (PENTING — harus `'radius'` supaya bisa diaktivasi RADIUS)
- `id_plan`: select dari plans prop
- `code`: text (manual entry untuk single voucher)
- `user`: text optional
- `status`: select `0`=Unused / `1`=Used, default `0`
- `used_date`: datetime optional
- `generated_by`: numeric, default 0

Buat `resources/js/Pages/Admin/Voucher/Create.vue` dan `Edit.vue`.

## Verifikasi

1. `/admin/vouchers` → list voucher
2. Create → buat 1 voucher manual
3. Generate → buka modal, pilih plan, qty=10, generate → 10 voucher muncul
4. Print → centang beberapa, klik Print → buka tab baru ke `/admin/vouchers/print?ids=...`
5. Edit/Delete → normal CRUD
