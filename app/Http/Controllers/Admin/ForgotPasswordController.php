<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PasswordResetOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ForgotPasswordController extends Controller
{
    public function __construct(private readonly PasswordResetOtpService $otp) {}

    public function show(): \Inertia\Response
    {
        return Inertia::render('Admin/Auth/ForgotPassword');
    }

    public function requestCode(Request $request): RedirectResponse
    {
        $data = $request->validate(['username' => ['required', 'string']]);

        $this->otp->requestOtp('admin', $data['username']);

        return back()->with('status', 'Kalau username ditemukan, kode verifikasi sudah dikirim via WhatsApp.')
            ->with('username', $data['username']);
    }

    public function reset(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'otp' => ['required', 'string'],
        ]);

        $newPassword = $this->otp->verifyAndReset('admin', $data['username'], $data['otp']);

        if (! $newPassword) {
            return back()->withErrors(['otp' => 'Kode verifikasi salah atau sudah kedaluwarsa.'])->withInput();
        }

        return back()->with('new_password', $newPassword)->with('username', $data['username']);
    }
}
