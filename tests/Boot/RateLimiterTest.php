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

namespace Tobento\App\RateLimiter\Test\Boot;

use PHPUnit\Framework\TestCase;
use Tobento\App\RateLimiter\Boot\RateLimiter;
use Tobento\App\RateLimiter\RateLimiterCreatorInterface;
use Tobento\App\RateLimiter\RegistriesInterface;
use Tobento\App\RateLimiter\FingerprintInterface;
use Tobento\App\AppInterface;
use Tobento\App\AppFactory;
use Tobento\Service\Filesystem\Dir;

class RateLimiterTest extends TestCase
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
    
    public function testInterfacesAreAvailable()
    {
        $app = $this->createApp();
        $app->boot(RateLimiter::class);
        $app->booting();
        
        $this->assertInstanceof(RateLimiterCreatorInterface::class, $app->get(RateLimiterCreatorInterface::class));
        $this->assertInstanceof(RegistriesInterface::class, $app->get(RegistriesInterface::class));
        $this->assertInstanceof(FingerprintInterface::class, $app->get(FingerprintInterface::class));
    }
}