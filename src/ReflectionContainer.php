<?php

declare(strict_types=1);

namespace Sabroan\DI\Container;

use Psr\Container\ContainerInterface;
use Sabroan\DI\Container\Exception\ContainerException;
use Sabroan\DI\Container\Exception\NotFoundException;

/**
 * @api
 */
final class ReflectionContainer implements ContainerInterface
{
    /**
     * @psalm-capabilities read-props
     */
    public function __construct(
        private readonly ContainerInterface $container,
    ) {}

    #[\Override]
    public function has(string $id): bool
    {
        if (!\class_exists($id)) {
            return false;
        }

        return (new \ReflectionClass($id))->isInstantiable();
    }

    #[\Override]
    public function get(string $id): mixed
    {
        /** @var class-string $id */
        if (!$this->has($id)) {
            throw new NotFoundException("Item '{$id}' not found");
        }

        try {
            $class = new \Sabroan\DI\Reflection\ReflectionClass($id);
            $constructor = $class->getConstructor();

            if (null === $constructor) {
                return $class->newInstance();
            }

            $container = $this->container;
            return $class->newLazyGhost(static function (
                object $ghost,
            ) use (
                $constructor,
                $container,
            ): void {
                $constructor->invokeArgs(
                    $ghost,
                    $constructor->getParameters($container),
                );
            });
        } catch (\ReflectionException $exception) {
            throw new ContainerException(
                message: $exception->getMessage(),
                previous: $exception,
            );
        }
    }
}
