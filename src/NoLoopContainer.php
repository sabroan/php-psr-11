<?php

declare(strict_types=1);

namespace Sabroan\DI\Container;

use Psr\Container\ContainerInterface;
use Sabroan\DI\Container\Exception\ContainerException;

/**
 * @api
 */
final class NoLoopContainer extends AbstractMutableContainer
{
    /**
     * @psalm-capabilities read-props
     */
    public function __construct(
        private readonly ContainerInterface $container,
    ) {}

    #[\Override]
    public function get(string $id): mixed
    {
        if (parent::has($id)) {
            throw new ContainerException("Circular dependency detected for {$id}");
        }

        parent::set($id, true);

        try {
            /** @var mixed $result */
            $result = $this->container->get($id);

            if (\is_object($result)) {
                (new \ReflectionClass($result))->initializeLazyObject($result);
            }

            return $result;
        } finally {
            parent::unset($id);
        }
    }

    #[\Override]
    public function has(string $id): bool
    {
        return $this->container->has($id);
    }
}
