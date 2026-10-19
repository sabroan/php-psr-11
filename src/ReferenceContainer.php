<?php

declare(strict_types=1);

namespace Sabroan\DI\Container;

use Psr\Container\ContainerInterface;

/**
 * @api
 */
final class ReferenceContainer extends AbstractMutableContainer
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
            /** @var mixed $ref */
            $ref = parent::get($id);

            if ($ref instanceof \WeakReference) {
                $item = $ref->get();
                if (null !== $item) {
                    return $item;
                }
            }

            parent::unset($id);
        }

        /** @var mixed $item */
        $item = $this->container->get($id);

        if (!\is_object($item)) {
            return $item;
        }

        parent::set($id, \WeakReference::create($item));

        return $item;
    }

    #[\Override]
    public function has(string $id): bool
    {
        return parent::has($id) || $this->container->has($id);
    }
}
