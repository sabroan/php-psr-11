# PSR-11 DI Container

A modular dependency injection library, supports automatic dependency resolution, circular dependency detection, alias handling, and weak-reference object caching.

## Features

- **Lazy Auto-Wiring:** Automatically creates objects and resolves their dependencies, initializing them only when needed.
- **Loop Detection:** Catch circular dependencies during resolution.
- **Recursive Alias Resolution:** Automatically follows alias chains to find the target service.
- **Weak-Reference Caching:** Reuse objects while they're in use without preventing garbage collection.
- **Composite Delegation:** Combine multiple containers using AggregateContainer.

## Installation

Install the package using Composer:

`composer require sabroan/psr-11`

## Containers

### ImmutableContainer

A container initialized with a predefined set of services or parameters.

```php
use Sabroan\DI\Container\ImmutableContainer;

$container = new ImmutableContainer([
    'db.host' => '127.0.0.1',
    'db.port' => 5432,
    'logger'  => new \App\Logger(),
]);

if ($container->has('db.host')) {
    $host = $container->get('db.host');
}
```

### AggregateContainer

Combines multiple PSR-11 containers into one, checking each in order until the requested service is found.

```php
use Sabroan\DI\Container\AggregateContainer;
use Sabroan\DI\Container\ImmutableContainer;

$first = new ImmutableContainer(['config.path' => '/etc/app']);
$second = new ImmutableContainer(['db.host' => 'localhost']);

$aggregate = new AggregateContainer($first, $second);

$path = $aggregate->get('config.path');
$host = $aggregate->get('db.host');
```

### ReflectionContainer

Automatically creates concrete classes, resolves constructor dependencies from the container, and initializes objects only when needed.

```php
use Sabroan\DI\Container\ReflectionContainer;
use Sabroan\DI\Container\ImmutableContainer;

class DatabaseConnection {}

class UserRepository
{
    public function __construct(public DatabaseConnection $db) {}
}

$services = new ImmutableContainer([
    DatabaseConnection::class => new DatabaseConnection(),
]);

$container = new ReflectionContainer($services);

if ($container->has(UserRepository::class)) {
    $userRepo = $container->get(UserRepository::class);
}
```

### NoLoopContainer

Wraps a container to detect circular dependencies and throw an exception when a cycle is found.

#### Note:

- This resolves the entire dependency chain immediately, bypassing lazy initialization.
- Consider using ReferenceContainer to help manage circular dependencies through lazy initialization and object caching.

```php
use Sabroan\DI\Container\NoLoopContainer;
use Sabroan\DI\Container\Exception\ContainerException;

$guard = new NoLoopContainer($container);

try {
    $service = $guard->get('{circular.dependency.id}');
} catch (ContainerException $e) {
    // "Circular dependency detected for {circular.dependency.id}"
}
```

### RecursiveContainer

Resolves alias chains recursively to find the target service while detecting circular dependencies.

```php
use Sabroan\DI\Container\RecursiveContainer;
use Sabroan\DI\Container\ImmutableContainer;
use Psr\Log\LoggerInterface;

$container = new ImmutableContainer([
    LoggerInterface::class => 'app.logger',
    'app.logger'           => 'file.logger',
    'file.logger'          => new \App\FileLogger(),
]);

$recursive = new RecursiveContainer($container);
$logger = $recursive->get(LoggerInterface::class);
```

### ReferenceContainer

Caches objects using weak references, allowing unused objects to be garbage-collected and fetched again from the underlying container when needed.

```php
use Sabroan\DI\Container\ReferenceContainer;
$references = new ReferenceContainer($container);

$first = $references->get('service');
$second = $references->get('service');

var_dump($first === $second);

unset($first, $second);

$third = $references->get('service');
```

## Example

```php
use Psr\Log\LoggerInterface;
use Sabroan\DI\Container;

class App {
    public function __construct(
        LoggerInterface $logger
    ) {
        $logger->log('Hello, World!');
    }
}

$services = [
    LoggerInterface::class => \App\Logger::class,
];

$container = new \ReflectionClass(Container\ReflectionContainer::class)->newLazyGhost(
    static function ($reflection) use (&$container): void {
        $reflection->__construct($container);
    }
);
$container = new Container\AggregateContainer(
    new Container\ImmutableContainer($services),
    $container,
);
$container = new Container\ReferenceContainer($container);
$container = new Container\NoLoopContainer($container);
$container = new Container\RecursiveContainer($container);

$logger = $container->get(LoggerInterface::class);

$logger->log('Loading App class...');

$app = $container->get(App::class);

```

## License

This package is released under the MIT License. See the LICENSE file for details.
