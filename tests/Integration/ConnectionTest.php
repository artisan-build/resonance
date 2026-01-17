<?php

use ArtisanBuild\Resonance\Resonance;

it('can establish a connection', function () {
    $resonance = createTestResonance();
    $connected = false;

    $resonance->connected(function () use (&$connected, $resonance) {
        $connected = true;
        $resonance->disconnect();
    });

    runEventLoop(5.0, fn () => $connected);

    expect($connected)->toBeTrue();
});

it('receives a socket id after connecting', function () {
    $resonance = createTestResonance();
    $socketId = null;

    $resonance->connected(function () use (&$socketId, $resonance) {
        $socketId = $resonance->socketId();
        $resonance->disconnect();
    });

    runEventLoop(5.0, fn () => $socketId !== null);

    expect($socketId)->toMatch('/^\d+\.\d+$/');
});

it('can disconnect from the server', function () {
    $resonance = createTestResonance();
    $disconnected = false;

    $resonance->connected(function () use (&$disconnected, $resonance) {
        $resonance->disconnect();
        $disconnected = true;
    });

    runEventLoop(5.0, fn () => $disconnected);

    expect($disconnected)->toBeTrue();
});

it('can subscribe to a public channel', function () {
    $resonance = createTestResonance();
    $subscribed = false;

    $resonance->connected(function () use (&$subscribed, $resonance) {
        $channel = $resonance->channel('test-channel');

        $channel->subscribed(function () use (&$subscribed, $resonance) {
            $subscribed = true;
            $resonance->disconnect();
        });
    });

    runEventLoop(5.0, fn () => $subscribed);

    expect($subscribed)->toBeTrue();
});

it('can listen for events on a channel', function () {
    $resonance = createTestResonance();
    $eventReceived = false;
    $eventData = null;

    $resonance->connected(function () use (&$eventReceived, &$eventData, $resonance) {
        $channel = $resonance->channel('test-channel');

        $channel->subscribed(function () use ($channel) {
            // Trigger an event from the server after subscribing
            triggerServerEvent('test-channel', 'TestEvent', ['message' => 'hello']);
        });

        $channel->listen('.TestEvent', function ($data) use (&$eventReceived, &$eventData, $resonance) {
            $eventReceived = true;
            $eventData = $data;
            $resonance->disconnect();
        });
    });

    runEventLoop(5.0, fn () => $eventReceived);

    expect($eventReceived)->toBeTrue();
    expect($eventData)->toHaveKey('message', 'hello');
});

it('can leave a channel', function () {
    $resonance = createTestResonance();
    $left = false;

    $resonance->connected(function () use (&$left, $resonance) {
        $resonance->channel('test-channel');
        $resonance->leave('test-channel');
        $left = true;
        $resonance->disconnect();
    });

    runEventLoop(5.0, fn () => $left);

    expect($left)->toBeTrue();
});
