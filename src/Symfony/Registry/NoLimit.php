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

namespace Tobento\App\RateLimiter\Symfony\Registry;

use Tobento\App\RateLimiter\RegistryInterface;
use Tobento\App\RateLimiter\Symfony\RateLimiterFactory;

/**
 * NoLimit
 */
final class NoLimit implements RegistryInterface
{
    /**
     * Returns the rate limiter factory which supports the registry.
     *
     * @return string
     */
    public function getRateLimiterFactory(): null|string
    {
        return RateLimiterFactory::class;
    }
    
    /**
     * Returns the rate limiter factory config.
     *
     * @return array
     */
    public function getRateLimiterFactoryConfig(): array
    {
        return [
            'policy' => 'no_limit',
        ];
    }
}