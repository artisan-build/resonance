<?php

declare(strict_types=1);

namespace ArtisanBuild\Resonance\Connector;

use ArtisanBuild\Resonance\Channel\Channel;
use ArtisanBuild\Resonance\Channel\PresenceChannel;
use Closure;

abstract class Connector
{
    /**
     * All of the subscribed channel names.
     */
    public array $channels = [];

    /**
     * Default connector options.
     */
    private array $defaultOptions = [
        'channelAuthorization' => [
            'transport' => 'http',
            'endpoint' => '/broadcasting/auth',
            'headers' => [],
        ],
        'userAuthentication' => [
            'transport' => 'http',
            'endpoint' => '/broadcasting/user-auth',
            'headers' => [],
        ],
        'broadcaster' => 'reverb',
        'key' => null,
        'authToken' => null,
        'host' => null,
        'port' => null,
        'forceTLS' => false,
        'cluster' => '',
        'namespace' => 'App.Events',
    ];

    /**
     * Connector options.
     */
    protected mixed $options;

    /**
     * Create a new class instance.
     */
    public function __construct(mixed $options)
    {
        $this->setOptions($options);
        $this->connect();
    }

    /**
     * Merge the custom options with the defaults.
     */
    protected function setOptions(array $options): void
    {
        $this->options = array_merge($this->defaultOptions, $options);

        if ($this->options['authToken']) {
            $this->options['channelAuthorization']['headers']['Authorization'] = "Bearer {$this->options['authToken']}";
            $this->options['userAuthentication']['headers']['Authorization'] = "Bearer {$this->options['authToken']}";
        }
    }

    /**
     * Create a fresh connection.
     */
    abstract public function connect(): void;

    /**
     * Register a callback to be called when the connection is established.
     */
    abstract public function connected(Closure $callback): void;

    /**
     * Get a channel instance by name.
     */
    abstract public function channel(string $channel): Channel;

    /**
     * Get a private channel instance by name.
     */
    abstract public function privateChannel(string $channel): Channel;

    /**
     * Get a private encrypted channel instance by name.
     */
    abstract public function encryptedPrivateChannel(string $channel): Channel;

    /**
     * Get a presence channel instance by name.
     */
    abstract public function presenceChannel(string $channel): PresenceChannel;

    /**
     * Listen for an event on a channel instance.
     */
    abstract public function listen(string $channel, string $event, Closure $callback): Channel;

    /**
     * Leave the given channel, as well as its private and presence variants.
     */
    abstract public function leave(string $channel): void;

    /**
     * Leave the given channel.
     */
    abstract public function leaveChannel(string $channel): void;

    /**
     * Get the socket_id of the connection.
     */
    abstract public function socketId(): ?string;

    /**
     * Disconnect from the Resonance server.
     */
    abstract public function disconnect(): void;
}
