<?php

/**
 * TOBENTO
 *
 * @copyright   Tobias Strub, TOBENTO
 * @license     MIT License, see LICENSE file distributed with this source code.
 * @author      Tobias Strub
 * @link        https://www.tobento.ch
 */

declare(strict_types=1);

namespace Tobento\App\RateLimiter;

/**
 * Registries
 */
final class Registries implements RegistriesInterface
{
    /**
     * @var array<string, RegistryInterface>
     */
    private array $registries = [];
    
    /**
     * Create a new Registries.
     *
     * @param RegistryInterface $fallback
     */
    public function __construct(
        private RegistryInterface $fallback,
    ) {}
    
    /**
     * Add a registry by name.
     *
     * @param string $name
     * @param RegistryInterface $registry
     * @return static $this
     */
    public function add(string $name, RegistryInterface $registry): static
    {
        $this->registries[$name] = $registry;
        return $this;
    }
    
    /**
     * Returns true if regsitry exists, otherwise false.
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->registries);
    }
    
    /**
     * Returns a registry by name.
     *
     * @param string $name
     * @return RegistryInterface
     */
    public function get(string $name): RegistryInterface
    {
        return $this->registries[$name] ?? $this->fallback;
    }
}