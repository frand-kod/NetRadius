<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    public static function log(string $type, string $description, ?int $userId = null): void
    {
        $userId ??= Auth::guard('web')->id();
        if ($userId === null) {
            // No authenticated admin (e.g. system/seed/test context) — nothing to attribute the entry to.
            return;
        }

        ActivityLog::create([
            'date' => now(),
            'type' => $type,
            'description' => $description,
            'userid' => $userId,
            'ip' => Request::ip() ?? '',
        ]);
    }
}
