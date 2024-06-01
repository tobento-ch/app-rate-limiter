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

use Tobento\App\RateLimiter\RateLimiterInterface;
use Symfony\Component\RateLimiter\LimiterInterface;
use Symfony\Component\RateLimiter\RateLimit;
use DateTimeImmutable;

/**
 * RateLimiter
 */
class RateLimiter implements RateLimiterInterface
{
    /**
     * @var RateLimit
     */
    protected RateLimit $limit;
    
    /**
     * Create a new RateLimiter.
     *
     * @param LimiterInterface $limiter
     */
    public function __construct(
        protected LimiterInterface $limiter,
    ) {
        $this->limit = $limiter->consume(0);
    }

    /**
     * Increment the attempts.
     *
     * @return static $this
     */
    public function hit(): static
    {
        $this->limit = $this->limiter->consume(1);
        return $this;
    }
    
    /**
     * Returns true if attempts has been exceeded, otherwise false.
     *
     * @return bool
     */
    public function isAttemptsExceeded(): bool
    {
        return $this->limit->isAccepted() === false;
    }
    
    /**
     * Returns the max number of attempts.
     *
     * @return int
     */
    public function maxAttempts(): int
    {
        return $this->limit->getLimit();
    }
    
    /**
     * Returns the remaining attempts.
     *
     * @return int
     */
    public function remainingAttempts(): int
    {
        return $this->limit->getRemainingTokens();
    }
    
    /**
     * Returns the date when the limiter is available again.
     *
     * @return DateTimeImmutable
     */
    public function availableAt(): DateTimeImmutable
    {
        return $this->limit->getRetryAfter();
    }
    
    /**
     * Returns the number of seconds until the limiter is available again.
     *
     * @return int
     */
    public function availableIn(): int
    {
        return $this->availableAt()->getTimestamp() - time();
    }
    
    /**
     * Reset the number of attempts.
     *
     * @return static $this
     */
    public function reset(): static
    {
        $this->limiter->reset();
        return $this;
    }
}