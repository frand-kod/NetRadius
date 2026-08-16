<?php

namespace App\Http\Middleware;

use App\Models\AppConfig;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'status' => fn () => $request->session()->get('status'),
                'username' => fn () => $request->session()->get('username'),
                'new_password' => fn () => $request->session()->get('new_password'),
            ],
            'auth' => [
                'user' => fn () => $request->user('web')?->only('id', 'username', 'fullname'),
                'customer' => fn () => $request->user('customer')?->only('id', 'username', 'fullname'),
            ],
            'settings' => [
                'company_name' => fn () => AppConfig::get('company_name', config('app.name', 'PHPNuxBill')),
                'currency_symbol' => fn () => AppConfig::get('currency_symbol', 'Rp'),
            ],
        ]);
    }
}
