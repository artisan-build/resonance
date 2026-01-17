<?php

declare(strict_types=1);

namespace ArtisanBuild\Resonance\Channel;

/**
 * This class represents a null private channel.
 */
class NullEncryptedPrivateChannel extends NullChannel
{
    /**
     * Send a whisper event to other clients in the channel.
     */
    public function whisper(string $eventName, mixed $data): static
    {
        return $this;
    }
}
