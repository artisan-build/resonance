<?php

declare(strict_types=1);

namespace ArtisanBuild\Resonance\Channel;

use Closure;

/**
 * This class represents a null channel.
 */
class NullChannel extends Channel
{
    public function __construct(
        public private(set) string $name = 'null',
    ) {}

    /**
     * Subscribe to a channel.
     */
    public function subscribe(): void
    {
        // ...
    }

    /**
     * Unsubscribe from a channel.
     */
    public function unsubscribe(): void
    {
        // ...
    }

    /**
     * Listen for an event on the channel instance.
     */
    public function listen(string $event, Closure $callback): static
    {
        return $this;
    }

    /**
     * Listen for all events on the channel instance.
     */
    public function listenToAll(Closure $callback): static
    {
        return $this;
    }

    /**
     * Stop listening for an event on the channel instance.
     */
    public function stopListening(string $event, ?Closure $callback = null): static
    {
        return $this;
    }

    /**
     * Stop listening to all events on the channel instance.
     */
    public function stopListeningToAll(?Closure $callback = null): static
    {
        return $this;
    }

    /**
     * Register a callback to be called anytime a subscription succeeds.
     */
    public function subscribed(Closure $callback): static
    {
        return $this;
    }

    /**
     * Register a callback to be called anytime an error occurs.
     */
    public function error(Closure $callback): static
    {
        return $this;
    }

    /**
     * Bind a channel to an event.
     */
    public function on(string $event, Closure $callback): static
    {
        return $this;
    }
}
