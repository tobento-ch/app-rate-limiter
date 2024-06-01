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
 * RateLimiterCreatorInterface
 */
interface RateLimiterCreatorInterface
{
    /**
     * Create a new rate limiter from the given registry.
     *
     * @param string $id A unique identifier.
     * @param RegistryInterface $registry
     * @return RateLimiterInterface
     */
    public function createFromRegistry(string $id, RegistryInterface $registry): RateLimiterInterface;
}