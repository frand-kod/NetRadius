<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Radius API Secret
    |--------------------------------------------------------------------------
    |
    | Shared secret used to authenticate requests from FreeRADIUS to the
    | /api/radius endpoint. Set RADIUS_API_SECRET in your .env to a long,
    | random value, then append the same value as `secret=` to the `data`
    | payload of every rlm_rest section in the FreeRADIUS
    | mods-enabled/rest config (see docs/freeradius-rest-integration.md).
    |
    | Keep this empty to deny all Radius requests (fail-closed) — the app
    | will not start accepting Radius calls until a secret is configured.
    |
    */

    'secret' => env('RADIUS_API_SECRET', ''),

];
