<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Voucher;
use App\Services\VoucherService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VoucherController extends Controller
{
    public function index(Request $request): Response
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
            'plans' => Plan::orderBy('name_plan')->get(['id', 'name_plan']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Voucher/Create', [
            'plans' => Plan::orderBy('name_plan')->get(['id', 'name_plan']),
        ]);
    }

    public function store(Request $request): RedirectResponse
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

        // `user` is NOT NULL in the DB — default empty string (ConvertEmptyStringsToNull
        // would otherwise turn a blank input into null and violate the constraint).
        $data['user'] = $data['user'] ?? '';

        Voucher::create($data);

        return redirect()->route('admin.vouchers.index')->with('success', 'Voucher berhasil dibuat.');
    }

    public function edit(Voucher $voucher): Response
    {
        return Inertia::render('Admin/Voucher/Edit', [
            'voucher' => $voucher,
            'plans' => Plan::orderBy('name_plan')->get(['id', 'name_plan']),
        ]);
    }

    public function update(Request $request, Voucher $voucher): RedirectResponse
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

    public function destroy(Voucher $voucher): RedirectResponse
    {
        $voucher->delete();

        return redirect()->route('admin.vouchers.index')->with('success', 'Voucher berhasil dihapus.');
    }

    public function generate(Request $request, VoucherService $voucherService): RedirectResponse
    {
        $data = $request->validate([
            'id_plan' => ['required', 'exists:tbl_plans,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:500'],
            'code_length' => ['required', 'integer', 'min:4', 'max:20'],
        ]);

        $plan = Plan::findOrFail($data['id_plan']);
        $vouchers = $voucherService->generate($plan, (int) $data['quantity'], (int) $data['code_length'], auth()->id());

        return redirect()->route('admin.vouchers.index')
            ->with('success', $vouchers->count().' voucher berhasil dibuat.');
    }
}
