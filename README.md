# App Rate Limiter

Rate limiter support for the app using the [Symfony - Rate Limiter](https://symfony.com/doc/current/rate_limiter.html) component as default implementation.

## Table of Contents

- [Getting Started](#getting-started)
    - [Requirements](#requirements)
- [Documentation](#documentation)
    - [App](#app)
    - [Rate Limiter Boot](#rate-limiter-boot)
        - [Rate Limiter Config](#rate-limiter-config)
    - [Basic Usage](#basic-usage)
        - [Using The Rate Limiter Creator](#using-the-rate-limiter-creator)
        - [Using The RateLimitRequests Middleware](#using-the-ratelimitrequests-middleware)
    - [Available Rate Limiter Registries](#available-rate-limiter-registries)
        - [Factory](#factory)
        - [Fixed Window](#fixed-window)
        - [Named](#named)
        - [No Limit](#no-limit)
        - [Sliding Window](#sliding-window)
        - [Token Bucket](#token-bucket)
    - [Register Named Rate Limiters](#register-named-rate-limiters)
    - [Events](#events)
- [Credits](#credits)
___

# Getting Started

Add the latest version of the app rate limiter project running this command.

```
composer require tobento/app-rate-limiter
```

## Requirements

- PHP 8.0 or greater

# Documentation

## App

Check out the [**App Skeleton**](https://github.com/tobento-ch/app-skeleton) if you are using the skeleton.

You may also check out the [**App**](https://github.com/tobento-ch/app) to learn more about the app in general.

## Rate Limiter Boot

The rate limiter boot does the following:

* installs and loads rate limiter config file
* implements rate limiter interfaces

```php
use Tobento\App\AppFactory;
use Tobento\App\RateLimiter\RateLimiterCreatorInterface;
use Tobento\App\RateLimiter\RegistriesInterface;
use Tobento\App\RateLimiter\FingerprintInterface;

// Create the app
$app = (new AppFactory())->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'public', 'public')
    ->dir($app->dir('root').'vendor', 'vendor');

// Adding boots:
$app->boot(\Tobento\App\RateLimiter\Boot\RateLimiter::class);
$app->booting();

// Implemented interfaces:
$limiterCreator = $app->get(RateLimiterCreatorInterface::class);
$registries = $app->get(RegistriesInterface::class);
$fingerprint = $app->get(FingerprintInterface::class);

// Run the app
$app->run();
```

### Rate Limiter Config

The configuration for the rate limiter is located in the ```app/config/rate_limiter.php``` file at the default App Skeleton config location where you can specify named rate limiters for your application.

## Basic Usage

### Using The Rate Limiter Creator

After having [booted the rate limiter](#rate-limiter-boot), inject it in any service or controller. You may consider booting the [App Http - Routing Boot](https://github.com/tobento-ch/app-http#routing-boot) as well in order to get HTTP and Routing support. 

```php
use Tobento\App\AppFactory;
use Tobento\App\RateLimiter\RateLimiterCreatorInterface;
use Tobento\App\RateLimiter\Symfony\Registry\SlidingWindow;
use Tobento\App\Http\Exception\TooManyRequestsException;

$app = (new AppFactory())->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'public', 'public')
    ->dir($app->dir('root').'vendor', 'vendor');

// Adding boots:
$app->boot(\Tobento\App\Http\Boot\ErrorHandler::class);
$app->boot(\Tobento\App\Http\Boot\Routing::class);
$app->boot(\Tobento\App\RateLimiter\Boot\RateLimiter::class);
$app->booting();

// Routes:
$app->route('POST', 'login', function(ServerRequestInterface $request, RateLimiterCreatorInterface $limiterCreator) {
    // create a rate limiter:
    $limiter = $limiterCreator->createFromRegistry(
        // a unique identifier of the client:
        id: $request->getServerParams()['REMOTE_ADDR'] ?? null,
        // define the rate limiter to use:
        registry: new SlidingWindow(limit: 5, interval: '5 minutes'),
    );

    // next hit the limiter and check if attempts exceeded:
    if ($limiter->hit()->isAttemptsExceeded()) {
        throw new TooManyRequestsException(
            retryAfter: $limiter->availableIn(),
            message: sprintf('Too Many Requests. Please retry after %d seconds.', $limiter->availableIn()),
            headers: [
                'X-RateLimit-Limit' => $limiter->maxAttempts(),
                'X-RateLimit-Remaining' => $limiter->remainingAttempts(),
                'X-RateLimit-Reset' => $limiter->availableAt()->getTimestamp(),
            ],
        );
    }

    // you may reset the attempts:
    // $limiter->reset();
    
    return 'response';
});

// Run the app:
$app->run();
```

Check out the [Available Rate Limiter Registries](#available-rate-limiter-registries) for its available limiter registries.

### Using The RateLimitRequests Middleware

Use the ```RateLimitRequests::class``` middleware to rate limit routes easily.

```php
use Tobento\App\AppFactory;
use Tobento\App\RateLimiter\Middleware\RateLimitRequests;
use Tobento\App\RateLimiter\Symfony\Registry\SlidingWindow;

$app = (new AppFactory())->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'public', 'public')
    ->dir($app->dir('root').'vendor', 'vendor');

// Adding boots:
$app->boot(\Tobento\App\Http\Boot\ErrorHandler::class);
$app->boot(\Tobento\App\Http\Boot\Routing::class);
$app->boot(\Tobento\App\RateLimiter\Boot\RateLimiter::class);
$app->booting();

// Routes:
$app->route('POST', 'login', function() {
    // being rate limited!
    return 'response';
})->middleware([
    RateLimitRequests::class,
    
    // define the rate limiter to use:
    'registry' => new SlidingWindow(limit: 5, interval: '5 minutes'),
    
    // or by named rate limiter:
    //'registry' => 'login',
]);

// Run the app:
$app->run();
```

## Available Rate Limiter Registries

### Factory

The ```Factory``` registry may be used to create a rate limiter from any factory:

```php
use Tobento\App\RateLimiter\Registry\Factory;
use Tobento\App\RateLimiter\Symfony\RateLimiterFactory;

$limiter = $limiterCreator->createFromRegistry(
    id: 'a-unique-identifier',
    registry: new Factory(
        factory: RateLimiterFactory::class,
        config: [
            'policy' => 'sliding_window',
            'limit' => 5,
            'interval' => '5 Minutes',
        ],
    ),
);
```

### Fixed Window

The ```FixedWindow``` registry creates a [Symfony fixed window rate limiter](https://symfony.com/doc/current/rate_limiter.html#fixed-window-rate-limiter):

```php
use Tobento\App\RateLimiter\Symfony\Registry\FixedWindow;
use Tobento\App\RateLimiter\Symfony\RateLimiterFactory;

$limiter = $limiterCreator->createFromRegistry(
    id: 'a-unique-identifier',
    registry: new FixedWindow(
        limit: 100,
        interval: '5 Minutes',
        
        // you may specify an id prefix:
        id: 'api',
        
        // you may change the storage used:
        storage: 'inmemory', // 'cache' is default
        
        // you may change the cache used if using the cache storage:
        cache: 'api-ratelimiter', // 'ratelimiter' is default
    ),
);
```

You may define a cache for the name ```ratelimiter``` (default) or ```api-ratelimiter``` (custom) in the [App Cache - Config](https://github.com/tobento-ch/app-cache#cache-config), otherwise the primary cache is used as default.

### Named

The ```Named``` registry may be used to create a rate limiter from a named rate limiter:

```php
use Tobento\App\RateLimiter\Registry\Named;
use Tobento\App\RateLimiter\Symfony\RateLimiterFactory;

$limiter = $limiterCreator->createFromRegistry(
    id: 'a-unique-identifier',
    registry: new Named('api'),
);
```

Check out the [Register Named Rate Limiters](#register-named-rate-limiters) section to learn more about it.

### No Limit

The ```NoLimit``` registry creates a Symfony no limit rate limiter:

```php
use Tobento\App\RateLimiter\Symfony\Registry\NoLimit;
use Tobento\App\RateLimiter\Symfony\RateLimiterFactory;

$limiter = $limiterCreator->createFromRegistry(
    id: 'a-unique-identifier',
    registry: new NoLimit(),
);
```

### Sliding Window

The ```SlidingWindow``` registry creates a [Symfony sliding window rate limiter](https://symfony.com/doc/current/rate_limiter.html#sliding-window-rate-limiter):

```php
use Tobento\App\RateLimiter\Symfony\Registry\SlidingWindow;
use Tobento\App\RateLimiter\Symfony\RateLimiterFactory;

$limiter = $limiterCreator->createFromRegistry(
    id: 'a-unique-identifier',
    registry: new SlidingWindow(
        limit: 100,
        interval: '5 Minutes',
        
        // you may specify an id prefix:
        id: 'api',
        
        // you may change the storage used:
        storage: 'inmemory', // 'cache' is default
        
        // you may change the cache used if using the cache storage:
        cache: 'api-ratelimiter', // 'ratelimiter' is default
    ),
);
```

You may define a cache for the name ```ratelimiter``` (default) or ```api-ratelimiter``` (custom) in the [App Cache - Config](https://github.com/tobento-ch/app-cache#cache-config), otherwise the primary cache is used as default.

### Token Bucket

The ```TokenBucket``` registry creates a [Symfony token bucket rate limiter](https://symfony.com/doc/current/rate_limiter.html#token-bucket-rate-limiter):

```php
use Tobento\App\RateLimiter\Symfony\Registry\TokenBucket;
use Tobento\App\RateLimiter\Symfony\RateLimiterFactory;

$limiter = $limiterCreator->createFromRegistry(
    id: 'a-unique-identifier',
    registry: new TokenBucket(
        limit: 5000,
        rateAmount: 500,
        rateInterval: '60 Minutes',
        
        // you may specify an id prefix:
        id: 'api',
        
        // you may change the storage used:
        storage: 'inmemory', // 'cache' is default
        
        // you may change the cache used if using the cache storage:
        cache: 'api-ratelimiter', // 'ratelimiter' is default
    ),
);
```

You may define a cache for the name ```ratelimiter``` (default) or ```api-ratelimiter``` (custom) in the [App Cache - Config](https://github.com/tobento-ch/app-cache#cache-config), otherwise the primary cache is used as default.

## Register Named Rate Limiters

**Register Named Rate Limiter via Config**

You can register named rate limiters in the config file ```app/config/rate_limiter.php```:

```php
return [
    // ...
    'limiters' => [
        'api' => new TokenBucket(limit: 10, rateAmount: 5, rateInterval: '5 Minutes'),
        'login' => new FixedWindow(limit: 2, interval: '1 Minutes'),
    ],
];
```

**Register Named Rate Limiter via Boot**

```php
use Tobento\App\Boot;
use Tobento\App\RateLimiter\Boot\RateLimiter;
use Tobento\App\RateLimiter\RegistriesInterface;
use Tobento\App\RateLimiter\Symfony\Registry\FixedWindow;

class RateLimitersBoot extends Boot
{
    public const BOOT = [
        // you may ensure the rate limiter boot.
        RateLimiter::class,
    ];
    
    public function boot()
    {
        // you may use the app on method to add only if requested:
        $app->on(
            RegistriesInterface::class,
            static function(RegistriesInterface $registries) {
                $registries->add(
                    name: 'api',
                    registry: new FixedWindow(limit: 2, interval: '1 Minutes'),
                );
            }
        );
    }
}
```

## Events

**Available Events**

| Event | Description |
| --- | --- |
| ```Tobento\App\RateLimiter\Event\AttemptsExceeded::class``` | The event will dispatch **after** the attempts exceeded |

**Supporting Events**

Simply, install the [App Event](https://github.com/tobento-ch/app-event) bundle.

# Credits

- [Tobias Strub](https://www.tobento.ch)
- [All Contributors](../../contributors)
- [Seldaek Monolog](https://github.com/Seldaek/monolog)