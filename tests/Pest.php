<?php

use ArtisanBuild\Resonance\Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific
| PHPUnit test case class. By default, that class is "PHPUnit\Framework\TestCase".
| Of course, you may need to change it using the "uses()" function to bind a
| different classes or traits to your test functions.
|
*/

uses(TestCase::class)->in('Unit', 'Integration');

/*
|--------------------------------------------------------------------------
| Integration Tests
|--------------------------------------------------------------------------
|
| Integration tests require a running Pusher-compatible WebSocket server
| (Reverb, Soketi, etc). Run with: composer test:integration
|
| For local development with Laravel Herd, start Reverb:
|   php artisan reverb:start
|
| For CI, Soketi is used via Docker service container.
|
*/

uses()
    ->group('integration')
    ->beforeAll(function () {
        ensureWebSocketServerIsRunning();
    })
    ->in('Integration');
