<?php

return [
    'base_url' => env('XERIN_MARKETPLACE_API_URL', 'https://api.xerinmarketplace.com'),
    'api_key' => env('XERIN_MARKETPLACE_API_KEY', ''),
    'provider' => env('XERIN_MARKETPLACE_PROVIDER', 'xerin_marketplace'),
    'webhook_secret' => env('XERIN_MARKETPLACE_WEBHOOK_SECRET', ''),
    'enabled_events' => env('XERIN_MARKETPLACE_ENABLED_EVENTS', 'shipment.updated,delivery.completed'),
];
