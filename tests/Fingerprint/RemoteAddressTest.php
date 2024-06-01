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
use Tobento\App\RateLimiter\Fingerprint\RemoteAddress;
use Tobento\App\RateLimiter\FingerprintInterface;
use Nyholm\Psr7\Factory\Psr17Factory;

class RemoteAddressTest extends TestCase
{
    public function testThatImplementsFingerprintInterface()
    {
        $this->assertInstanceof(FingerprintInterface::class, new RemoteAddress());
    }
    
    public function testGetFingerprint()
    {
        $request = (new Psr17Factory())->createServerRequest('GET', 'uri', ['REMOTE_ADDR' => 'foo']);
        
        $this->assertSame('60da9573cdffafbca01c1ae05df133856ac55b21', (new RemoteAddress())->getFingerprint($request));
    }
    
    public function testGetFingerprintReturnsNullIfNoRemoteAddr()
    {
        $request = (new Psr17Factory())->createServerRequest('GET', 'uri');
        
        $this->assertSame(null, (new RemoteAddress())->getFingerprint($request));
    }
}