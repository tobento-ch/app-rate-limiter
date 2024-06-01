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
use Tobento\App\RateLimiter\RateLimiterFactory;

/**
 * Named
 */
class Named implements RegistryInterface
{
    /**
     * Create a new Named.
     *
     * @param string $name
     */
    public function __construct(
        protected string $name,
    ) {}
    
    /**
     * Returns the name.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }
    
    /**
     * Returns the rate limiter factory which supports the registry.
     *
     * @return null|string
     */
    public function getRateLimiterFactory(): null|string
    {
        return null;
    }
    
    /**
     * Returns the rate limiter factory config.
     *
     * @return array
     */
    public function getRateLimiterFactoryConfig(): array
    {
        return [
            'name' => $this->name,
        ];
    }
}