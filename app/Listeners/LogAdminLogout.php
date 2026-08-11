<?php

namespace App\Listeners;

use App\Services\ActivityLogger;
use Illuminate\Auth\Events\Logout;

class LogAdminLogout
{
    public function handle(Logout $event): void
    {
        if ($event->guard !== 'web' || ! $event->user) {
            return;
        }

        ActivityLogger::log('logout', "Admin [{$event->user->username}] logged out", $event->user->getKey());
    }
}
