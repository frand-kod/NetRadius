<?php

namespace App\Services\Hotspot;

use App\Models\Plan;
use InvalidArgumentException;

class HotspotDeviceResolver
{
    /**
     * @var array<string, class-string<HotspotDeviceInterface>>
     */
    private const DEVICES = [
        'MikrotikHotspot' => MikrotikHotspotService::class,
        'RadiusRest' => RadiusRestService::class,
    ];

    public static function resolve(Plan $plan): HotspotDeviceInterface
    {
        $device = $plan->device ?: 'MikrotikHotspot';

        if (! isset(self::DEVICES[$device])) {
            throw new InvalidArgumentException("Unknown hotspot device [{$device}] for plan [{$plan->id}].");
        }

        return app(self::DEVICES[$device]);
    }
}
