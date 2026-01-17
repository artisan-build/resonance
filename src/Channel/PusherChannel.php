<?php

declare(strict_types=1);

namespace ArtisanBuild\Resonance\Channel;

use ArtisanBuild\Resonance\Util\EventFormatter;
use Closure;

/**
 * This class represents a Pusher channel.
 */
class PusherChannel extends Channel
{
    /**
     * The event formatter.
     */
    protected EventFormatter $eventFormatter;

    /**
     * The subscription of the channel.
     */
    protected mixed $subscription;

    /**
     * Create a new class instance.
     */
    public function __construct(
        protected mixed $pusher,
        protected mixed $name,
        protected mixed $options,
    ) {
        $this->eventFormatter = new EventFormatter($options['namespace']);

        $this->subscribe();
    }

    /**
     * Subscribe to a Pusher channel.
     */
    public function subscribe(): void
    {
        $this->subscription = $this->pusher->subscribe($this->name);
    }

    /**
     * Unsubscribe from a Pusher channel.
     */
    public function unsubscribe(): void
    {
        $this->pusher->unsubscribe($this->name);
    }

    /**
     * Listen for an event on the channel instance.
     */
    public function listen(string $event, Closure $callback): static
    {
        $this->on($this->eventFormatter->format($event), $callback);

        return $this;
    }

    /**
     * Listen for all events on the channel instance.
     */
    public function listenToAll(Closure $callback): static
    {
        $this->subscription->bind_global(function ($event, $data) use ($callback): void {
            if (str_starts_with($event, 'pusher:')) {
                return;
            }

            $namespace = preg_replace('/\./', '\\', $this->options['namespace']);

            $formattedEvent = str_starts_with($event, $namespace) ? substr($event, strlen($namespace) + 1) : ".{$event}";

            $callback($formattedEvent, $data);
        });

        return $this;
    }

    /**
     * Stop listening for an event on the channel instance.
     */
    public function stopListening(string $event, ?Closure $callback = null): static
    {
        if ($callback) {
            $this->subscription->unbind($this->eventFormatter->format($event), $callback);
        } else {
            $this->subscription->unbind($this->eventFormatter->format($event));
        }

        return $this;
    }

    /**
     * Stop listening for all events on the channel instance.
     */
    public function stopListeningToAll(?Closure $callback = null): static
    {
        if ($callback) {
            $this->subscription->unbind_global($callback);
        } else {
            $this->subscription->unbind_global();
        }

        return $this;
    }

    /**
     * Register a callback to be called anytime a subscription succeeds.
     */
    public function subscribed(Closure $callback): static
    {
        $this->on('pusher:subscription_succeeded', $callback);

        return $this;
    }

    /**
     * Register a callback to be called anytime a subscription error occurs.
     */
    public function error(Closure $callback): static
    {
        $this->on('pusher:subscription_error', $callback);

        return $this;
    }

    /**
     * Bind a channel to an event.
     */
    public function on(string $event, Closure $callback): static
    {
        $this->subscription->bind($event, $callback);

        return $this;
    }
}
