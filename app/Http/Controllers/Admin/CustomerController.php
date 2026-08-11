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
            'password' => ['required', 'string', 'max:255'],
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

        Customer::create($data);

        return redirect()->route('admin.customers.index')->with('success', 'Customer berhasil dibuat.');
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
            'password' => ['nullable', 'string', 'max:255'],
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

        if (empty($data['password'])) {
            unset($data['password']);
        }
        if (empty($data['pppoe_password'])) {
            unset($data['pppoe_password']);
        }

        $customer->update($data);

        return redirect()->route('admin.customers.index')->with('success', 'Customer berhasil diperbarui.');
    }

    public function destroy(Customer $customer): \Illuminate\Http\RedirectResponse
    {
        $customer->delete();

        return redirect()->route('admin.customers.index')->with('success', 'Customer berhasil dihapus.');
    }
}
