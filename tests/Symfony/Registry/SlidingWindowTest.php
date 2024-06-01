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
use Tobento\App\RateLimiter\Symfony\Registry\SlidingWindow;
use Tobento\App\RateLimiter\Symfony\RateLimiterFactory;
use Tobento\App\RateLimiter\RegistryInterface;

class SlidingWindowTest extends TestCase
{
    public function testThatImplementsRegistryInterface()
    {
        $this->assertInstanceof(RegistryInterface::class, new SlidingWindow());
    }
    
    public function testRegistry()
    {
        $registry = new SlidingWindow(
            limit: 20,
            interval: '60 Minutes',
            id: 'api',
            storage: 'inmemory',
            cache: 'api-ratelimiter',
        );
        
        $this->assertSame(RateLimiterFactory::class, $registry->getRateLimiterFactory());
        
        $this->assertSame(
            [
                'id' => 'api',
                'storage' => 'inmemory',
                'policy' => 'sliding_window',
                'limit' => 20,
                'interval' => '60 Minutes',
            ],
            $registry->getRateLimiterFactoryConfig()
        );
    }
}