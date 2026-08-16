<?php

namespace App\Services\Hotspot;

use App\Models\Plan;
use InvalidArgumentException;

class HotspotDeviceResolver
{
    /**
     * Aplikasi hanya memakai satu jalur device: FreeRADIUS REST.
     *
     * @var array<string, class-string<HotspotDeviceInterface>>
     */
    private const DEVICES = [
        'RadiusRest' => RadiusRestService::class,
    ];

    public static function resolve(Plan $plan): HotspotDeviceInterface
    {
        $device = $plan->device ?: 'RadiusRest';

        if (! isset(self::DEVICES[$device])) {
            throw new InvalidArgumentException("Unknown hotspot device [{$device}] for plan [{$plan->id}].");
        }

        return app(self::DEVICES[$device]);
    }
}
