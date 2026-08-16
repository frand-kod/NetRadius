<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppConfig;
use App\Services\NotificationTemplateService;
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

        $templateEvents = collect(NotificationTemplateService::EVENTS)
            ->map(function (array $config, string $key) {
                $vars = [];
                foreach ($config['vars'] as $var => $desc) {
                    $vars[] = ['var' => $var, 'desc' => $desc];
                }

                return [
                    'key' => $key,
                    'label' => $config['label'],
                    'description' => $config['description'],
                    'value' => AppConfig::get('notif_'.$key, $config['default']),
                    'enabled' => AppConfig::get('notif_'.$key.'_enabled', '1') !== '0',
                    'vars' => $vars,
                ];
            })
            ->values();

        return Inertia::render('Admin/Settings/Notification', [
            'settings' => $settings,
            'templateEvents' => $templateEvents,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validation = [];
        foreach (self::KEYS as $key) {
            $validation[$key] = ['nullable', 'string'];
        }
        foreach (array_keys(NotificationTemplateService::EVENTS) as $key) {
            $validation['notif_'.$key] = ['nullable', 'string'];
            $validation['notif_'.$key.'_enabled'] = ['sometimes', 'boolean'];
        }

        $data = $request->validate($validation);

        foreach (self::KEYS as $key) {
            AppConfig::set($key, $data[$key] ?? '');
        }
        foreach (array_keys(NotificationTemplateService::EVENTS) as $key) {
            AppConfig::set('notif_'.$key, $data['notif_'.$key] ?? '');
            AppConfig::set('notif_'.$key.'_enabled', $request->boolean('notif_'.$key.'_enabled') ? '1' : '0');
        }

        return back()->with('success', 'Pengaturan notifikasi disimpan.');
    }
}
