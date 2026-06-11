<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sidebar promo card
    |--------------------------------------------------------------------------
    |
    | Toggles the "build your own CRM" promo card shown at the bottom of the
    | sidebar. Set SIDEBAR_PROMO_ENABLED=false to hide it.
    |
    */

    'sidebar_promo' => [
        'enabled' => (bool) env('SIDEBAR_PROMO_ENABLED', true),
    ],

];
