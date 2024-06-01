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
use Tobento\App\RateLimiter\Registry\Factory;
use Tobento\App\RateLimiter\RegistryInterface;

class FactoryTest extends TestCase
{
    public function testThatImplementsRegistryInterface()
    {
        $this->assertInstanceof(RegistryInterface::class, new Factory(factory: 'foo'));
    }
    
    public function testRegistry()
    {
        $registry = new Factory(factory: 'foo', config: ['key' => 'value']);
        
        $this->assertSame('foo', $registry->getRateLimiterFactory());
        $this->assertSame(['key' => 'value'], $registry->getRateLimiterFactoryConfig());
    }
}