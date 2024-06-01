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

namespace Tobento\App\RateLimiter\Symfony;

use Tobento\App\RateLimiter\RateLimiterFactoryInterface;
use Tobento\App\RateLimiter\RateLimiterInterface;
use Tobento\Service\Cache\CacheItemPoolsInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory as SymfonyRateLimiterFactory;
use Symfony\Component\RateLimiter\Storage;
use DateTimeImmutable;
use LogicException;

/**
 * RateLimiterFactory
 */
class RateLimiterFactory implements RateLimiterFactoryInterface
{
    /**
     * Create a new RateLimiterFactory.
     *
     * @param CacheItemPoolsInterface $pools
     */
    public function __construct(
        protected CacheItemPoolsInterface $pools,
    ) {}
    
    /**
     * Create a rate limiter.
     *
     * @param string $id A unique identifier.
     * @param array $config
     * @return RateLimiterInterface
     */
    public function createRateLimiter(string $id, array $config = []): RateLimiterInterface
    {
        $config['storage'] ??= 'cache';
        $config['cache'] ??= 'ratelimiter';
        $config['id'] ??= 'global';
        $config['policy'] ??= 'token_bucket';
        
        switch ($config['policy']) {
            case 'token_bucket':
                $config['limit'] ??= 5;
                $config['rate'] ??= ['amount' => 1, 'interval' => '5 minutes'];
                break;
            case 'fixed_window':
                $config['limit'] ??= 5;
                $config['interval'] ??= '5 minutes';
                break;
            case 'sliding_window':
                $config['limit'] ??= 5;
                $config['interval'] ??= '5 minutes';
                break;
            case 'no_limit':
                break;
            default:
                throw new LogicException(sprintf('Limiter policy "%s" does not exists, it must be either "token_bucket", "sliding_window", "fixed_window" or "no_limit".', $config['policy']));
        }
        
        // handle storage:
        switch ($config['storage']) {
            case 'cache':
                $pool = $this->pools->has($config['cache'])
                    ? $this->pools->get($config['cache'])
                    : $this->pools->default('primary');
                
                $storage = new Storage\CacheStorage(pool: $pool);
                break;
            case 'inmemory':
                $storage = new Storage\InMemoryStorage();
                break;
            default:
                throw new LogicException(sprintf('Limiter storage "%s" does not exists, it must be either "cahce" or "inmemory".', $config['storage']));
        }
        
        unset($config['storage']);
        unset($config['cache']);
        
        $limiter = (new SymfonyRateLimiterFactory(config: $config, storage: $storage))->create(sha1($id));
        
        return new RateLimiter(limiter: $limiter);
    }
}