<?php

declare(strict_types=1);

namespace ArtisanBuild\Resonance\Channel;

use Closure;

/**
 * This class represents a Pusher presence channel.
 */
class PusherPresenceChannel extends PusherPrivateChannel implements PresenceChannel
{
    /**
     * Register a callback to be called anytime the member list changes.
     */
    public function here(Closure $callback): static
    {
        $this->on('pusher:subscription_succeeded', function ($data) use ($callback) {
            // callback(Object.keys(data.members).map((k) => data.members[k]));
            $callback(array_values($data->members));
        });

        return $this;
    }

    /**
     * Listen for someone joining the channel.
     */
    public function joining(Closure $callback): static
    {
        $this->on('pusher:member_added', fn ($member) => $callback($member->info));

        return $this;
    }

    /**
     * Send a whisper event to other clients in the channel.
     */
    public function whisper(string $eventName, mixed $data): static
    {
        $this->pusher->channels->channels[$this->name]->trigger("client-{$eventName}", $data);

        return $this;
    }

    /**
     * Listen for someone leaving the channel.
     */
    public function leaving(Closure $callback): static
    {
        $this->on('pusher:member_removed', fn ($member) => $callback($member->info));

        return $this;
    }
}
