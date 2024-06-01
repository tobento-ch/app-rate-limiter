<?php

/**
 * TOBENTO
 *
 * @copyright   Tobias Strub, TOBENTO
 * @license     MIT License, see LICENSE file distributed with this source code.
 * @author      Tobias Strub
 * @link        https://www.tobento.ch
 */

use Tobento\App\RateLimiter\Symfony\Registry\FixedWindow;
use Tobento\App\RateLimiter\Symfony\Registry\NoLimit;
use Tobento\App\RateLimiter\Symfony\Registry\SlidingWindow;
use Tobento\App\RateLimiter\Symfony\Registry\TokenBucket;

return [
    
    /*
    |--------------------------------------------------------------------------
    | Named Rate Limiters
    |--------------------------------------------------------------------------
    |
    | Configure any named rate limiters needed for your application.
    |
    | See: https://github.com/tobento-ch/app-rate-limiter#register-named-rate-limiters
    |
    */
    
    'limiters' => [
        // 'api' => new TokenBucket(limit: 10, rateAmount: 5, rateInterval: '5 Minutes'),
    ],
    
    
    /*
    |--------------------------------------------------------------------------
    | Fallback Named Rate Limiter
    |--------------------------------------------------------------------------
    |
    | Configure the fallback named rate limiter.
    |
    */
    
    'limiter' => new TokenBucket(limit: 10, rateAmount: 5, rateInterval: '5 Minutes'),
];