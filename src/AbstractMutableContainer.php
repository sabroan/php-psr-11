<?php

declare(strict_types=1);

namespace Sabroan\DI\Container;

use Psr\Container\ContainerInterface;
use Sabroan\DI\Container\Traits\ContainerTrait;
use Sabroan\DI\Container\Traits\MutableTrait;

/**
 * @api
 */
abstract class AbstractMutableContainer implements ContainerInterface
{
    use ContainerTrait;
    use MutableTrait {
        set as protected;
        unset as protected;
    }

    /** @var array<string, mixed> */
    private array $items = [];
}
