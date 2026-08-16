<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Customer::query();

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('username', 'like', "%{$s}%")
                    ->orWhere('fullname', 'like', "%{$s}%")
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

    public function create(): Response
    {
        return Inertia::render('Admin/Customer/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:45', 'unique:tbl_customers,username'],
            'password' => ['required', 'string', 'max:255'],
            'fullname' => ['required', 'string', 'max:45'],
            'phonenumber' => ['required', 'string', 'max:20'],
            'status' => ['required', 'in:Active,Banned,Disabled,Inactive,Limited,Suspended'],
        ]);

        Customer::create($data);

        return redirect()->route('admin.customers.index')->with('success', 'Customer berhasil dibuat.');
    }

    public function edit(Customer $customer): Response
    {
        return Inertia::render('Admin/Customer/Edit', [
            'customer' => $customer,
        ]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:45', 'unique:tbl_customers,username,'.$customer->id],
            'password' => ['nullable', 'string', 'max:255'],
            'fullname' => ['required', 'string', 'max:45'],
            'phonenumber' => ['required', 'string', 'max:20'],
            'status' => ['required', 'in:Active,Banned,Disabled,Inactive,Limited,Suspended'],
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $customer->update($data);

        return redirect()->route('admin.customers.index')->with('success', 'Customer berhasil diperbarui.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return redirect()->route('admin.customers.index')->with('success', 'Customer berhasil dihapus.');
    }
}
