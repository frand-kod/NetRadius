# Task 04 — Order Resource (List + Create + Actions)

**Tujuan**: Halaman order admin. HANYA list dan create — TIDAK ADA edit page. Dua action: "Mark as Paid" dan "Cancel" pada order yang masih pending.

**Dependensi**: `00-setup.md`, `01-admin-auth.md`

**Waktu estimasi**: 1 jam

---

## ⚠️ Catatan Penting

- Order dibuat via `OrderService::create()` — jangan insert manual
- "Mark as Paid" memanggil `OrderService::markAsPaid()` → di dalamnya panggil `RechargeService::recharge()`
- Kalau customer masih punya plan aktif → `ActivePlanStillActiveException` → tampilkan error, jangan silent fail
- JANGAN buat Edit page — order tidak bisa diedit manual

---

## Skema DB

Tabel `tbl_orders`:
```
id, customer_id FK→tbl_customers, plan_id FK→tbl_plans, price(40),
status enum[pending,paid,cancelled], invoice_token(64) unique,
admin_id uint, created_at, paid_at datetime nullable
```

---

## Step 1: Controller

Buat `app/Http/Controllers/Admin/OrderController.php`:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ActivePlanStillActiveException;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Plan;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index(Request $request): \Inertia\Response
    {
        $query = Order::with('customer', 'plan');

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->whereHas('customer', fn ($c) => $c->where('fullname', 'like', "%{$s}%"))
                  ->orWhereHas('plan', fn ($p) => $p->where('name_plan', 'like', "%{$s}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $query->orderBy('id', 'desc');

        return Inertia::render('Admin/Order/Index', [
            'orders' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(): \Inertia\Response
    {
        return Inertia::render('Admin/Order/Create', [
            'customers' => Customer::orderBy('fullname')->get(['id', 'fullname']),
            'plans' => Plan::where('enabled', true)->orderBy('name_plan')->get(['id', 'name_plan', 'price']),
        ]);
    }

    public function store(Request $request, OrderService $orderService): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:tbl_customers,id'],
            'plan_id' => ['required', 'exists:tbl_plans,id'],
        ]);

        $customer = Customer::findOrFail($data['customer_id']);
        $plan = Plan::findOrFail($data['plan_id']);

        $order = $orderService->create($customer, $plan, auth()->id());

        return redirect()->route('admin.orders.index')
            ->with('success', "Order #{$order->id} berhasil dibuat. Invoice: {$order->invoice_token}");
    }

    public function markAsPaid(Order $order, OrderService $orderService): \Illuminate\Http\RedirectResponse
    {
        if ($order->status !== 'pending') {
            return back()->with('error', 'Hanya order pending yang bisa di-approve.');
        }

        try {
            $orderService->markAsPaid($order, auth()->id());
            return back()->with('success', 'Order berhasil di-approve. Customer sudah di-recharge.');
        } catch (ActivePlanStillActiveException $e) {
            return back()->with('error', 'Gagal approve: ' . $e->getMessage());
        }
    }

    public function cancel(Order $order, OrderService $orderService): \Illuminate\Http\RedirectResponse
    {
        if ($order->status !== 'pending') {
            return back()->with('error', 'Hanya order pending yang bisa di-cancel.');
        }

        $orderService->cancel($order, auth()->id());

        return back()->with('success', 'Order berhasil di-cancel.');
    }
}
```

## Step 2: Routes

```php
use App\Http\Controllers\Admin\OrderController;

Route::middleware('auth:web')->prefix('admin/orders')->name('admin.orders.')->group(function () {
    Route::get('/', [OrderController::class, 'index'])->name('index');
    Route::get('/create', [OrderController::class, 'create'])->name('create');
    Route::post('/', [OrderController::class, 'store'])->name('store');
    Route::post('/{order}/mark-as-paid', [OrderController::class, 'markAsPaid'])->name('mark-as-paid');
    Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
});
```

## Step 3: Vue Pages

### Index — `resources/js/Pages/Admin/Order/Index.vue`

```vue
<script setup>
import { Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, watch } from 'vue';

const props = defineProps({ orders: Object, filters: Object });

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');

watch([search, statusFilter], () => {
    router.get('/admin/orders', { search: search.value, status: statusFilter.value },
        { preserveState: true, replace: true });
});

const markPaidForm = useForm({});
const cancelForm = useForm({});

function markAsPaid(id) {
    if (!confirm('Approve order ini? Customer akan langsung di-recharge.')) return;
    markPaidForm.post(`/admin/orders/${id}/mark-as-paid`, { preserveScroll: true });
}

function cancelOrder(id) {
    if (!confirm('Cancel order ini?')) return;
    cancelForm.post(`/admin/orders/${id}/cancel`, { preserveScroll: true });
}
</script>

<template>
    <AdminLayout>
        <template #title>Orders</template>

        <div class="mb-4 flex gap-4">
            <Link href="/admin/orders/create" class="bg-amber-600 text-white px-4 py-2 rounded hover:bg-amber-700">
                + Buat Order
            </Link>
            <input v-model="search" type="text" placeholder="Cari customer atau plan..."
                   class="flex-1 max-w-md rounded border px-3 py-2" />
            <select v-model="statusFilter" class="rounded border px-3 py-2">
                <option value="">Semua Status</option>
                <option value="pending">Pending</option>
                <option value="paid">Paid</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>

        <div class="bg-white rounded shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left">ID</th>
                        <th class="px-3 py-2 text-left">Customer</th>
                        <th class="px-3 py-2 text-left">Plan</th>
                        <th class="px-3 py-2 text-left">Price</th>
                        <th class="px-3 py-2 text-left">Status</th>
                        <th class="px-3 py-2 text-left">Created</th>
                        <th class="px-3 py-2 text-left">Paid</th>
                        <th class="px-3 py-2 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="o in orders.data" :key="o.id" class="border-t hover:bg-gray-50">
                        <td class="px-3 py-2 font-mono">#{{ o.id }}</td>
                        <td class="px-3 py-2">{{ o.customer?.fullname }}</td>
                        <td class="px-3 py-2">{{ o.plan?.name_plan }}</td>
                        <td class="px-3 py-2">Rp {{ Number(o.price).toLocaleString('id-ID') }}</td>
                        <td class="px-3 py-2">
                            <span :class="{
                                'bg-green-100 text-green-700': o.status === 'paid',
                                'bg-red-100 text-red-700': o.status === 'cancelled',
                                'bg-amber-100 text-amber-700': o.status === 'pending',
                            }" class="px-2 py-0.5 rounded-full text-xs font-medium">{{ o.status }}</span>
                        </td>
                        <td class="px-3 py-2 text-xs">{{ o.created_at }}</td>
                        <td class="px-3 py-2 text-xs">{{ o.paid_at || '-' }}</td>
                        <td class="px-3 py-2 text-right space-x-1">
                            <Link :href="`/invoice/${o.invoice_token}`" target="_blank"
                                  class="text-gray-600 hover:underline text-xs">Invoice</Link>
                            <button v-if="o.status === 'pending'" @click="markAsPaid(o.id)"
                                    class="text-green-600 hover:underline text-xs">Approve</button>
                            <button v-if="o.status === 'pending'" @click="cancelOrder(o.id)"
                                    class="text-red-600 hover:underline text-xs">Cancel</button>
                        </td>
                    </tr>
                    <tr v-if="orders.data.length === 0">
                        <td colspan="8" class="px-4 py-4 text-center text-gray-500">Belum ada order.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="orders.links" class="mt-4 flex justify-center gap-2">
            <Link v-for="link in orders.links" :key="link.label" :href="link.url || '#'"
                  v-html="link.label"
                  class="px-3 py-1 rounded border text-sm"
                  :class="{ 'bg-gray-200': link.active, 'opacity-50 pointer-events-none': !link.url }" />
        </div>
    </AdminLayout>
</template>
```

### Create — `resources/js/Pages/Admin/Order/Create.vue`

```vue
<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { computed } from 'vue';

const props = defineProps({ customers: Array, plans: Array });

const form = useForm({
    customer_id: '',
    plan_id: '',
});

function submit() { form.post('/admin/orders'); }
</script>

<template>
    <AdminLayout>
        <template #title>Buat Order Baru</template>
        <form @submit.prevent="submit" class="bg-white rounded shadow p-6 max-w-lg space-y-4">
            <div>
                <label class="block text-sm font-medium">Customer *</label>
                <select v-model="form.customer_id" required class="mt-1 block w-full rounded border px-3 py-2">
                    <option value="">-- Pilih Customer --</option>
                    <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.fullname }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium">Plan *</label>
                <select v-model="form.plan_id" required class="mt-1 block w-full rounded border px-3 py-2">
                    <option value="">-- Pilih Plan --</option>
                    <option v-for="p in plans" :key="p.id" :value="p.id">
                        {{ p.name_plan }} — Rp {{ Number(p.price).toLocaleString('id-ID') }}
                    </option>
                </select>
            </div>
            <p class="text-sm text-gray-500">
                Order akan dibuat dengan status <strong>pending</strong>. Invoice + link QR akan dikirim ke WhatsApp customer.
                Admin harus approve manual setelah pembayaran terkonfirmasi.
            </p>
            <button type="submit" :disabled="form.processing"
                    class="bg-amber-600 text-white px-6 py-2 rounded hover:bg-amber-700 disabled:opacity-50">
                Buat Order
            </button>
        </form>
    </AdminLayout>
</template>
```

## Verifikasi

1. `/admin/orders` → list order terlihat
2. Klik "Buat Order" → pilih customer + plan → submit → order pending muncul di list
3. Klik "Approve" pada order pending → status jadi `paid`, `UserRecharge` dibuat
4. Klik "Cancel" pada order pending → status jadi `cancelled`
5. Tombol "Approve" dan "Cancel" tidak muncul pada order yang sudah paid/cancelled

## Catatan

- **JANGAN buat `resources/js/Pages/Admin/Order/Edit.vue`** — order tidak punya edit page
- `OrderService` dan `RechargeService` sudah ada — jangan dimodifikasi
- Kalau approve gagal (customer masih punya plan aktif), error akan ditampilkan via flash message
