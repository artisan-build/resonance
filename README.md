<p align="center"><img src="https://raw.githubusercontent.com/artisan-build/resonance/main/art/ChatGPT%20Image%20Jan%2016,%202026,%2009_01_17%20PM.png" alt="Resonance Logo"></p>

# Resonance

[![Latest Version on Packagist](https://img.shields.io/packagist/v/artisan-build/resonance.svg?style=flat-square)](https://packagist.org/packages/artisan-build/resonance)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/artisan-build/resonance/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/artisan-build/resonance/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/artisan-build/resonance/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/artisan-build/resonance/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/artisan-build/resonance.svg?style=flat-square)](https://packagist.org/packages/artisan-build/resonance)

Resonance is a Laravel WebSocket client for CLI commands, queue workers, and background processes. Inspired by Laravel Echo's API, it provides a familiar interface for real-time event listening—but from your PHP backend instead of the browser.

Built on [artisan-build/pusher-websocket-php](https://github.com/artisan-build/pusher-websocket-php) and ReactPHP's async event loop, Resonance connects to Reverb, Pusher, Soketi, and other Pusher-compatible servers.

## Why Resonance?

Laravel Echo handles real-time features in the browser, but what about server-side PHP processes that need to listen for WebSocket events? Resonance fills that gap with:

- **Echo-inspired API** - Familiar `listen()`, `private()`, `join()` methods
- **Manager pattern** - Connect to multiple WebSocket servers simultaneously
- **Extensible drivers** - Add custom broadcasters with `extend()`
- **Laravel-native** - Config files, facades, and service providers out of the box

## Installation

```bash
composer require artisan-build/resonance
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="resonance-config"
```

## Configuration

Resonance follows Laravel's convention of defining connections in config and selecting via environment variable:

```php
// config/resonance.php
return [
    'default' => env('RESONANCE_CONNECTION', 'reverb'),

    'connections' => [
        'reverb' => [
            'broadcaster' => 'reverb',
            'key' => env('REVERB_APP_KEY'),
            'secret' => env('REVERB_APP_SECRET'),
            'wsHost' => env('REVERB_HOST', '127.0.0.1'),
            'wsPort' => env('REVERB_PORT', 8080),
            'forceTLS' => env('REVERB_SCHEME', 'https') === 'https',
        ],

        'pusher' => [
            'broadcaster' => 'pusher',
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'cluster' => env('PUSHER_APP_CLUSTER', 'mt1'),
            'forceTLS' => true,
        ],

        'null' => [
            'broadcaster' => 'null',
        ],
    ],

    'namespace' => 'App.Events',
];
```

Use `RESONANCE_CONNECTION=null` in your `.env.testing` to disable WebSocket connections during tests.

## Usage

### Basic Channel Subscription

```php
use ArtisanBuild\Resonance\Facades\Resonance;

// Using the default connection from config
$channel = Resonance::channel('orders');

$channel->listen('OrderShipped', function ($event) {
    echo "Order shipped: " . $event['order_id'];
});
```

### Multiple Connections

Resonance uses the Manager pattern, allowing you to work with multiple connections:

```php
use ArtisanBuild\Resonance\Facades\Resonance;

// Use a specific connection
Resonance::connection('reverb')->listen('orders', 'OrderShipped', function ($event) {
    // Handle internal events from your Reverb server
});

Resonance::connection('pusher')->listen('analytics', 'PageView', function ($event) {
    // Handle events from a third-party Pusher service
});
```

### Custom Drivers

Extend Resonance with custom broadcasters:

```php
use ArtisanBuild\Resonance\Facades\Resonance;

Resonance::extend('ably', function ($app, $config) {
    return new AblyConnector($config);
});
```

### Direct Instantiation

Or instantiate directly with custom options:

```php
use ArtisanBuild\Resonance\Resonance;

$resonance = new Resonance([
    'broadcaster' => 'reverb',
    'key' => 'your-app-key',
    'wsHost' => '127.0.0.1',
    'wsPort' => 8080,
    'forceTLS' => false,
    'namespace' => 'App.Events',
]);

$resonance->listen('orders', 'OrderShipped', function ($event) {
    echo "Order shipped: " . $event['order_id'];
});
```

### Private Channels

```php
// Subscribe to a private channel
$channel = $resonance->private('orders.123');

$channel->listen('OrderUpdated', function ($event) {
    // Handle the event
});
```

### Presence Channels

```php
// Join a presence channel
$presence = $resonance->join('chat.room.1');

$presence->here(function ($users) {
    // Users currently in the channel
});

$presence->joining(function ($user) {
    // A user joined
});

$presence->leaving(function ($user) {
    // A user left
});

$presence->listen('NewMessage', function ($event) {
    // Handle chat message
});
```

### Connection Management

```php
// Register a callback for when connection is established
$resonance->connected(function () {
    echo "Connected to WebSocket server!";
});

// Get the socket ID (useful for excluding sender)
$socketId = $resonance->socketId();

// Leave a specific channel
$resonance->leave('orders');

// Leave a single channel variant
$resonance->leaveChannel('private-orders.123');

// Disconnect entirely
$resonance->disconnect();
```

### Encrypted Private Channels

```php
$channel = $resonance->encryptedPrivate('sensitive-data');

$channel->listen('SecretEvent', function ($event) {
    // Decrypted event data
});
```

## Real-World Example: CLI Chat with Community Prompts

Here's a real-time chat application built with `AsyncPrompt` from [artisan-build/community-prompts](https://github.com/artisan-build/community-prompts), demonstrating how Resonance enables interactive CLI tools:

```php
<?php

namespace App\Console\Prompts;

use ArtisanBuild\Resonance\Resonance;
use Illuminate\Support\Collection;
use Laravel\Prompts\AsyncPrompt;
use Laravel\Prompts\Key;

class ChatPrompt extends AsyncPrompt
{
    protected Resonance $socket;
    protected mixed $channel;
    public Collection $messages;
    public bool $typing = false;

    public function __construct()
    {
        $this->messages = collect([['system', 'Connecting...']]);

        $this->on('key', fn ($key) => match ($key) {
            Key::ENTER => $this->sendMessage($this->value()),
            Key::ESCAPE => $this->disconnect(),
            default => null,
        });

        $this->connect();
    }

    protected function connect(): void
    {
        $this->socket = new Resonance([
            'broadcaster' => 'reverb',
            'key' => config('broadcasting.connections.reverb.key'),
            'wsHost' => '127.0.0.1',
            'wsPort' => 8080,
            'forceTLS' => false,
        ]);

        $this->socket->connected(function () {
            $this->channel = $this->socket->private('chat');

            // Listen for all events on the channel
            $this->channel->listenToAll(function ($event, $data) {
                $this->handleEvent($event, $data);
            });

            // Or listen for specific events
            $this->channel->listen('MessageSent', function ($data) {
                $this->messages->push(['left', $data['message']]);
                $this->render();
            });

            // Listen for client whispers (peer-to-peer)
            $this->channel->listenForWhisper('typing', function ($data) {
                $this->typing = true;
                $this->render();
            });

            $this->messages = collect([['system', 'Connected! Start chatting...']]);
            $this->render();
        });
    }

    protected function sendMessage(string $message): void
    {
        if (empty($message)) return;

        // Send a whisper to other clients (no server round-trip)
        $this->channel->whisper('message', ['text' => $message]);

        $this->messages->push(['right', $message]);
        $this->typedValue = '';
        $this->render();
    }

    protected function disconnect(): void
    {
        $this->socket->disconnect();
        $this->submit();
    }
}
```

This example showcases:
- **Async WebSocket connection** in a CLI environment
- **Private channel** subscription with authentication
- **`listenToAll()`** for catching all channel events
- **`listenForWhisper()`** for peer-to-peer client events
- **`whisper()`** for sending client events without server round-trips

## API Reference

### Channel Methods

| Method | Description |
|--------|-------------|
| `listen($event, $callback)` | Listen for a specific event |
| `listenToAll($callback)` | Listen for all events on the channel |
| `listenForWhisper($event, $callback)` | Listen for client whisper events |
| `stopListening($event)` | Stop listening to an event |
| `stopListeningForWhisper($event)` | Stop listening for a whisper |
| `notification($callback)` | Listen for Laravel notifications |
| `subscribed($callback)` | Callback when subscription succeeds |
| `error($callback)` | Callback when subscription fails |

### Private Channel Methods

| Method | Description |
|--------|-------------|
| `whisper($event, $data)` | Send a client event to other subscribers |

### Presence Channel Methods

| Method | Description |
|--------|-------------|
| `here($callback)` | Get current members when joining |
| `joining($callback)` | Called when a member joins |
| `leaving($callback)` | Called when a member leaves |
| `whisper($event, $data)` | Send a client event to other subscribers |

## Supported Broadcasters

| Broadcaster | Status |
|-------------|--------|
| Laravel Reverb | Supported |
| Pusher Channels | Supported |
| Soketi | Supported (via Pusher connector) |
| Ably | Planned |

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Len Woodward](https://github.com/ProjektGopher)
- [Ed Grosvenor](https://github.com/edgrosvenor)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
