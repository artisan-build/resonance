<?php

use ArtisanBuild\Resonance\Util\EventFormatter;

it('formats events with a namespace', function () {
    $formatter = new EventFormatter('App.Events');

    expect($formatter->format('OrderShipped'))->toBe('App\\Events\\OrderShipped');
});

it('strips leading dot and returns raw event name', function () {
    $formatter = new EventFormatter('App.Events');

    expect($formatter->format('.OrderShipped'))->toBe('OrderShipped');
});

it('strips leading backslash and returns raw event name', function () {
    $formatter = new EventFormatter('App.Events');

    expect($formatter->format('\\OrderShipped'))->toBe('OrderShipped');
});

it('formats events without namespace when namespace is false', function () {
    $formatter = new EventFormatter(false);

    expect($formatter->format('OrderShipped'))->toBe('OrderShipped');
});

it('converts dots to backslashes in namespace', function () {
    $formatter = new EventFormatter('App.Domain.Events');

    expect($formatter->format('OrderShipped'))->toBe('App\\Domain\\Events\\OrderShipped');
});

it('converts dots to backslashes in event name', function () {
    $formatter = new EventFormatter(false);

    expect($formatter->format('Order.Shipped'))->toBe('Order\\Shipped');
});

it('can update the namespace', function () {
    $formatter = new EventFormatter('App.Events');

    $formatter->setNamespace('Custom.Namespace');

    expect($formatter->format('TestEvent'))->toBe('Custom\\Namespace\\TestEvent');
});

it('can disable the namespace', function () {
    $formatter = new EventFormatter('App.Events');

    $formatter->setNamespace(false);

    expect($formatter->format('TestEvent'))->toBe('TestEvent');
});
