<?php

declare(strict_types=1);

namespace ArtisanBuild\Resonance\Channel;

use Closure;

/**
 * This interface represents a presence channel.
 */
interface PresenceChannel
{
    /**
     * Register a callback to be called anytime the member list changes.
     */
    public function here(Closure $callback): static;

    /**
     * Listen for someone joining the channel.
     */
    public function joining(Closure $callback): static;

    /**
     * Send a whisper event to other clients in the channel.
     */
    public function whisper(string $eventName, mixed $data): static;

    /**
     * Listen for someone leaving the channel.
     */
    public function leaving(Closure $callback): static;
}
