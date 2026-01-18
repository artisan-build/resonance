<?php

namespace ArtisanBuild\Resonance\Tests;

use ArtisanBuild\Resonance\ResonanceServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use React\EventLoop\Loop;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // Ensure clean event loop state before each test
        Loop::get()->stop();
    }

    protected function tearDown(): void
    {
        // Stop any running event loop after each test
        Loop::get()->stop();

        parent::tearDown();
    }

    protected function getPackageProviders($app)
    {
        return [
            ResonanceServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        // Default to null driver for unit tests to avoid starting real connections
        $app['config']->set('resonance.default', 'null');

        $app['config']->set('resonance.connections.reverb', [
            'broadcaster' => 'reverb',
            'key' => env('REVERB_APP_KEY', 'app-key'),
            'secret' => env('REVERB_APP_SECRET', 'app-secret'),
            'wsHost' => env('REVERB_HOST', '127.0.0.1'),
            'wsPort' => (int) env('REVERB_PORT', 8080),
            'forceTLS' => false,
            'namespace' => 'App.Events',
        ]);

        $app['config']->set('resonance.connections.pusher', [
            'broadcaster' => 'pusher',
            'key' => env('PUSHER_APP_KEY', 'pusher-key'),
            'secret' => env('PUSHER_APP_SECRET', 'pusher-secret'),
            'cluster' => env('PUSHER_APP_CLUSTER', 'mt1'),
            'forceTLS' => true,
            'namespace' => 'App.Events',
        ]);

        $app['config']->set('resonance.connections.null', [
            'broadcaster' => 'null',
            'namespace' => 'App.Events',
        ]);

        $app['config']->set('resonance.namespace', 'App.Events');
    }
}
