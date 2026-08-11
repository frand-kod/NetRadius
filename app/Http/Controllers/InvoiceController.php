<?php

namespace App\Http\Controllers;

use App\Models\AppConfig;
use App\Models\Order;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function show(Order $order): View
    {
        return view('invoice.show', [
            'order' => $order->load('customer', 'plan'),
            'qrPath' => AppConfig::get('payment_qr_path'),
        ]);
    }
}
