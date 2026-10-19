<?php

declare(strict_types=1);

namespace Sabroan\DI\Container;

use Psr\Container\ContainerInterface;
use Sabroan\DI\Container\Traits\ContainerTrait;

/**
 * @api
 */
abstract class AbstractImmutableContainer implements ContainerInterface
{
    use ContainerTrait;

    /**
     * @param array<string, mixed> $items
     * @psalm-capabilities read-props
     */
    public function __construct(
        private readonly array $items,
    ) {}
}
