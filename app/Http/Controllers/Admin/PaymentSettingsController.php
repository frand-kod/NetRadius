<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentSettingsController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('Admin/Settings/Payment', [
            'qrPath' => AppConfig::get('payment_qr_path'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'payment_qr' => ['required', 'image', 'max:2048'],
        ]);

        $path = $request->file('payment_qr')->store('payment-qr', 'public');
        AppConfig::set('payment_qr_path', $path);

        return back()->with('success', 'QR pembayaran disimpan.');
    }
}
