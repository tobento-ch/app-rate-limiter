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
 
namespace Tobento\App\RateLimiter\Boot;

use Tobento\App\Boot;
use Tobento\App\Boot\Config;
use Tobento\App\Migration\Boot\Migration;
use Tobento\App\RateLimiter\RateLimiterCreatorInterface;
use Tobento\App\RateLimiter\RateLimiterCreator;
use Tobento\App\RateLimiter\FingerprintInterface;
use Tobento\App\RateLimiter\Fingerprint;
use Tobento\App\RateLimiter\RegistriesInterface;
use Tobento\App\RateLimiter\Registries;
use Tobento\App\RateLimiter\Symfony\Registry\TokenBucket;

/**
 * RateLimiter
 */
class RateLimiter extends Boot
{
    public const INFO = [
        'boot' => [
            'installs and loads rate limiter config file',
            'implements rate limiter interfaces',
        ],
    ];

    public const BOOT = [
        Config::class,
        Migration::class,
        \Tobento\App\Cache\Boot\Cache::class,
    ];

    /**
     * Boot application services.
     *
     * @param Migration $migration
     * @param Config $config
     * @return void
     */
    public function boot(Migration $migration, Config $config): void
    {
        // install migration:
        $migration->install(\Tobento\App\RateLimiter\Migration\RateLimiter::class);
        
        // interfaces:
        $this->app->set(RateLimiterCreatorInterface::class, RateLimiterCreator::class);
        
        $this->app->set(FingerprintInterface::class, static function() {
            return new Fingerprint\Composite(
                new Fingerprint\User(),
                new Fingerprint\RemoteAddress(),
            );
        });
        
        $this->app->set(
            RegistriesInterface::class,
            static function() use ($config): RegistriesInterface {
                
                $config = $config->load(file: 'rate_limiter.php');

                $registries = new Registries(
                    fallback: $config['limiter'] ?? new TokenBucket()
                );

                foreach($config['limiters'] ?? [] as $name => $registry) {
                    $registries->add($name, $registry);
                }

                return $registries;
            }
        );
    }
}