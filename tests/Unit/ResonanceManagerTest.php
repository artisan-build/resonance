<?php

use ArtisanBuild\Resonance\Facades\Resonance;
use ArtisanBuild\Resonance\Resonance as ResonanceInstance;
use ArtisanBuild\Resonance\ResonanceManager;

it('resolves the default connection', function () {
    // Default is 'null' to avoid starting real WebSocket connections
    $resonance = Resonance::connection();

    expect($resonance)->toBeInstanceOf(ResonanceInstance::class);
});

it('resolves a named connection', function () {
    // Use 'null' driver to avoid starting real WebSocket connections
    // The manager pattern works the same regardless of driver
    $resonance = Resonance::connection('null');

    expect($resonance)->toBeInstanceOf(ResonanceInstance::class);
});

it('caches connection instances', function () {
    $first = Resonance::connection();
    $second = Resonance::connection();

    expect($first)->toBe($second);
});

it('throws for unconfigured connections', function () {
    Resonance::connection('nonexistent');
})->throws(InvalidArgumentException::class, 'Resonance connection [nonexistent] is not configured.');

it('returns the default driver name from config', function () {
    $manager = app(ResonanceManager::class);

    // TestCase sets default to 'null' to avoid starting real connections
    expect($manager->getDefaultDriver())->toBe('null');
});

it('can extend with custom drivers', function () {
    $called = false;

    Resonance::extend('custom', function ($app) use (&$called) {
        $called = true;
        return new ResonanceInstance([
            'broadcaster' => 'null',
            'namespace' => 'App.Events',
        ]);
    });

    config()->set('resonance.connections.custom', [
        'broadcaster' => 'custom',
    ]);

    $resonance = Resonance::connection('custom');

    expect($called)->toBeTrue();
    expect($resonance)->toBeInstanceOf(ResonanceInstance::class);
});

it('proxies method calls to the default connection', function () {
    // Default is already 'null' in TestCase
    // NullConnector returns 'fake-socket-id' for testing purposes
    $socketId = Resonance::socketId();

    expect($socketId)->toBe('fake-socket-id');
});
