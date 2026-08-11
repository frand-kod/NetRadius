<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ActivePlanStillActiveException;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Plan;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request): Response
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

    public function create(): Response
    {
        return Inertia::render('Admin/Order/Create', [
            'customers' => Customer::orderBy('fullname')->get(['id', 'fullname']),
            'plans' => Plan::where('enabled', true)->orderBy('name_plan')->get(['id', 'name_plan', 'price']),
        ]);
    }

    public function store(Request $request, OrderService $orderService): RedirectResponse
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

    public function markAsPaid(Order $order, OrderService $orderService): RedirectResponse
    {
        if ($order->status !== 'pending') {
            return back()->with('error', 'Hanya order pending yang bisa di-approve.');
        }

        try {
            $orderService->markAsPaid($order, auth()->id());

            return back()->with('success', 'Order berhasil di-approve. Customer sudah di-recharge.');
        } catch (ActivePlanStillActiveException $e) {
            return back()->with('error', 'Gagal approve: '.$e->getMessage());
        }
    }

    public function cancel(Order $order, OrderService $orderService): RedirectResponse
    {
        if ($order->status !== 'pending') {
            return back()->with('error', 'Hanya order pending yang bisa di-cancel.');
        }

        $orderService->cancel($order, auth()->id());

        return back()->with('success', 'Order berhasil di-cancel.');
    }
}
