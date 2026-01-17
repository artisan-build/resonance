<?php

declare(strict_types=1);

namespace ArtisanBuild\Resonance\Channel;

/**
 * This class represents a Pusher private channel.
 */
class PusherPrivateChannel extends PusherChannel
{
    /**
     * Send a whisper event to other clients in the channel.
     */
    public function whisper(string $eventName, mixed $data): static
    {
        $this->pusher->channels->channels[$this->name]->trigger("client-{$eventName}", $data);

        return $this;
    }

    public function send(string $eventName, mixed $data): static
    {
        $this->pusher->send_event($eventName, $data, $this->name);

        return $this;
    }
}
