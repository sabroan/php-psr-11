<?php

declare(strict_types=1);

namespace Sabroan\DI\Container\Tests\Fixtures;

use Sabroan\DI\Container\Traits\MutableTrait;

/**
 * @property array<string, mixed> $items
 */
final class MutableTraitFixture
{
    use MutableTrait;

    /** @var array<string, mixed> */
    public array $items = [];

    public function callSet(string $id, mixed $value): void
    {
        $this->set($id, $value);
    }

    public function callUnset(string $id): void
    {
        $this->unset($id);
    }
}
