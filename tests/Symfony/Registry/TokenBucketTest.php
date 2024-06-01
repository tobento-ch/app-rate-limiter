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

namespace Tobento\App\RateLimiter\Test\Symfony\Registry;

use PHPUnit\Framework\TestCase;
use Tobento\App\RateLimiter\Symfony\Registry\TokenBucket;
use Tobento\App\RateLimiter\Symfony\RateLimiterFactory;
use Tobento\App\RateLimiter\RegistryInterface;

class TokenBucketTest extends TestCase
{
    public function testThatImplementsRegistryInterface()
    {
        $this->assertInstanceof(RegistryInterface::class, new TokenBucket());
    }
    
    public function testRegistry()
    {
        $registry = new TokenBucket(
            limit: 5000,
            rateAmount: 500,
            rateInterval: '60 Minutes',
            id: 'api',
            storage: 'inmemory',
            cache: 'api-ratelimiter',
        );
        
        $this->assertSame(RateLimiterFactory::class, $registry->getRateLimiterFactory());
        
        $this->assertSame(
            [
                'id' => 'api',
                'storage' => 'inmemory',
                'policy' => 'token_bucket',
                'limit' => 5000,
                'rate' => ['amount' => 500, 'interval' => '60 Minutes'],
            ],
            $registry->getRateLimiterFactoryConfig()
        );
    }
}