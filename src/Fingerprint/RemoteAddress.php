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

namespace Tobento\App\RateLimiter\Fingerprint;

use Psr\Http\Message\ServerRequestInterface;
use Tobento\App\RateLimiter\FingerprintInterface;

/**
 * RemoteAddress
 */
final class RemoteAddress implements FingerprintInterface
{
    /**
     * Returns the fingerprint.
     *
     * @return null|string
     */
    public function getFingerprint(ServerRequestInterface $request): null|string
    {
        if (!is_null($remoteAddr = $request->getServerParams()['REMOTE_ADDR'] ?? null)) {
            return sha1($request->getUri()->getHost().'|'.$remoteAddr);
        }
        
        return null;
    }
}