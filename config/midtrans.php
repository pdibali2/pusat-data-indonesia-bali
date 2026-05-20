<?php

$isProduction = env('MIDTRANS_IS_PRODUCTION', false);

return [
    'server_key'       => env('MIDTRANS_SERVER_KEY'),
    'client_key'       => env('MIDTRANS_CLIENT_KEY'),
    'is_production'    => $isProduction,
    'is_sanitized'     => env('MIDTRANS_IS_SANITIZED'),
    'is_3ds'           => env('MIDTRANS_IS_3DS'),
    'notification_url' => env('MIDTRANS_NOTIFICATION_URL'),

    'snap_url' => $isProduction
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js',
];