<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bandwidth;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlanController extends Controller
{
    public function index(Request $request): Response
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

    public function create(): Response
    {
        return Inertia::render('Admin/Plan/Create', [
            'bandwidths' => Bandwidth::orderBy('name_bw')->get(['id', 'name_bw']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name_plan' => ['required', 'string', 'max:40'],
            'id_bw' => ['required', 'integer', 'exists:tbl_bandwidth,id'],
            'price' => ['required', 'string', 'max:40'],
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
            'enabled' => ['boolean'],
            'device' => ['required', 'string', 'max:32'],
        ]);

        Plan::create($data);

        return redirect()->route('admin.plans.index')->with('success', 'Plan berhasil dibuat.');
    }

    public function edit(Plan $plan): Response
    {
        return Inertia::render('Admin/Plan/Edit', [
            'plan' => $plan,
            'bandwidths' => Bandwidth::orderBy('name_bw')->get(['id', 'name_bw']),
        ]);
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $data = $request->validate([
            'name_plan' => ['required', 'string', 'max:40'],
            'id_bw' => ['required', 'integer', 'exists:tbl_bandwidth,id'],
            'price' => ['required', 'string', 'max:40'],
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
            'enabled' => ['boolean'],
            'device' => ['required', 'string', 'max:32'],
        ]);

        $plan->update($data);

        return redirect()->route('admin.plans.index')->with('success', 'Plan berhasil diperbarui.');
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        $plan->delete();

        return redirect()->route('admin.plans.index')->with('success', 'Plan berhasil dihapus.');
    }
}
