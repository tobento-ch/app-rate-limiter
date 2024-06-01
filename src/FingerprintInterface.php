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

use Psr\Http\Message\ServerRequestInterface;

/**
 * FingerprintInterface
 */
interface FingerprintInterface
{
    /**
     * Returns the fingerprint.
     *
     * @return null|string
     */
    public function getFingerprint(ServerRequestInterface $request): null|string;
}