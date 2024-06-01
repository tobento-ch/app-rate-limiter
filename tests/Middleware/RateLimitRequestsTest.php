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

namespace Tobento\App\RateLimiter\Test\Middleware;

use PHPUnit\Framework\TestCase;
use Tobento\App\RateLimiter\Middleware\RateLimitRequests;
use Tobento\App\RateLimiter\RateLimiterCreatorInterface;
use Tobento\App\RateLimiter\RateLimiterCreator;
use Tobento\App\RateLimiter\FingerprintInterface;
use Tobento\App\RateLimiter\Fingerprint;
use Tobento\App\RateLimiter\RegistriesInterface;
use Tobento\App\RateLimiter\Registries;
use Tobento\App\RateLimiter\Symfony\Registry\FixedWindow;
use Tobento\App\RateLimiter\Exception\FingerprintException;
use Tobento\App\Http\Exception\TooManyRequestsException;
use Tobento\Service\Cache\CacheItemPoolsInterface;
use Tobento\Service\Cache\CacheItemPools;
use Tobento\Service\Cache\ArrayCacheItemPool;
use Tobento\Service\Middleware\MiddlewareDispatcherInterface;
use Tobento\Service\Middleware\MiddlewareDispatcher;
use Tobento\Service\Middleware\AutowiringMiddlewareFactory;
use Tobento\Service\Middleware\FallbackHandler;
use Tobento\Service\Container\Container;
use Tobento\Service\Clock\SystemClock;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Container\ContainerInterface;

class RateLimitRequestsTest extends TestCase
{
    private function getContainer(): Container
    {
        $container = new Container();
        $container->set(RateLimiterCreatorInterface::class, RateLimiterCreator::class);
        $container->set(RegistriesInterface::class, new Registries(new FixedWindow()));
        $container->set(FingerprintInterface::class, static function() {
            return new Fingerprint\Composite(new Fingerprint\RemoteAddress());
        });
        $container->set(CacheItemPoolsInterface::class, static function() {
            $pools = new CacheItemPools();
            $pools->add('array', new ArrayCacheItemPool(clock: new SystemClock()));
            $pools->addDefault(name: 'primary', pool: 'array');
            return $pools;
        });
        $container->set(MiddlewareDispatcherInterface::class, static function(ContainerInterface $container) {
            return new MiddlewareDispatcher(
                new FallbackHandler((new Psr17Factory())->createResponse(404)),
                new AutowiringMiddlewareFactory($container)
            );
        });
        
        return $container;
    }

    public function testRateLimiting()
    {
        $container = $this->getContainer();
        $md = $container->get(MiddlewareDispatcherInterface::class);
        
        $md->add([RateLimitRequests::class, 'registry' => new FixedWindow(limit: 5, interval: '5 minutes')]);
        
        $request = (new Psr17Factory())->createServerRequest(
            method: 'GET',
            uri: 'foo',
            serverParams: ['REMOTE_ADDR' => 'addr']
        );

        $response = $md->handle($request);
        
        $this->assertSame('5', $response->getHeaderLine('X-RateLimit-Limit'));
        $this->assertSame('4', $response->getHeaderLine('X-RateLimit-Remaining'));
        $this->assertTrue(!empty($response->getHeaderLine('X-RateLimit-Reset')));
    }
    
    public function testRateLimitingWithNamed()
    {
        $container = $this->getContainer();
        $md = $container->get(MiddlewareDispatcherInterface::class);
        
        $md->add([RateLimitRequests::class, 'registry' => 'api']);
        
        $request = (new Psr17Factory())->createServerRequest(
            method: 'GET',
            uri: 'foo',
            serverParams: ['REMOTE_ADDR' => 'addr']
        );

        $response = $md->handle($request);
        
        $this->assertSame('5', $response->getHeaderLine('X-RateLimit-Limit'));
        $this->assertSame('4', $response->getHeaderLine('X-RateLimit-Remaining'));
        $this->assertTrue(!empty($response->getHeaderLine('X-RateLimit-Reset')));
    }

    public function testRateLimitingThrowsTooManyRequestsExceptionIfAttemptsExceeded()
    {
        $container = $this->getContainer();
        $md = $container->get(MiddlewareDispatcherInterface::class);
        
        $md->add([RateLimitRequests::class, 'registry' => new FixedWindow(limit: 1, interval: '5 minutes')]);
        
        $request = (new Psr17Factory())->createServerRequest(
            method: 'GET',
            uri: 'foo',
            serverParams: ['REMOTE_ADDR' => 'addr']
        );

        $md->handle($request);
        
        // second:
        $md->add([RateLimitRequests::class, 'registry' => new FixedWindow(limit: 1, interval: '5 minutes')]);
        
        $request = (new Psr17Factory())->createServerRequest(
            method: 'GET',
            uri: 'foo',
            serverParams: ['REMOTE_ADDR' => 'addr']
        );
        
        $thrown = false;
        
        try {
            $md->handle($request);
        } catch (TooManyRequestsException $e) {
            $thrown = true;
            $this->assertSame(1, $e->headers()['X-RateLimit-Limit'] ?? null);
            $this->assertSame(0, $e->headers()['X-RateLimit-Remaining'] ?? null);
            $this->assertTrue(!empty($e->headers()['X-RateLimit-Reset'] ?? null));
        }
        
        $this->assertTrue($thrown);
    }
    
    public function testRateLimitingThrowsFingerprintExceptionIfNotResolvable()
    {
        $this->expectException(FingerprintException::class);
        
        $container = $this->getContainer();
        $md = $container->get(MiddlewareDispatcherInterface::class);
        
        $md->add([RateLimitRequests::class, 'registry' => new FixedWindow(limit: 5, interval: '5 minutes')]);
        
        $request = (new Psr17Factory())->createServerRequest(
            method: 'GET',
            uri: 'foo',
            serverParams: []
        );

        $md->handle($request);
    }
}