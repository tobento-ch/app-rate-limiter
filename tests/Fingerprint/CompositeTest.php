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

namespace Tobento\App\RateLimiter\Test\Fingerprint;

use PHPUnit\Framework\TestCase;
use Tobento\App\RateLimiter\Fingerprint\Composite;
use Tobento\App\RateLimiter\Fingerprint\RemoteAddress;
use Tobento\App\RateLimiter\Fingerprint\User;
use Tobento\App\RateLimiter\FingerprintInterface;
use Nyholm\Psr7\Factory\Psr17Factory;

class CompositeTest extends TestCase
{
    public function testThatImplementsFingerprintInterface()
    {
        $this->assertInstanceof(FingerprintInterface::class, new Composite());
    }
    
    public function testGetFingerprintReturnsFirstResolved()
    {
        $request = (new Psr17Factory())->createServerRequest('GET', 'uri', ['REMOTE_ADDR' => 'foo']);
        $composite = new Composite(
            new User(),
            new RemoteAddress(),
        );
        
        $this->assertSame('60da9573cdffafbca01c1ae05df133856ac55b21', $composite->getFingerprint($request));
    }

    public function testGetFingerprintReturnsNullIfNone()
    {
        $request = (new Psr17Factory())->createServerRequest('GET', 'uri');
        
        $this->assertSame(null, (new Composite())->getFingerprint($request));
    }
}