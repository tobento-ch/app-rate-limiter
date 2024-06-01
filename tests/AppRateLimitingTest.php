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
use Tobento\App\RateLimiter\Boot\RateLimiter;
use Tobento\App\RateLimiter\RateLimiterCreatorInterface;
use Tobento\App\RateLimiter\RegistriesInterface;
use Tobento\App\RateLimiter\Registry\Factory;
use Tobento\App\RateLimiter\Registry\Named;
use Tobento\App\RateLimiter\Symfony\Registry\FixedWindow;
use Tobento\App\RateLimiter\Symfony\Registry\NoLimit;
use Tobento\App\RateLimiter\Symfony\Registry\SlidingWindow;
use Tobento\App\RateLimiter\Symfony\Registry\TokenBucket;
use Tobento\App\RateLimiter\Symfony\RateLimiterFactory;
use Tobento\App\Http\Exception\TooManyRequestsException;
use Tobento\App\AppInterface;
use Tobento\App\AppFactory;
use Tobento\Service\Filesystem\Dir;

class AppRateLimitingTest extends TestCase
{
    protected function createApp(bool $deleteDir = true): AppInterface
    {
        if ($deleteDir) {
            (new Dir())->delete(__DIR__.'/../app/');
        }
        
        (new Dir())->create(__DIR__.'/../app/');
        
        $app = (new AppFactory())->createApp();
        
        $app->dirs()
            ->dir(realpath(__DIR__.'/../../'), 'root')
            ->dir(realpath(__DIR__.'/../app/'), 'app')
            ->dir($app->dir('app').'config', 'config', group: 'config')
            ->dir($app->dir('root').'vendor', 'vendor')
            // for testing only we add public within app dir.
            ->dir($app->dir('app').'public', 'public');
        
        return $app;
    }
    
    public static function tearDownAfterClass(): void
    {
        (new Dir())->delete(__DIR__.'/../app/');
    }
    
    public function testUsingFactory()
    {
        $app = $this->createApp();
        $app->boot(RateLimiter::class);
        $app->booting();

        $limiter = $app->get(RateLimiterCreatorInterface::class)->createFromRegistry(
            id: 'not-exceeding',
            registry: new Factory(
                factory: RateLimiterFactory::class,
                config: [
                    'policy' => 'sliding_window',
                    'limit' => 1,
                    'interval' => '1 Minutes',
                ],
            ),
        );

        if ($limiter->hit()->isAttemptsExceeded()) {
            throw new TooManyRequestsException(retryAfter: $limiter->availableIn());
        }
        
        $limiter->reset();
        
        $this->assertTrue(true);
    }
    
    public function testUsingFactoryExceeded()
    {
        $this->expectException(TooManyRequestsException::class);
        
        $app = $this->createApp();
        $app->boot(RateLimiter::class);
        $app->booting();

        $limiter = $app->get(RateLimiterCreatorInterface::class)->createFromRegistry(
            id: 'exceeding',
            registry: new Factory(
                factory: RateLimiterFactory::class,
                config: [
                    'policy' => 'sliding_window',
                    'limit' => 1,
                    'interval' => '1 Minutes',
                ],
            ),
        );

        if ($limiter->hit()->hit()->isAttemptsExceeded()) {
            throw new TooManyRequestsException(retryAfter: $limiter->availableIn());
        }
        
        $limiter->reset();
    }

    public function testUsingFixedWindow()
    {
        $app = $this->createApp();
        $app->boot(RateLimiter::class);
        $app->booting();

        $limiter = $app->get(RateLimiterCreatorInterface::class)->createFromRegistry(
            id: 'not-exceeding',
            registry: new FixedWindow(limit: 1, interval: '1 minutes'),
        );

        if ($limiter->hit()->isAttemptsExceeded()) {
            throw new TooManyRequestsException(retryAfter: $limiter->availableIn());
        }
        
        $limiter->reset();
        
        $this->assertTrue(true);
    }
    
    public function testUsingFixedWindowExceeded()
    {
        $this->expectException(TooManyRequestsException::class);
        
        $app = $this->createApp();
        $app->boot(RateLimiter::class);
        $app->booting();

        $limiter = $app->get(RateLimiterCreatorInterface::class)->createFromRegistry(
            id: 'exceeding',
            registry: new FixedWindow(limit: 1, interval: '1 minutes'),
        );

        if ($limiter->hit()->hit()->isAttemptsExceeded()) {
            throw new TooManyRequestsException(retryAfter: $limiter->availableIn());
        }
        
        $limiter->reset();
    }
    
    public function testUsingNamedWindow()
    {
        $app = $this->createApp();
        $app->boot(RateLimiter::class);
        $app->booting();

        $limiter = $app->get(RateLimiterCreatorInterface::class)->createFromRegistry(
            id: 'not-exceeding',
            registry: new Named('foo'),
        );

        if ($limiter->hit()->isAttemptsExceeded()) {
            throw new TooManyRequestsException(retryAfter: $limiter->availableIn());
        }
        
        $limiter->reset();
        
        $this->assertTrue(true);
    }
    
    public function testUsingNamedExceeded()
    {
        $this->expectException(TooManyRequestsException::class);
        
        $app = $this->createApp();
        $app->boot(RateLimiter::class);
        $app->booting();
        
        $app->get(RegistriesInterface::class)->add('foo', new SlidingWindow(limit: 1, interval: '1 minutes'));

        $limiter = $app->get(RateLimiterCreatorInterface::class)->createFromRegistry(
            id: 'exceeding',
            registry: new Named('foo'),
        );

        if ($limiter->hit()->hit()->isAttemptsExceeded()) {
            throw new TooManyRequestsException(retryAfter: $limiter->availableIn());
        }
        
        $limiter->reset();
    }
    
    public function testUsingNoLimit()
    {
        $app = $this->createApp();
        $app->boot(RateLimiter::class);
        $app->booting();

        $limiter = $app->get(RateLimiterCreatorInterface::class)->createFromRegistry(
            id: 'not-exceeding',
            registry: new NoLimit(),
        );

        if ($limiter->hit()->isAttemptsExceeded()) {
            throw new TooManyRequestsException(retryAfter: $limiter->availableIn());
        }
        
        $limiter->reset();
        
        $this->assertTrue(true);
    }
    
    public function testUsingSlidingWindow()
    {
        $app = $this->createApp();
        $app->boot(RateLimiter::class);
        $app->booting();

        $limiter = $app->get(RateLimiterCreatorInterface::class)->createFromRegistry(
            id: 'not-exceeding',
            registry: new SlidingWindow(limit: 1, interval: '1 minutes'),
        );

        if ($limiter->hit()->isAttemptsExceeded()) {
            throw new TooManyRequestsException(retryAfter: $limiter->availableIn());
        }
        
        $limiter->reset();
        
        $this->assertTrue(true);
    }
    
    public function testUsingSlidingWindowExceeded()
    {
        $this->expectException(TooManyRequestsException::class);
        
        $app = $this->createApp();
        $app->boot(RateLimiter::class);
        $app->booting();

        $limiter = $app->get(RateLimiterCreatorInterface::class)->createFromRegistry(
            id: 'exceeding',
            registry: new SlidingWindow(limit: 1, interval: '1 minutes'),
        );

        if ($limiter->hit()->hit()->isAttemptsExceeded()) {
            throw new TooManyRequestsException(retryAfter: $limiter->availableIn());
        }
        
        $limiter->reset();
    }
    
    public function testUsingTokenBucket()
    {
        $app = $this->createApp();
        $app->boot(RateLimiter::class);
        $app->booting();

        $limiter = $app->get(RateLimiterCreatorInterface::class)->createFromRegistry(
            id: 'not-exceeding',
            registry: new TokenBucket(limit: 1, rateAmount: 1),
        );

        if ($limiter->hit()->isAttemptsExceeded()) {
            throw new TooManyRequestsException(retryAfter: $limiter->availableIn());
        }
        
        $limiter->reset();
        
        $this->assertTrue(true);
    }
    
    public function testUsingTokenBucketExceeded()
    {
        $this->expectException(TooManyRequestsException::class);
        
        $app = $this->createApp();
        $app->boot(RateLimiter::class);
        $app->booting();

        $limiter = $app->get(RateLimiterCreatorInterface::class)->createFromRegistry(
            id: 'exceeding',
            registry: new TokenBucket(limit: 1, rateAmount: 1),
        );

        if ($limiter->hit()->hit()->isAttemptsExceeded()) {
            throw new TooManyRequestsException(retryAfter: $limiter->availableIn());
        }
        
        $limiter->reset();
    }
}