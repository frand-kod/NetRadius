<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\Voucher;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function show(): \Inertia\Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'totalCustomers' => Customer::count(),
                'activeCustomers' => Customer::where('status', 'Active')->count(),
                'pendingOrders' => Order::where('status', 'pending')->count(),
                'unusedVouchers' => Voucher::where('status', '0')->count(),
                'totalIncome' => Transaction::sum('price'),
            ],
        ]);
    }
}
