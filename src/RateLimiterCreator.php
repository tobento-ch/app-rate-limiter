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

namespace Tobento\App\RateLimiter;

use Tobento\App\RateLimiter\Registry\Named;
use Tobento\App\RateLimiter\Exception\RateLimiterException;
use Tobento\Service\Autowire\Autowire;
use Psr\Container\ContainerInterface;
use LogicException;

/**
 * Creates a rate limiter from another factory.
 */
final class RateLimiterCreator implements RateLimiterCreatorInterface
{
    /**
     * @var Autowire
     */
    private Autowire $autowire;
    
    /**
     * Create a new RateLimiterFactory.
     *
     * @param ContainerInterface $container
     */
    public function __construct(
        ContainerInterface $container,
        private RegistriesInterface $registries,
    ) {
        $this->autowire = new Autowire($container);
    }
    
    /**
     * Create a new rate limiter from the given registry.
     *
     * @param string $id A unique identifier.
     * @param RegistryInterface $registry
     * @return RateLimiterInterface
     */
    public function createFromRegistry(string $id, RegistryInterface $registry): RateLimiterInterface
    {
        if ($registry instanceof Named) {
            $registry = $this->registries->get($registry->name());
        }
        
        if (is_null($registry->getRateLimiterFactory())) {
            throw new RateLimiterException('Unable to create rate limiter from registry without defined factory!');
        }
        
        $factory = $this->autowire->resolve($registry->getRateLimiterFactory());
        
        return $factory->createRateLimiter(
            id: $id,
            config: $registry->getRateLimiterFactoryConfig(),
        );
    }
}