<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function show(): View
    {
        $customer = Auth::guard('customer')->user();

        return view('customer.dashboard', [
            'customer' => $customer,
            'orders' => Order::query()->where('customer_id', $customer->id)->latest('id')->get(),
            'transactions' => Transaction::query()->where('user_id', $customer->id)->latest('id')->get(),
        ]);
    }
}
