<?php

namespace App\Publishing;

use InvalidArgumentException;

class PlatformAdapterRegistry
{
    /** @var array<string, PlatformAdapter> */
    private array $adapters = [];

    /**
     * @param  iterable<PlatformAdapter>  $adapters
     */
    public function __construct(iterable $adapters)
    {
        foreach ($adapters as $adapter) {
            $key = $adapter->key();

            if (isset($this->adapters[$key])) {
                throw new InvalidArgumentException("Duplicate platform adapter [{$key}].");
            }

            $this->adapters[$key] = $adapter;
        }
    }

    /**
     * @return list<PlatformAdapter>
     */
    public function all(): array
    {
        return array_values($this->adapters);
    }

    /**
     * @return list<string>
     */
    public function keys(): array
    {
        return array_keys($this->adapters);
    }

    public function has(string $key): bool
    {
        return isset($this->adapters[$key]);
    }

    public function get(string $key): PlatformAdapter
    {
        if (! isset($this->adapters[$key])) {
            throw new InvalidArgumentException("Unknown platform adapter [{$key}].");
        }

        return $this->adapters[$key];
    }

    /**
     * @return list<PlatformAdapter>
     */
    public function phaseOne(): array
    {
        return array_values(array_filter(
            $this->all(),
            fn (PlatformAdapter $adapter) => $adapter->phase() === 1,
        ));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function catalog(): array
    {
        return array_map(fn (PlatformAdapter $adapter) => [
            'key' => $adapter->key(),
            'label' => $adapter->label(),
            'label_zh' => $adapter->labelZh(),
            'description' => $adapter->description(),
            'mode' => $adapter->mode()->value,
            'mode_label' => $adapter->mode()->label(),
            'mode_label_zh' => $adapter->mode()->labelZh(),
            'phase' => $adapter->phase(),
            'publishes_automatically' => $adapter->publishesAutomatically(),
        ], $this->all());
    }
}
