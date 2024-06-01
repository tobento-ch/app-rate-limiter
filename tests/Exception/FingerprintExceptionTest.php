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

namespace Tobento\App\RateLimiter\Test\Exception;

use PHPUnit\Framework\TestCase;
use Tobento\App\RateLimiter\Exception\FingerprintException;
use Tobento\App\RateLimiter\Exception\RateLimiterException;

class FingerprintExceptionTest extends TestCase
{
    public function testException()
    {
        $this->assertInstanceof(RateLimiterException::class, new FingerprintException());
    }
}