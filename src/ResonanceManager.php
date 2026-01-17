<?php

declare(strict_types=1);

namespace ArtisanBuild\Resonance;

use ArtisanBuild\Resonance\Connector\NullConnector;
use ArtisanBuild\Resonance\Connector\PusherConnector;
use Closure;
use Illuminate\Support\Manager;
use InvalidArgumentException;

class ResonanceManager extends Manager
{
    /**
     * Get a Resonance connection instance.
     */
    public function connection(?string $name = null): Resonance
    {
        return $this->driver($name);
    }

    /**
     * Get the default driver name.
     */
    public function getDefaultDriver(): string
    {
        return $this->config->get('resonance.default', 'reverb');
    }

    /**
     * Create a Reverb driver instance.
     */
    protected function createReverbDriver(): Resonance
    {
        return $this->createConnection('reverb');
    }

    /**
     * Create a Pusher driver instance.
     */
    protected function createPusherDriver(): Resonance
    {
        return $this->createConnection('pusher');
    }

    /**
     * Create a Null driver instance.
     */
    protected function createNullDriver(): Resonance
    {
        return $this->createConnection('null');
    }

    /**
     * Create a new Resonance connection instance.
     */
    protected function createConnection(string $name): Resonance
    {
        $config = $this->config->get("resonance.connections.{$name}");

        if (is_null($config)) {
            throw new InvalidArgumentException("Resonance connection [{$name}] is not configured.");
        }

        return new Resonance([
            ...$config,
            'namespace' => $config['namespace'] ?? $this->config->get('resonance.namespace', 'App.Events'),
        ]);
    }

    /**
     * Dynamically call the default driver instance.
     */
    public function __call($method, $parameters)
    {
        return $this->driver()->$method(...$parameters);
    }
}
