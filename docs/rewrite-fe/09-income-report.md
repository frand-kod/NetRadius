# Task 09 — Income Report

**Tujuan**: Halaman laporan income per periode (date range + tabel agregasi). Tidak ada CRUD — query `ReportService`.

**Dependensi**: `00-setup.md`, `01-admin-auth.md`

**Waktu estimasi**: 30 menit

---

## Controller

Buat `app/Http/Controllers/Admin/IncomeReportController.php`:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IncomeReportController extends Controller
{
    public function show(Request $request, ReportService $reportService): \Inertia\Response
    {
        $from = $request->input('from', Carbon::now()->startOfMonth()->toDateString());
        $to = $request->input('to', Carbon::now()->endOfMonth()->toDateString());

        $fromDate = Carbon::parse($from)->startOfDay();
        $toDate = Carbon::parse($to)->endOfDay();

        $rows = $reportService->incomeByDay($fromDate, $toDate);
        $total = $reportService->totalIncome($fromDate, $toDate) ?? 0;

        return Inertia::render('Admin/IncomeReport', [
            'rows' => $rows,
            'total' => $total,
            'from' => $from,
            'to' => $to,
        ]);
    }
}
```

## Route

```php
use App\Http\Controllers\Admin\IncomeReportController;

Route::middleware('auth:web')->get('/admin/income-report', [IncomeReportController::class, 'show'])
    ->name('admin.income-report');
```

## Vue Page — `resources/js/Pages/Admin/IncomeReport.vue`

```vue
<script setup>
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    rows: Array,
    total: Number,
    from: String,
    to: String,
});

const fromDate = ref(props.from);
const toDate = ref(props.to);

function generate() {
    router.get('/admin/income-report', { from: fromDate.value, to: toDate.value },
        { preserveState: true, replace: true });
}

function formatCurrency(amount) {
    return 'Rp ' + Number(amount).toLocaleString('id-ID');
}
</script>

<template>
    <AdminLayout>
        <template #title>Income Report</template>

        <!-- Date Filter -->
        <div class="bg-white rounded shadow p-4 mb-6 flex gap-4 items-end">
            <div>
                <label class="block text-sm font-medium">Dari Tanggal</label>
                <input v-model="fromDate" type="date" class="mt-1 rounded border px-3 py-2" />
            </div>
            <div>
                <label class="block text-sm font-medium">Sampai Tanggal</label>
                <input v-model="toDate" type="date" class="mt-1 rounded border px-3 py-2" />
            </div>
            <button @click="generate"
                    class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                Tampilkan
            </button>
        </div>

        <!-- Results Table -->
        <div class="bg-white rounded shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">Tanggal</th>
                        <th class="px-4 py-2 text-right">Jumlah Transaksi</th>
                        <th class="px-4 py-2 text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in rows" :key="row.date" class="border-t hover:bg-gray-50">
                        <td class="px-4 py-2">{{ row.date }}</td>
                        <td class="px-4 py-2 text-right">{{ row.count }}</td>
                        <td class="px-4 py-2 text-right font-medium">{{ formatCurrency(row.total) }}</td>
                    </tr>
                    <tr v-if="rows.length === 0">
                        <td colspan="3" class="px-4 py-4 text-center text-gray-500">
                            Tidak ada transaksi pada periode ini.
                        </td>
                    </tr>
                </tbody>
                <tfoot v-if="rows.length > 0" class="bg-gray-100 font-bold">
                    <tr>
                        <td class="px-4 py-3 text-left">TOTAL</td>
                        <td class="px-4 py-3 text-right">{{ rows.reduce((s, r) => s + Number(r.count), 0) }}</td>
                        <td class="px-4 py-3 text-right">{{ formatCurrency(total) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </AdminLayout>
</template>
```

## Verifikasi

1. `/admin/income-report` → tabel tampil dengan data bulan ini
2. Ganti filter date → klik "Tampilkan" → data sesuai periode
3. Total footer akurat (bandingkan dengan `SELECT SUM(price) FROM tbl_transactions WHERE recharged_on BETWEEN ...`)
