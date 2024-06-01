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
 * TokenBucket
 */
final class TokenBucket implements RegistryInterface
{
    /**
     * Create a new TokenBucket.
     *
     * @param int $limit
     * @param int $rateAmount
     * @param string $rateInterval
     * @param string $id
     * @param string $storage
     * @param string $cache
     */
    public function __construct(
        private int $limit = 5,
        private int $rateAmount = 1,
        private string $rateInterval = '5 minutes',
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
            'policy' => 'token_bucket',
            'limit' => $this->limit,
            'rate' => ['amount' => $this->rateAmount, 'interval' => $this->rateInterval],
        ];
    }
}