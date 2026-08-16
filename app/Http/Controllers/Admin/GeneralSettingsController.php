<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GeneralSettingsController extends Controller
{
    private const KEYS = [
        'company_name',
        'company_address',
        'company_phone',
        'company_email',
        'currency_symbol',
        'currency_code',
    ];

    private const DEFAULTS = [
        'currency_symbol' => 'Rp',
        'currency_code' => 'IDR',
    ];

    public function edit(): Response
    {
        $settings = [];
        foreach (self::KEYS as $key) {
            $settings[$key] = AppConfig::get($key, self::DEFAULTS[$key] ?? null);
        }

        return Inertia::render('Admin/Settings/General', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string'],
            'company_phone' => ['nullable', 'string', 'max:50'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'currency_symbol' => ['nullable', 'string', 'max:10'],
            'currency_code' => ['nullable', 'string', 'max:10'],
        ]);

        foreach (self::KEYS as $key) {
            AppConfig::set($key, $data[$key] ?? '');
        }

        return back()->with('success', 'Pengaturan umum disimpan.');
    }
}
