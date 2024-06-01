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

namespace Tobento\App\RateLimiter\Event;

use Psr\Http\Message\ServerRequestInterface;
use Tobento\App\RateLimiter\RateLimiterInterface;

/**
 * Event when attempts exceeded.
 */
final class AttemptsExceeded
{
    /**
     * Create a new AttemptsExceeded.
     *
     * @param RateLimiterInterface $rateLimiter
     * @param null|ServerRequestInterface $request
     */
    public function __construct(
        private RateLimiterInterface $rateLimiter,
        private null|ServerRequestInterface $request = null,
    ) {}
    
    /**
     * Returns the rate limiter.
     *
     * @return RateLimiterInterface
     */
    public function rateLimiter(): RateLimiterInterface
    {
        return $this->rateLimiter;
    }
    
    /**
     * Returns the request.
     *
     * @return null|ServerRequestInterface
     */
    public function request(): null|ServerRequestInterface
    {
        return $this->request;
    }
}