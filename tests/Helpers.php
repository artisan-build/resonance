<?php

use ArtisanBuild\Resonance\Resonance;
use React\EventLoop\Loop;

/**
 * Ensure the WebSocket server is running before integration tests.
 *
 * @throws RuntimeException If server is not reachable
 */
function ensureWebSocketServerIsRunning(): void
{
    $host = env('REVERB_HOST', '127.0.0.1');
    $port = env('REVERB_PORT', 8080);

    $connection = @fsockopen($host, (int) $port, $errno, $errstr, 1);

    if (! $connection) {
        throw new RuntimeException(
            "Integration tests require a running WebSocket server.\n\n" .
            "For local development:\n" .
            "  php artisan reverb:start\n\n" .
            "Expected server at {$host}:{$port}\n" .
            "Configure via REVERB_HOST and REVERB_PORT environment variables."
        );
    }

    fclose($connection);
}

/**
 * Create a test Resonance instance with default test configuration.
 */
function createTestResonance(array $options = []): Resonance
{
    return new Resonance(array_merge([
        'broadcaster' => 'reverb',
        'key' => env('REVERB_APP_KEY', 'app-key'),
        'secret' => env('REVERB_APP_SECRET', 'app-secret'),
        'wsHost' => env('REVERB_HOST', '127.0.0.1'),
        'wsPort' => (int) env('REVERB_PORT', 8080),
        'forceTLS' => env('REVERB_SCHEME', 'http') === 'https',
        'namespace' => 'App.Events',
    ], $options));
}

/**
 * Run the ReactPHP event loop for a specified duration.
 *
 * @param  float  $maxSeconds  Maximum time to run the loop
 * @param  callable|null  $until  Optional callback that returns true when loop should stop
 * @param  float  $checkInterval  How often to check the $until callback
 */
function runEventLoop(float $maxSeconds = 1.0, ?callable $until = null, float $checkInterval = 0.01): void
{
    $loop = Loop::get();
    $startTime = microtime(true);

    if ($until !== null) {
        $timer = $loop->addPeriodicTimer($checkInterval, function () use ($loop, $until, &$timer) {
            if ($until()) {
                $loop->cancelTimer($timer);
                $loop->stop();
            }
        });
    }

    $loop->addTimer($maxSeconds, function () use ($loop) {
        $loop->stop();
    });

    $loop->run();
}

/**
 * Trigger a server-side event via the Pusher HTTP API.
 *
 * This allows testing that clients receive events broadcast from the server.
 */
function triggerServerEvent(string $channel, string $event, array $data = []): void
{
    $host = env('REVERB_HOST', '127.0.0.1');
    $port = env('REVERB_PORT', 8080);
    $appId = env('REVERB_APP_ID', 'app-id');
    $key = env('REVERB_APP_KEY', 'app-key');
    $secret = env('REVERB_APP_SECRET', 'app-secret');

    $body = json_encode([
        'name' => $event,
        'channel' => $channel,
        'data' => json_encode($data),
    ]);

    $path = "/apps/{$appId}/events";
    $timestamp = time();

    $params = [
        'auth_key' => $key,
        'auth_timestamp' => $timestamp,
        'auth_version' => '1.0',
        'body_md5' => md5($body),
    ];

    ksort($params);
    $queryString = http_build_query($params);

    $stringToSign = "POST\n{$path}\n{$queryString}";
    $signature = hash_hmac('sha256', $stringToSign, $secret);

    $url = "http://{$host}:{$port}{$path}?{$queryString}&auth_signature={$signature}";

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n",
            'content' => $body,
        ],
    ]);

    file_get_contents($url, false, $context);
}
