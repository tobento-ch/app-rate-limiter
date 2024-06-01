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
 * SlidingWindow
 */
final class SlidingWindow implements RegistryInterface
{
    /**
     * Create a new SlidingWindow.
     *
     * @param int $limit
     * @param string $interval
     * @param string $id
     * @param string $storage
     * @param string $cache
     */
    public function __construct(
        private int $limit = 5,
        private string $interval = '5 minutes',
        private string $id = 'global',
        private string $storage = 'cache',
        private string $cache = 'ratelimiter',
    ) {}
    
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
            'id' => $this->id,
            'storage' => $this->storage,
            'policy' => 'sliding_window',
            'limit' => $this->limit,
            'interval' => $this->interval,
        ];
    }
}