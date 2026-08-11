<?php

namespace App\Http\Controllers;

use App\Models\AppConfig;
use App\Models\Order;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function show(Order $order): Response
    {
        return Inertia::render('Public/Invoice', [
            'order' => $order->load('customer', 'plan'),
            'qrPath' => AppConfig::get('payment_qr_path'),
        ]);
    }
}
