<?php

declare(strict_types=1);

namespace ArtisanBuild\Resonance;

use ArtisanBuild\Resonance\Channel\Channel;
use ArtisanBuild\Resonance\Channel\PresenceChannel;
use ArtisanBuild\Resonance\Connector\Connector;
use ArtisanBuild\Resonance\Connector\NullConnector;
use ArtisanBuild\Resonance\Connector\PusherConnector;
use Closure;
use Exception;

/**
 * This class is the primary API for interacting with broadcasting.
 */
class Resonance
{
    /**
     * The broadcasting connector.
     */
    // protected Connector|PusherConnector $connector;
    protected PusherConnector $connector;

    /**
     * The Echo options.
     */
    protected array $options;

    /**
     * Create a new class instance.
     */
    public function __construct(array $options)
    {
        $this->options = $options;
        $this->connect();

        // if (! $this->options['withoutInterceptors']) {
        //     $this->registerInterceptors();
        // }
    }

    /**
     * Get a channel instance by name.
     */
    public function channel(string $channel): Channel
    {
        return $this->connector->channel($channel);
    }

    /**
     * Create a new connection.
     */
    protected function connect(): void
    {
        $this->connector = match ($this->options['broadcaster']) {
            'reverb' => new PusherConnector([...$this->options, 'cluster' => '']),
            'pusher' => new PusherConnector($this->options),
            // 'socket.io' => new SocketIoConnector($this->options),
            'null' => new NullConnector($this->options),
            // 'function' => new $this->options['broadcaster']($this->options),
            default => throw new Exception(
                "Broadcaster {$this->options['broadcaster']} is not supported."
            ),
        };
    }

    /**
     * Disconnect from the Echo server.
     */
    public function disconnect(): void
    {
        $this->connector->disconnect();
    }

    /**
     * Register a callback to be called when the connection is established.
     */
    public function connected(Closure $callback): void
    {
        $this->connector->connected($callback);
    }

    /**
     * Get a presence channel instance by name.
     */
    public function join(string $channel): PresenceChannel
    {
        return $this->connector->presenceChannel($channel);
    }

    /**
     * Leave the given channel, as well as its private and presence variants.
     */
    public function leave(string $channel): void
    {
        $this->connector->leave($channel);
    }

    /**
     * Leave the given channel.
     */
    public function leaveChannel(string $channel): void
    {
        $this->connector->leaveChannel($channel);
    }

    /**
     * Leave all channels.
     */
    public function leaveAllChannels(): void
    {
        foreach ($this->connector->channels as $channel) {
            $this->leaveChannel($channel);
        }
    }

    /**
     * Listen for an event on a channel instance.
     */
    public function listen(string $channel, string $event, Closure $callback): Channel
    {
        return $this->connector->listen($channel, $event, $callback);
    }

    /**
     * Get a private channel instance by name.
     */
    public function private(string $channel): Channel
    {
        return $this->connector->privateChannel($channel);
    }

    /**
     * Get a private encrypted channel instance by name.
     */
    public function encryptedPrivate(string $channel): Channel
    {
        // if ((this.connector as any) instanceof SocketIoConnector) {
        //     throw new Error(
        //         `Broadcaster ${typeof this.options.broadcaster} ${
        //             this.options.broadcaster
        //         } does not support encrypted private channels.`
        //     );
        // }

        return $this->connector->encryptedPrivateChannel($channel);
    }

    /**
     * Get the Socket ID for the connection.
     */
    public function socketId(): ?string
    {
        return $this->connector->socketId();
    }

    /**
     * Register 3rd party request interceptiors. These are used to automatically
     * send a connections socket id to a Laravel app with a X-Socket-Id header.
     */
    // registerInterceptors(): void {
    //     if (typeof Vue === 'function' && Vue.http) {
    //         this.registerVueRequestInterceptor();
    //     }

    //     if (typeof axios === 'function') {
    //         this.registerAxiosRequestInterceptor();
    //     }

    //     if (typeof jQuery === 'function') {
    //         this.registerjQueryAjaxSetup();
    //     }

    //     if (typeof Turbo === 'object') {
    //         this.registerTurboRequestInterceptor();
    //     }
    // }

    /**
     * Register a Vue HTTP interceptor to add the X-Socket-ID header.
     */
    // registerVueRequestInterceptor(): void {
    //     Vue.http.interceptors.push((request, next) => {
    //         if (this.socketId()) {
    //             request.headers.set('X-Socket-ID', this.socketId());
    //         }

    //         next();
    //     });
    // }

    /**
     * Register an Axios HTTP interceptor to add the X-Socket-ID header.
     */
    // registerAxiosRequestInterceptor(): void {
    //     axios.interceptors.request.use((config) => {
    //         if (this.socketId()) {
    //             config.headers['X-Socket-Id'] = this.socketId();
    //         }

    //         return config;
    //     });
    // }

    /**
     * Register jQuery AjaxPrefilter to add the X-Socket-ID header.
     */
    // registerjQueryAjaxSetup(): void {
    //     if (typeof jQuery.ajax != 'undefined') {
    //         jQuery.ajaxPrefilter((options, originalOptions, xhr) => {
    //             if (this.socketId()) {
    //                 xhr.setRequestHeader('X-Socket-Id', this.socketId());
    //             }
    //         });
    //     }
    // }

    /**
     * Register the Turbo Request interceptor to add the X-Socket-ID header.
     */
    // registerTurboRequestInterceptor(): void {
    //     document.addEventListener('turbo:before-fetch-request', (event: any) => {
    //         event.detail.fetchOptions.headers['X-Socket-Id'] = this.socketId();
    //     });
    // }
}
