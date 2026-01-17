<?php

declare(strict_types=1);

namespace ArtisanBuild\Resonance\Connector;

use ArtisanBuild\Resonance\Channel\PusherChannel;
use ArtisanBuild\Resonance\Channel\PusherEncryptedPrivateChannel;
use ArtisanBuild\Resonance\Channel\PusherPresenceChannel;
use ArtisanBuild\Resonance\Channel\PusherPrivateChannel;
use ArtisanBuild\Pusher\Pusher;
use Closure;

/**
 * This class creates a connector to Pusher.
 */
class PusherConnector extends Connector
{
    /**
     * The Pusher instance.
     */
    protected Pusher $pusher;

    /**
     * All of the subscribed channel names.
     */
    public array $channels = [];

    /**
     * Create a fresh Pusher connection.
     */
    public function connect(): void
    {
        $useTLS = ($this->options['scheme'] ?? 'https') === 'https'
            || ($this->options['scheme'] ?? 'wss') === 'wss';

        $pusherOptions = new \ArtisanBuild\Pusher\Options(
            channelAuthorization: $this->options['channelAuthorization'] ?? null,
            userAuthentication: $this->options['userAuthentication'] ?? null,
            cluster: $this->options['cluster'] ?? '',
            wsHost: $this->options['host'] ?? null,
            wsPort: isset($this->options['port']) ? (int) $this->options['port'] : null,
            wssPort: isset($this->options['port']) ? (int) $this->options['port'] : null,
            forceTLS: $useTLS,
        );

        // if (typeof this.options.client !== 'undefined') {
        if (array_key_exists('client', $this->options) && $this->options['client']) {
            $this->pusher = $this->options['client'];
        } elseif (
            array_key_exists('Pusher', $this->options) &&
            $this->options['Pusher'] &&
            class_exists($this->options['Pusher'])
        ) {
            $this->pusher = new $this->options['Pusher']($this->options['key'], $pusherOptions);
        } else {
            $this->pusher = new Pusher($this->options['key'], $pusherOptions);
        }

        $this->pusher->connect();
    }

    /**
     * Sign in the user via Pusher user authentication (https://pusher.com/docs/channels/using_channels/user-authentication/).
     */
    public function signin(): void
    {
        $this->pusher->signin();
    }

    /**
     * Register a callback to be called when the connection is established.
     */
    public function connected(Closure $callback): void
    {
        $this->pusher->connection->bind('connected', $callback);
    }

    /**
     * Listen for an event on a channel instance.
     */
    public function listen(string $name, string $event, Closure $callback): PusherChannel|PusherPrivateChannel|PusherEncryptedPrivateChannel|PusherPresenceChannel
    {
        return $this->channel($name)->listen($event, $callback);
    }

    /**
     * Get a channel instance by name.
     */
    public function channel(string $name): PusherChannel|PusherPrivateChannel|PusherEncryptedPrivateChannel|PusherPresenceChannel
    {
        if (! array_key_exists($name, $this->channels)) {
            $this->channels[$name] = new PusherChannel(
                $this->pusher,
                $name,
                $this->options,
            );
        }

        return $this->channels[$name];
    }

    /**
     * Get a private channel instance by name.
     */
    public function privateChannel(string $name): PusherPrivateChannel
    {
        if (! array_key_exists("private-{$name}", $this->channels)) {
            $this->channels["private-{$name}"] = new PusherPrivateChannel(
                $this->pusher,
                "private-{$name}",
                $this->options,
            );
        }

        return $this->channels["private-{$name}"];
    }

    /**
     * Get a private encrypted channel instance by name.
     */
    public function encryptedPrivateChannel(string $name): PusherEncryptedPrivateChannel
    {
        if (! array_key_exists("private-encrypted-{$name}", $this->channels)) {
            $this->channels["private-encrypted-{$name}"] = new PusherEncryptedPrivateChannel(
                $this->pusher,
                "private-encrypted-{$name}",
                $this->options,
            );
        }

        return $this->channels["private-encrypted-{$name}"];
    }

    /**
     * Get a presence channel instance by name.
     */
    public function presenceChannel(string $name): PusherPresenceChannel
    {
        if (! array_key_exists("presence-{$name}", $this->channels)) {
            $this->channels["presence-{$name}"] = new PusherPresenceChannel(
                $this->pusher,
                "presence-{$name}",
                $this->options,
            );
        }

        return $this->channels["presence-{$name}"];
    }

    /**
     * Leave the given channel, as well as its private and presence variants.
     */
    public function leave(string $name): void
    {
        $channels = [$name, "private-{$name}", "private-encrypted-{$$name}", "presence-{$name}"];

        foreach ($channels as $channel) {
            $this->leaveChannel($channel);
        }
    }

    /**
     * Leave the given channel.
     */
    public function leaveChannel(string $name): void
    {
        if (array_key_exists($name, $this->channels)) {
            $this->channels[$name]->unsubscribe();

            unset($this->channels[$name]);
        }
    }

    /**
     * Get the socket ID for the connection.
     */
    public function socketId(): ?string
    {
        return $this->pusher->connection->socket_id ?? null;
    }

    /**
     * Disconnect Pusher connection.
     */
    public function disconnect(): void
    {
        $this->pusher->disconnect();
    }
}
