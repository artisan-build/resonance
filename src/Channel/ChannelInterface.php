<?php

declare(strict_types=1);

namespace ArtisanBuild\Resonance\Channel;

use Closure;

/**
 * This interface defines the base channel contract.
 */
interface ChannelInterface
{
    /**
     * The channel name.
     */
    public string $name { get; }

    /**
     * Listen for an event on the channel instance.
     */
    public function listen(string $event, Closure $callback): static;

    /**
     * Listen for a whisper event on the channel instance.
     */
    public function listenForWhisper(string $event, Closure $callback): static;

    /**
     * Listen for all events on the channel instance.
     */
    public function listenToAll(Closure $callback): static;

    /**
     * Listen for an event on the channel instance.
     */
    public function notification(Closure $callback): static;

    /**
     * Stop listening to an event on the channel instance.
     */
    public function stopListening(string $event, ?Closure $callback = null): static;

    /**
     * Stop listening for a whisper event on the channel instance.
     */
    public function stopListeningForWhisper(string $event, ?Closure $callback = null): static;

    /**
     * Register a callback to be called anytime a subscription succeeds.
     */
    public function subscribed(Closure $callback): static;

    /**
     * Register a callback to be called anytime an error occurs.
     */
    public function error(Closure $callback): static;

    /**
     * Unsubscribe from the channel.
     */
    public function unsubscribe(): void;

    /**
     * Stop listening to all events on the channel instance.
     */
    public function stopListeningToAll(?Closure $callback = null): static;
}
