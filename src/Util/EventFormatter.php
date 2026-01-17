<?php

declare(strict_types=1);

namespace ArtisanBuild\Resonance\Util;

/**
 * Event name formatter
 */
class EventFormatter
{
    /**
     * Create a new class instance.
     */
    public function __construct(private string|bool $namespace)
    {
        // ...
    }

    /**
     * Format the given event name.
     */
    public function format(string $event): string
    {
        if (in_array($event[0], ['.', '\\'])) {
            return substr($event, 1);
        } elseif ($this->namespace) {
            $event = "{$this->namespace}.{$event}";
        }

        return preg_replace('/\./g', '\\', $event);
    }

    /**
     * Set the event namespace.
     */
    public function setNamespace(string|bool $value): void
    {
        $this->namespace = $value;
    }
}
