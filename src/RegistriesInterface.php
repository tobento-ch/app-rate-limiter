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
 * RegistriesInterface
 */
interface RegistriesInterface
{
    /**
     * Add a registry by name.
     *
     * @param string $name
     * @param RegistryInterface $registry
     * @return static $this
     */
    public function add(string $name, RegistryInterface $registry): static;
    
    /**
     * Returns true if regsitry exists, otherwise false.
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool;
    
    /**
     * Returns a registry by name.
     *
     * @param string $name
     * @return RegistryInterface
     */
    public function get(string $name): RegistryInterface;
}