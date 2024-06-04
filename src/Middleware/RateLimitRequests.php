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
use Tobento\Service\Responser\ResponserInterface;
use Tobento\Service\Routing\RouterInterface;
use Tobento\Service\Routing\UrlException;
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
     * @param null|RouterInterface $router
     * @param string|RegistryInterface $registry
     * @param null|string $redirectUri
     * @param null|string $redirectRoute
     * @param string $message
     * @param string $messageLevel
     * @param null|string $messageKey
     */
    public function __construct(
        protected RateLimiterCreatorInterface $rateLimiterCreator,
        protected FingerprintInterface $fingerprint,
        protected null|RouterInterface $router,
        string|RegistryInterface $registry,
        protected null|string $redirectUri = null,
        protected null|string $redirectRoute = null,
        protected string $message = '',
        protected string $messageLevel = 'error',
        protected null|string $messageKey = null,
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
            // redirect if defined and available:
            $redirectUri = $this->redirectUri;
            
            if (!empty($this->redirectRoute) && $this->router) {
                try {
                    $redirectUri = (string) $this->router->url($this->redirectRoute);
                } catch (UrlException $e) {
                    //
                }
            }
            
            if (
                !is_null($redirectUri)
                && !is_null($responser = $request->getAttribute(ResponserInterface::class))
            ) {
                if (!empty($this->message)) {
                    $responser->messages()->add(
                        level: $this->messageLevel,
                        message: $this->message,
                        parameters: [':seconds' => $limiter->availableIn()],
                        key: $this->messageKey,
                    );
                }

                return $responser->redirect(uri: $redirectUri);
            }
            
            // otherwise throw exception:
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