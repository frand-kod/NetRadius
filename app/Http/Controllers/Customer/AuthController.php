<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function showLogin(): Response
    {
        return Inertia::render('Customer/Login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Customer.password is stored in plaintext (RADIUS PAP/CHAP needs the
        // original value) — do NOT use Hash::check()/Auth::attempt() here.
        $customer = Customer::query()
            ->where('username', $credentials['username'])
            ->where('password', $credentials['password'])
            ->where('status', 'Active')
            ->first();

        if (! $customer) {
            return back()->withErrors(['username' => 'Username atau password salah.'])->onlyInput('username');
        }

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        return redirect()->route('customer.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('customer.login');
    }
}
