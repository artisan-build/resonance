<?php

use ArtisanBuild\Resonance\Facades\Resonance;
use ArtisanBuild\Resonance\Resonance as ResonanceInstance;
use ArtisanBuild\Resonance\ResonanceManager;

it('resolves the default connection', function () {
    $resonance = Resonance::connection();

    expect($resonance)->toBeInstanceOf(ResonanceInstance::class);
});

it('resolves a named connection', function () {
    $resonance = Resonance::connection('reverb');

    expect($resonance)->toBeInstanceOf(ResonanceInstance::class);
});

it('resolves the null connection', function () {
    $resonance = Resonance::connection('null');

    expect($resonance)->toBeInstanceOf(ResonanceInstance::class);
});

it('caches connection instances', function () {
    $first = Resonance::connection('null');
    $second = Resonance::connection('null');

    expect($first)->toBe($second);
});

it('throws for unconfigured connections', function () {
    Resonance::connection('nonexistent');
})->throws(InvalidArgumentException::class, 'Driver [nonexistent] not supported.');

it('returns the default driver name from config', function () {
    $manager = app(ResonanceManager::class);

    expect($manager->getDefaultDriver())->toBe('reverb');
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
    config()->set('resonance.default', 'null');

    // Clear the cached driver so it picks up the new default
    app()->forgetInstance(ResonanceManager::class);

    // This should not throw - it proxies to the null connection
    // NullConnector returns 'fake-socket-id' for testing purposes
    $socketId = Resonance::socketId();

    expect($socketId)->toBe('fake-socket-id');
});
