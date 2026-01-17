<?php

declare(strict_types=1);

namespace ArtisanBuild\Resonance\Connector;

use ArtisanBuild\Resonance\Channel\NullChannel;
use ArtisanBuild\Resonance\Channel\NullEncryptedPrivateChannel;
use ArtisanBuild\Resonance\Channel\NullPresenceChannel;
use ArtisanBuild\Resonance\Channel\NullPrivateChannel;
use Closure;

/**
 * This class creates a null connector.
 */
class NullConnector extends Connector
{
    /**
     * All of the subscribed channel names.
     */
    public array $channels = [];

    /**
     * Create a fresh connection.
     */
    public function connect(): void
    {
        // ...
    }

    /**
     * Register a callback to be called when the connection is established.
     * For NullConnector, we call it immediately since there's no real connection.
     */
    public function connected(Closure $callback): void
    {
        $callback();
    }

    /**
     * Listen for an event on a channel instance.
     */
    public function listen(string $name, string $event, Closure $callback): NullChannel
    {
        return new NullChannel;
    }

    /**
     * Get a channel instance by name.
     */
    public function channel(string $name): NullChannel
    {
        return new NullChannel;
    }

    /**
     * Get a private channel instance by name.
     */
    public function privateChannel(string $name): NullPrivateChannel
    {
        return new NullPrivateChannel;
    }

    /**
     * Get a private encrypted channel instance by name.
     */
    public function encryptedPrivateChannel(string $name): NullEncryptedPrivateChannel
    {
        return new NullEncryptedPrivateChannel;
    }

    /**
     * Get a presence channel instance by name.
     */
    public function presenceChannel(string $name): NullPresenceChannel
    {
        return new NullPresenceChannel;
    }

    /**
     * Leave the given channel, as well as its private and presence variants.
     */
    public function leave(string $name): void
    {
        // ...
    }

    /**
     * Leave the given channel.
     */
    public function leaveChannel(string $name): void
    {
        //
    }

    /**
     * Get the socket ID for the connection.
     */
    public function socketId(): ?string
    {
        return 'fake-socket-id';
    }

    /**
     * Disconnect the connection.
     */
    public function disconnect(): void
    {
        // ...
    }
}
