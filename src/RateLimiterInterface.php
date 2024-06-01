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

use DateTimeImmutable;

/**
 * RateLimiterInterface
 */
interface RateLimiterInterface
{
    /**
     * Increment the attempts.
     *
     * @return static $this
     */
    public function hit(): static;
    
    /**
     * Returns true if attempts has been exceeded, otherwise false.
     *
     * @return bool
     */
    public function isAttemptsExceeded(): bool;
    
    /**
     * Returns the max number of attempts.
     *
     * @return int
     */
    public function maxAttempts(): int;
    
    /**
     * Returns the remaining attempts.
     *
     * @return int
     */
    public function remainingAttempts(): int;
    
    /**
     * Returns the date when the limiter is available again.
     *
     * @return DateTimeImmutable
     */
    public function availableAt(): DateTimeImmutable;
    
    /**
     * Returns the number of seconds until the limiter is available again.
     *
     * @return int
     */
    public function availableIn(): int;
    
    /**
     * Reset the number of attempts.
     *
     * @return static $this
     */
    public function reset(): static;
}