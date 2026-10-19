<?php

declare(strict_types=1);

namespace Sabroan\DI\Container\Tests\Fixtures;

use Sabroan\DI\Container\Traits\ContainerTrait;

final class ContainerTraitFixture
{
    use ContainerTrait;

    /** @var array<string, mixed> */
    private readonly array $items;

    /** @param array<string, mixed> $items */
    public function __construct(array $items)
    {
        $this->items = $items;
    }
}
