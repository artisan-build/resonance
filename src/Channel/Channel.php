<?php

declare(strict_types=1);

namespace ArtisanBuild\Resonance\Channel;

use Closure;

/**
 * This class represents a basic channel.
 */
abstract class Channel
{
    /**
     * The Resonance options.
     */
    protected mixed $options;

    /**
     * Listen for an event on the channel instance.
     */
    abstract public function listen(string $event, Closure $callback): static;

    /**
     * Listen for a whisper event on the channel instance.
     */
    public function listenForWhisper(string $event, Closure $callback): static
    {
        return $this->listen(".client-{$event}", $callback);
    }

    /**
     * Listen for an event on the channel instance.
     */
    public function notification(Closure $callback): static
    {
        return $this->listen('.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated', $callback);
    }

    /**
     * Stop listening to an event on the channel instance.
     */
    abstract public function stopListening(string $event, ?Closure $callback = null): static;

    /**
     * Stop listening for a whisper event on the channel instance.
     */
    public function stopListeningForWhisper(string $event, ?Closure $callback = null): static
    {
        return $this->stopListening(".client-{$event}", $callback);
    }

    /**
     * Register a callback to be called anytime a subscription succeeds.
     */
    abstract public function subscribed(Closure $callback): static;

    /**
     * Register a callback to be called anytime an error occurs.
     */
    abstract public function error(Closure $callback): static;
}
