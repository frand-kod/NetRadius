<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function show(): Response
    {
        $customer = Auth::guard('customer')->user();

        return Inertia::render('Customer/Dashboard', [
            'customer' => $customer,
            'orders' => Order::query()->with('plan')->where('customer_id', $customer->id)->latest('id')->get(),
            'transactions' => Transaction::query()->where('user_id', $customer->id)->latest('id')->get(),
        ]);
    }
}
