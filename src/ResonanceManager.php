<?php

declare(strict_types=1);

namespace ArtisanBuild\Resonance;

use Illuminate\Support\Manager;
use InvalidArgumentException;

class ResonanceManager extends Manager
{
    /**
     * Get a Resonance connection instance.
     */
    public function connection(?string $name = null): Resonance
    {
        $name = $name ?: $this->getDefaultDriver();

        return $this->drivers[$name] ?? ($this->drivers[$name] = $this->resolve($name));
    }

    /**
     * Get the default driver name.
     */
    public function getDefaultDriver(): string
    {
        return $this->config->get('resonance.default', 'reverb');
    }

    /**
     * Resolve the given connection.
     */
    protected function resolve(string $name): Resonance
    {
        $config = $this->config->get("resonance.connections.{$name}");

        if (is_null($config)) {
            throw new InvalidArgumentException("Resonance connection [{$name}] is not configured.");
        }

        // Check for custom creator based on broadcaster type
        $broadcaster = $config['broadcaster'] ?? $name;

        if (isset($this->customCreators[$broadcaster])) {
            return $this->customCreators[$broadcaster]($this->container, $config);
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
        return $this->connection()->$method(...$parameters);
    }
}
