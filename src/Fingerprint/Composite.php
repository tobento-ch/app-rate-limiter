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
 * Composite
 */
final class Composite implements FingerprintInterface
{
    /**
     * @var array<array-key, FingerprintInterface>
     */
    private array $fingerprints = [];
    
    /**
     * Create a new Composite.
     *
     * @param FingerprintInterface ...$fingerprints
     */
    public function __construct(
        FingerprintInterface ...$fingerprints,
    ) {
        $this->fingerprints = $fingerprints;
    }
    
    /**
     * Returns the fingerprint.
     *
     * @return null|string
     */
    public function getFingerprint(ServerRequestInterface $request): null|string
    {
        foreach($this->fingerprints as $fingerprint) {
            if (!is_null($fingerprint = $fingerprint->getFingerprint($request))) {
                return $fingerprint;
            }
        }

        return null;
    }
}