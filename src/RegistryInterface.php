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
 * RegistryInterface
 */
interface RegistryInterface
{
    /**
     * Returns the rate limiter factory which supports the registry.
     *
     * @return null|string
     */
    public function getRateLimiterFactory(): null|string;
    
    /**
     * Returns the rate limiter factory config.
     *
     * @return array
     */
    public function getRateLimiterFactoryConfig(): array;
}