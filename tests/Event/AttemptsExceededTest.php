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

namespace Tobento\App\RateLimiter\Test\Event;

use PHPUnit\Framework\TestCase;
use Tobento\App\RateLimiter\Event\AttemptsExceeded;
use Tobento\App\RateLimiter\Symfony\RateLimiterFactory;
use Tobento\Service\Cache\CacheItemPools;
use Nyholm\Psr7\Factory\Psr17Factory;

class AttemptsExceededTest extends TestCase
{
    public function testEvent()
    {
        $factory = new RateLimiterFactory(new CacheItemPools());
        $limiter = $factory->createRateLimiter('id', ['policy' => 'no_limit', 'storage' => 'inmemory']);
        $request = (new Psr17Factory())->createServerRequest('GET', 'uri', []);
        
        $event = new AttemptsExceeded(
            rateLimiter: $limiter,
            request: $request,
        );
        
        $this->assertTrue($limiter === $event->rateLimiter());
        $this->assertTrue($request === $event->request());
    }
}