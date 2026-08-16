<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(): Response
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();

        return Inertia::render('Customer/Profile', [
            'profile' => [
                'fullname' => $customer->fullname,
                'phonenumber' => $customer->phonenumber,
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();

        $data = $request->validate([
            'fullname' => ['required', 'string', 'max:255'],
            'phonenumber' => ['nullable', 'string', 'max:255'],
            'current_password' => ['nullable', 'required_with:password', 'string'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if ($request->filled('password') && $data['current_password'] !== $customer->password) {
            throw ValidationException::withMessages(['current_password' => 'Password saat ini salah.']);
        }

        $customer->fullname = $data['fullname'];

        if ($request->filled('phonenumber')) {
            $customer->phonenumber = $data['phonenumber'];
        }

        if ($request->filled('password')) {
            // Customer.password disimpan PLAINTEXT karena RADIUS PAP/CHAP
            // membutuhkan nilai asli — JANGAN Hash::make di sini.
            $customer->password = $data['password'];
        }

        $customer->save();

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
