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

namespace Tobento\App\RateLimiter\Test;

use PHPUnit\Framework\TestCase;
use Tobento\App\RateLimiter\Registries;
use Tobento\App\RateLimiter\RegistriesInterface;
use Tobento\App\RateLimiter\Symfony\Registry\FixedWindow;
use Tobento\App\RateLimiter\Symfony\Registry\SlidingWindow;

class RegistriesTest extends TestCase
{
    public function testThatImplementsInterfaces()
    {
        $registries = new Registries(new SlidingWindow());
        
        $this->assertInstanceof(RegistriesInterface::class, $registries);
    }
    
    public function testAddMethod()
    {
        $registries = new Registries(new SlidingWindow());
        
        $this->assertFalse($registries->has('foo'));
        
        $registries->add('foo', new FixedWindow(limit: 1, interval: '1 minutes'));
        
        $this->assertTrue($registries->has('foo'));
    }
    
    public function testGetMethod()
    {
        $registries = new Registries(new SlidingWindow());
        $registry = new FixedWindow(limit: 1, interval: '1 minutes');
        $registries->add('foo', $registry);
        
        $this->assertTrue($registry === $registries->get('foo'));
    }
    
    public function testGetMethodReturnsFallbackIfNotExists()
    {
        $registry = new FixedWindow(limit: 1, interval: '1 minutes');
        $registries = new Registries($registry);
        
        $this->assertTrue($registry === $registries->get('foo'));
    }    
}