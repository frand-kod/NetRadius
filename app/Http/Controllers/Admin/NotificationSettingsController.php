<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationSettingsController extends Controller
{
    private const KEYS = [
        'telegram_bot',
        'telegram_target_id',
        'alt_wga_server_url',
        'alt_wga_device_id',
        'alt_wga_username',
        'alt_wga_password',
        'country_code_phone',
    ];

    public function edit(): Response
    {
        $settings = [];
        foreach (self::KEYS as $key) {
            $settings[$key] = AppConfig::get($key);
        }

        return Inertia::render('Admin/Settings/Notification', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'telegram_bot' => ['nullable', 'string'],
            'telegram_target_id' => ['nullable', 'string'],
            'alt_wga_server_url' => ['nullable', 'string'],
            'alt_wga_device_id' => ['nullable', 'string'],
            'alt_wga_username' => ['nullable', 'string'],
            'alt_wga_password' => ['nullable', 'string'],
            'country_code_phone' => ['nullable', 'string'],
        ]);

        foreach (self::KEYS as $key) {
            AppConfig::set($key, $data[$key] ?? '');
        }

        return back()->with('success', 'Pengaturan notifikasi disimpan.');
    }
}
