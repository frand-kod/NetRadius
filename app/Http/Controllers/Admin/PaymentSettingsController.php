<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\InvoiceController;
use App\Models\AppConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentSettingsController extends Controller
{
    public function edit(): Response
    {
        $invoiceVars = [];
        foreach (InvoiceController::VARS as $var => $desc) {
            $invoiceVars[] = ['var' => $var, 'desc' => $desc];
        }

        return Inertia::render('Admin/Settings/Payment', [
            'qrPath' => AppConfig::get('payment_qr_path'),
            'paymentInstructions' => AppConfig::get('payment_instructions'),
            'invoiceTemplate' => AppConfig::get('invoice_template', InvoiceController::DEFAULT_TEMPLATE),
            'invoiceVars' => $invoiceVars,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'payment_instructions' => ['nullable', 'string'],
            'invoice_template' => ['nullable', 'string'],
            'payment_qr' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('payment_qr')) {
            $path = $request->file('payment_qr')->store('payment-qr', 'public');
            AppConfig::set('payment_qr_path', $path);
        }

        AppConfig::set('payment_instructions', $data['payment_instructions'] ?? '');
        AppConfig::set('invoice_template', $data['invoice_template'] ?? '');

        return back()->with('success', 'Pengaturan pembayaran disimpan.');
    }
}
