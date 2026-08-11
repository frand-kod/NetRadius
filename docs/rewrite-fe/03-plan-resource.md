# Task 03 — Plan Resource (CRUD)

**Tujuan**: CRUD admin untuk `tbl_plans`. 26 form fields, 24 table columns.

**Dependensi**: `00-setup.md`, `01-admin-auth.md`

**Waktu estimasi**: 1 jam

---

## Skema DB

Tabel `tbl_plans`:
```
id, name_plan(40), id_bw FK→tbl_bandwidth, price(40), price_old(40),
type enum[Hotspot,PPPOE,VPN,Balance], typebp enum[Unlimited,Limited] nullable,
limit_type enum[Time_Limit,Data_Limit,Both_Limit] nullable,
time_limit uint nullable, time_unit enum[Mins,Hrs] nullable,
data_limit uint nullable, data_unit enum[MB,GB] nullable,
validity int, validity_unit enum[Mins,Hrs,Days,Months,Period],
shared_users int nullable, routers(32), is_radius boolean,
pool(40) nullable, plan_expired int, expired_date tinyint,
enabled boolean, allow_purchase enum[yes,no], prepaid enum[yes,no],
plan_type enum[Business,Personal], device(32),
on_login text nullable, on_logout text nullable
```

---

## Step 1: Controller

Buat `app/Http/Controllers/Admin/PlanController.php`:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bandwidth;
use App\Models\Plan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlanController extends Controller
{
    public function index(Request $request): \Inertia\Response
    {
        $query = Plan::query();

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('name_plan', 'like', "%{$s}%")
                  ->orWhere('type', 'like', "%{$s}%")
                  ->orWhere('device', 'like', "%{$s}%");
            });
        }

        $query->orderBy('id', 'desc');

        return Inertia::render('Admin/Plan/Index', [
            'plans' => $query->with('bandwidth')->paginate(15)->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(): \Inertia\Response
    {
        return Inertia::render('Admin/Plan/Create', [
            'bandwidths' => Bandwidth::orderBy('name_bw')->get(['id', 'name_bw']),
        ]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'name_plan' => ['required', 'string', 'max:40'],
            'id_bw' => ['required', 'integer', 'exists:tbl_bandwidth,id'],
            'price' => ['required', 'string', 'max:40'],
            'price_old' => ['required', 'string', 'max:40'],
            'type' => ['required', 'in:Hotspot,PPPOE,VPN,Balance'],
            'typebp' => ['nullable', 'in:Unlimited,Limited'],
            'limit_type' => ['nullable', 'in:Time_Limit,Data_Limit,Both_Limit'],
            'time_limit' => ['nullable', 'integer', 'min:0'],
            'time_unit' => ['nullable', 'in:Mins,Hrs'],
            'data_limit' => ['nullable', 'integer', 'min:0'],
            'data_unit' => ['nullable', 'in:MB,GB'],
            'validity' => ['required', 'integer', 'min:0'],
            'validity_unit' => ['required', 'in:Mins,Hrs,Days,Months,Period'],
            'shared_users' => ['nullable', 'integer', 'min:0'],
            'routers' => ['required', 'string', 'max:32'],
            'is_radius' => ['boolean'],
            'pool' => ['nullable', 'string', 'max:40'],
            'plan_expired' => ['required', 'integer'],
            'expired_date' => ['required', 'integer'],
            'enabled' => ['boolean'],
            'allow_purchase' => ['required', 'in:yes,no'],
            'prepaid' => ['required', 'in:yes,no'],
            'plan_type' => ['required', 'in:Business,Personal'],
            'device' => ['required', 'string', 'max:32'],
            'on_login' => ['nullable', 'string'],
            'on_logout' => ['nullable', 'string'],
        ]);

        Plan::create($data);

        return redirect()->route('admin.plans.index')->with('success', 'Plan berhasil dibuat.');
    }

    public function edit(Plan $plan): \Inertia\Response
    {
        return Inertia::render('Admin/Plan/Edit', [
            'plan' => $plan,
            'bandwidths' => Bandwidth::orderBy('name_bw')->get(['id', 'name_bw']),
        ]);
    }

    public function update(Request $request, Plan $plan): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'name_plan' => ['required', 'string', 'max:40'],
            'id_bw' => ['required', 'integer', 'exists:tbl_bandwidth,id'],
            'price' => ['required', 'string', 'max:40'],
            'price_old' => ['required', 'string', 'max:40'],
            'type' => ['required', 'in:Hotspot,PPPOE,VPN,Balance'],
            'typebp' => ['nullable', 'in:Unlimited,Limited'],
            'limit_type' => ['nullable', 'in:Time_Limit,Data_Limit,Both_Limit'],
            'time_limit' => ['nullable', 'integer', 'min:0'],
            'time_unit' => ['nullable', 'in:Mins,Hrs'],
            'data_limit' => ['nullable', 'integer', 'min:0'],
            'data_unit' => ['nullable', 'in:MB,GB'],
            'validity' => ['required', 'integer', 'min:0'],
            'validity_unit' => ['required', 'in:Mins,Hrs,Days,Months,Period'],
            'shared_users' => ['nullable', 'integer', 'min:0'],
            'routers' => ['required', 'string', 'max:32'],
            'is_radius' => ['boolean'],
            'pool' => ['nullable', 'string', 'max:40'],
            'plan_expired' => ['required', 'integer'],
            'expired_date' => ['required', 'integer'],
            'enabled' => ['boolean'],
            'allow_purchase' => ['required', 'in:yes,no'],
            'prepaid' => ['required', 'in:yes,no'],
            'plan_type' => ['required', 'in:Business,Personal'],
            'device' => ['required', 'string', 'max:32'],
            'on_login' => ['nullable', 'string'],
            'on_logout' => ['nullable', 'string'],
        ]);

        $plan->update($data);

        return redirect()->route('admin.plans.index')->with('success', 'Plan berhasil diperbarui.');
    }

    public function destroy(Plan $plan): \Illuminate\Http\RedirectResponse
    {
        $plan->delete();
        return redirect()->route('admin.plans.index')->with('success', 'Plan berhasil dihapus.');
    }
}
```

## Step 2: Routes

```php
use App\Http\Controllers\Admin\PlanController;

Route::middleware('auth:web')->prefix('admin/plans')->name('admin.plans.')->group(function () {
    Route::get('/', [PlanController::class, 'index'])->name('index');
    Route::get('/create', [PlanController::class, 'create'])->name('create');
    Route::post('/', [PlanController::class, 'store'])->name('store');
    Route::get('/{plan}/edit', [PlanController::class, 'edit'])->name('edit');
    Route::put('/{plan}', [PlanController::class, 'update'])->name('update');
    Route::delete('/{plan}', [PlanController::class, 'destroy'])->name('destroy');
});
```

## Step 3: Vue Pages

Model: `/admin/plans`, `/admin/plans/create`, `/admin/plans/{plan}/edit`.

Gunakan pola yang sama seperti Customer resource — buat tiga file:
- `resources/js/Pages/Admin/Plan/Index.vue`
- `resources/js/Pages/Admin/Plan/Create.vue`
- `resources/js/Pages/Admin/Plan/Edit.vue`

### Index — tabel menampilkan kolom kunci:
`name_plan`, `type`, `price`, `validity` + `validity_unit`, `routers`, `is_radius` (badge), `enabled` (badge), `device`, `Actions` (Edit/Delete)

### Create/Edit — form 3-column grid:
- Row 1: `name_plan`, `id_bw` (select dari `bandwidths` prop), `price`
- Row 2: `price_old`, `type` (select), `typebp` (select)
- Row 3: `limit_type` (select), `time_limit`, `time_unit` (select)
- Row 4: `data_limit`, `data_unit` (select), `validity`
- Row 5: `validity_unit` (select), `shared_users`, `routers`
- Row 6: `is_radius` (toggle), `pool`, `plan_expired`
- Row 7: `expired_date`, `enabled` (toggle), `allow_purchase` (select)
- Row 8: `prepaid` (select), `plan_type` (select), `device`
- Row 9: `on_login` (textarea, full width)
- Row 10: `on_logout` (textarea, full width)

> Karena Plan punya 26 field, implementasi Vue lengkap diserahkan ke Flash model mengikuti pola Router/Customer.
> Semua enum field pakai `<select>` dengan opsi yang sesuai.

## Verifikasi

1. `/admin/plans` → list dengan kolom key
2. Create → semua field tampil, submit → redirect list
3. Edit → data pre-filled, ubah → list terupdate
4. Delete → plan hilang
