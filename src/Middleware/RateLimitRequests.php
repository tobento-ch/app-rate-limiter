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

namespace Tobento\App\RateLimiter\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Tobento\App\RateLimiter\RateLimiterCreatorInterface;
use Tobento\App\RateLimiter\FingerprintInterface;
use Tobento\App\RateLimiter\RegistryInterface;
use Tobento\App\RateLimiter\Registry\Named;
use Tobento\App\RateLimiter\Exception\FingerprintException;
use Tobento\App\Http\Exception\TooManyRequestsException;

/**
 * RateLimitRequests
 */
class RateLimitRequests implements MiddlewareInterface
{
    /**
     * @var RegistryInterface
     */
    protected RegistryInterface $registry;
    
    /**
     * Create a new RateLimitRequests.
     *
     * @param RateLimiterCreatorInterface $rateLimiterCreator
     * @param FingerprintInterface $fingerprint
     * @param string|RegistryInterface $registry
     */
    public function __construct(
        protected RateLimiterCreatorInterface $rateLimiterCreator,
        protected FingerprintInterface $fingerprint,
        string|RegistryInterface $registry,
    ) {
        $this->registry = is_string($registry) ? new Named($registry) : $registry;
    }
    
    /**
     * Process the middleware.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $identifier = $this->fingerprint->getFingerprint($request);

        if ($identifier === null) {
            throw new FingerprintException('Fingerprint can not be resolved for request.');
        }
        
        $limiter = $this->rateLimiterCreator->createFromRegistry(
            id: $identifier,
            registry: $this->registry,
        );
        
        $limiter->hit();
        
        $headers = [
            'X-RateLimit-Limit' => $limiter->maxAttempts(),
            'X-RateLimit-Remaining' => $limiter->remainingAttempts(),
            'X-RateLimit-Reset' => $limiter->availableAt()->getTimestamp(),
        ];
        
        if ($limiter->isAttemptsExceeded()) {
            throw new TooManyRequestsException(
                retryAfter: $limiter->availableIn(),
                headers: $headers,
            );
        }
        
        return $this->addRateLimitHeaders($handler->handle($request), $headers);
    }
    
    /**
     * Add rate limit headers.
     *
     * @param ResponseInterface $response
     * @param array $headers
     * @return ResponseInterface
     */
    protected function addRateLimitHeaders(ResponseInterface $response, array $headers): ResponseInterface
    {
        foreach($headers as $name => $value) {
            $response = $response->withHeader($name, $value);
        }
        
        return $response;
    }
}