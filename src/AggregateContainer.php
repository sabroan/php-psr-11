<?php

declare(strict_types=1);

namespace Sabroan\DI\Container;

use Psr\Container\ContainerInterface;
use Sabroan\DI\Container\Exception\NotFoundException;

/**
 * @api
 */
final class AggregateContainer implements ContainerInterface
{
    /** @var array<array-key, ContainerInterface> */
    private readonly array $containers;

    /**
     * @param ContainerInterface ...$containers
     * @psalm-capabilities read-props
     */
    public function __construct(
        ContainerInterface ...$containers,
    ) {
        $this->containers = $containers;
    }

    #[\Override]
    public function get(string $id): mixed
    {
        foreach ($this->containers as $container) {
            if ($container->has($id)) {
                return $container->get($id);
            }
        }

        throw new NotFoundException("Item '{$id}' not found");
    }

    #[\Override]
    public function has(string $id): bool
    {
        foreach ($this->containers as $container) {
            if ($container->has($id)) {
                return true;
            }
        }

        return false;
    }
}
