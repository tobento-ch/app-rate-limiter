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

namespace Tobento\App\RateLimiter\Test\Registry;

use PHPUnit\Framework\TestCase;
use Tobento\App\RateLimiter\Registry\Named;
use Tobento\App\RateLimiter\RegistryInterface;

class NamedTest extends TestCase
{
    public function testThatImplementsRegistryInterface()
    {
        $this->assertInstanceof(RegistryInterface::class, new Named('foo'));
    }
    
    public function testRegistry()
    {
        $registry = new Named('foo');
        
        $this->assertSame(null, $registry->getRateLimiterFactory());
        $this->assertSame(['name' => 'foo'], $registry->getRateLimiterFactoryConfig());
    }
}