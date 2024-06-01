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

namespace Tobento\App\RateLimiter\Registry;

use Tobento\App\RateLimiter\RegistryInterface;

/**
 * Factory
 */
class Factory implements RegistryInterface
{
    /**
     * Create a new Factory.
     *
     * @param string $factory
     * @param array $config
     */
    public function __construct(
        protected string $factory,
        protected array $config = [],
    ) {}
    
    /**
     * Returns the rate limiter factory which supports the registry.
     *
     * @return null|string
     */
    public function getRateLimiterFactory(): null|string
    {
        return $this->factory;
    }
    
    /**
     * Returns the rate limiter factory config.
     *
     * @return array
     */
    public function getRateLimiterFactoryConfig(): array
    {
        return $this->config;
    }
}