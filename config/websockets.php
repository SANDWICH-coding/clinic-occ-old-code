<?php

return [
    'debug' => env('APP_DEBUG', false),

    'servers' => [
        [
            'host' => env('LARAVEL_WEBSOCKETS_HOST', '0.0.0.0'),
            'port' => env('LARAVEL_WEBSOCKETS_PORT', 6001),
            'pathname' => '/app',
            'max_backlog' => null,
            'ssl' => [
                'certPath' => env('LARAVEL_WEBSOCKETS_SSL_CERT', null),
                'keyPath' => env('LARAVEL_WEBSOCKETS_SSL_KEY', null),
                'passphrase' => env('LARAVEL_WEBSOCKETS_SSL_PASSPHRASE', null),
            ],
        ],
    ],

    'apps' => [
        [
            'id' => env('PUSHER_APP_ID'),
            'name' => env('APP_NAME'),
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'path' => '/app',
            'capacity' => null,
            'enable_client_messages' => true,
            'enable_statistics' => true,
        ],
    ],

    'allowed_origins' => [],

    'statistics' => [
        'enabled' => true,
        'interval_in_seconds' => 60,
    ],

    'ssl' => [
        'certPath' => env('LARAVEL_WEBSOCKETS_SSL_CERT', null),
        'keyPath' => env('LARAVEL_WEBSOCKETS_SSL_KEY', null),
        'passphrase' => env('LARAVEL_WEBSOCKETS_SSL_PASSPHRASE', null),
    ],
];