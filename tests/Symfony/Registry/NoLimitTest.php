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
use Tobento\App\RateLimiter\Symfony\Registry\NoLimit;
use Tobento\App\RateLimiter\Symfony\RateLimiterFactory;
use Tobento\App\RateLimiter\RegistryInterface;

class NoLimitTest extends TestCase
{
    public function testThatImplementsRegistryInterface()
    {
        $this->assertInstanceof(RegistryInterface::class, new NoLimit());
    }
    
    public function testRegistry()
    {
        $registry = new NoLimit();
        
        $this->assertSame(RateLimiterFactory::class, $registry->getRateLimiterFactory());
        
        $this->assertSame(
            [
                'policy' => 'no_limit',
            ],
            $registry->getRateLimiterFactoryConfig()
        );
    }
}