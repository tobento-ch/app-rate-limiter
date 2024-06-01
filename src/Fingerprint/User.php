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
use Tobento\App\User\UserInterface;

/**
 * User
 */
final class User implements FingerprintInterface
{
    /**
     * Returns the fingerprint.
     *
     * @return null|string
     */
    public function getFingerprint(ServerRequestInterface $request): null|string
    {
        $userId = $request->getAttribute(UserInterface::class)?->id();
        
        if ($userId > 0) {
            return sha1($request->getUri()->getHost().'|'.$userId);
        }

        return null;
    }
}