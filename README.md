<p align="center"><img src="/art/ChatGPT Image Jan 16, 2026, 09_01_17 PM.png" alt="Resonance Logo"></p>

# Resonance

[![Latest Version on Packagist](https://img.shields.io/packagist/v/artisan-build/resonance.svg?style=flat-square)](https://packagist.org/packages/artisan-build/resonance)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/artisan-build/resonance/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/artisan-build/resonance/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/artisan-build/resonance/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/artisan-build/resonance/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/artisan-build/resonance.svg?style=flat-square)](https://packagist.org/packages/artisan-build/resonance)

Resonance is a Laravel package that brings Laravel Echo's elegant API to pure PHP. It wraps [artisan-build/pusher-websocket-php](https://github.com/artisan-build/pusher-websocket-php) to provide seamless real-time WebSocket connectivity with Reverb, Pusher, Soketi, and other Pusher-compatible servers—all from your Laravel backend without requiring JavaScript.

## Why Resonance?

Laravel Echo is fantastic for browser-based real-time features, but what about CLI commands, queue workers, daemons, or other server-side PHP processes that need to listen for WebSocket events? Resonance fills that gap by providing Echo's familiar API in pure PHP, powered by ReactPHP's async event loop.

## Installation

```bash
composer require artisan-build/resonance
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="resonance-config"
```

## Usage

### Basic Channel Subscription

```php
use ArtisanBuild\Resonance\Resonance;

$resonance = new Resonance([
    'broadcaster' => 'reverb', // or 'pusher'
    'key' => 'your-app-key',
    'cluster' => 'mt1',
    'wsHost' => '127.0.0.1',
    'wsPort' => 6001,
    'forceTLS' => false,
]);

// Listen on a public channel
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
