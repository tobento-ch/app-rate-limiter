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
 * RateLimiterFactoryInterface
 */
interface RateLimiterFactoryInterface
{
    /**
     * Create a rate limiter.
     *
     * @param string $id A unique identifier.
     * @param array $config
     * @return RateLimiterInterface
     */
    public function createRateLimiter(string $id, array $config = []): RateLimiterInterface;
}