<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Resonance Connection
    |--------------------------------------------------------------------------
    |
    | This option controls the default connection that will be used when
    | using the Resonance WebSocket client. You may set this to any of
    | the connections defined in the "connections" array below.
    |
    */

    'default' => env('RESONANCE_CONNECTION', 'reverb'),

    /*
    |--------------------------------------------------------------------------
    | Resonance Connections
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the WebSocket connections for your application.
    | Each connection can use a different broadcaster (reverb, pusher, null).
    |
    */

    'connections' => [
        'reverb' => [
            'broadcaster' => 'reverb',
            'key' => env('REVERB_APP_KEY'),
            'authToken' => env('RESONANCE_AUTH_TOKEN'),
            'host' => env('REVERB_HOST', '127.0.0.1'),
            'port' => env('REVERB_PORT', 8080),
            'forceTLS' => env('REVERB_SCHEME', 'https') === 'https',
            'channelAuthorization' => [
                'endpoint' => env('APP_URL').'/broadcasting/auth',
            ],
        ],

        'soketi' => [
            'broadcaster' => 'reverb',
            'key' => env('SOKETI_APP_KEY'),
            'authToken' => env('RESONANCE_AUTH_TOKEN'),
            'host' => env('SOKETI_HOST', '127.0.0.1'),
            'port' => env('SOKETI_PORT', 6001),
            'forceTLS' => env('SOKETI_SCHEME', 'https') === 'https',
            'channelAuthorization' => [
                'endpoint' => env('APP_URL').'/broadcasting/auth',
            ],
        ],

        'pusher' => [
            'broadcaster' => 'pusher',
            'key' => env('PUSHER_APP_KEY'),
            'authToken' => env('RESONANCE_AUTH_TOKEN'),
            'cluster' => env('PUSHER_APP_CLUSTER', 'mt1'),
            'forceTLS' => true,
            'channelAuthorization' => [
                'endpoint' => env('APP_URL').'/broadcasting/auth',
            ],
        ],

        'ably' => [
            'broadcaster' => 'pusher',
            'key' => env('ABLY_KEY'),
            'authToken' => env('RESONANCE_AUTH_TOKEN'),
            'host' => 'realtime-pusher.ably.io',
            'port' => 443,
            'forceTLS' => true,
            'channelAuthorization' => [
                'endpoint' => env('APP_URL').'/broadcasting/auth',
            ],
        ],

        'null' => [
            'broadcaster' => 'null',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Event Namespace
    |--------------------------------------------------------------------------
    |
    | This option controls the default namespace for event classes. This is
    | used when formatting event names for the WebSocket connection.
    |
    */

    'namespace' => 'App.Events',
];
