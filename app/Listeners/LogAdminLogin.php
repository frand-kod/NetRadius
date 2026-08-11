<?php

namespace App\Listeners;

use App\Services\ActivityLogger;
use Illuminate\Auth\Events\Login;

class LogAdminLogin
{
    public function handle(Login $event): void
    {
        if ($event->guard !== 'web') {
            return;
        }

        ActivityLogger::log('login', "Admin [{$event->user->username}] logged in", $event->user->getKey());
    }
}
