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
use Tobento\App\RateLimiter\Fingerprint\User as UserFp;
use Tobento\App\RateLimiter\FingerprintInterface;
use Tobento\App\User\UserInterface;
use Tobento\App\User\User;
use Nyholm\Psr7\Factory\Psr17Factory;

class UserTest extends TestCase
{
    public function testThatImplementsFingerprintInterface()
    {
        $this->assertInstanceof(FingerprintInterface::class, new UserFp());
    }
    
    public function testGetFingerprint()
    {
        $request = (new Psr17Factory())->createServerRequest('GET', 'uri');
        $request = $request->withAttribute(UserInterface::class, new User(id: 1));

        $this->assertSame('f9cb6c0fe5007572f6d7310a731ddfb2e243b48e', (new UserFp())->getFingerprint($request));
    }

    public function testGetFingerprintReturnsNullIfWithIdNull()
    {
        $request = (new Psr17Factory())->createServerRequest('GET', 'uri');
        $request = $request->withAttribute(UserInterface::class, new User(id: 0));
        
        $this->assertSame(null, (new UserFp())->getFingerprint($request));
    }
    
    public function testGetFingerprintReturnsNullIfNoUserAtAll()
    {
        $request = (new Psr17Factory())->createServerRequest('GET', 'uri');
        
        $this->assertSame(null, (new UserFp())->getFingerprint($request));
    }
}