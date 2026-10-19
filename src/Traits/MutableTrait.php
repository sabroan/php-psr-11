<?php

declare(strict_types=1);

namespace Sabroan\DI\Container\Traits;

/**
 * @property-write array<string, mixed> $items
 * @psalm-capabilities read-props|write-this-props|write-refs
 */
trait MutableTrait
{
    private function set(string $id, mixed $value): void
    {
        $this->items[$id] = $value;
    }

    private function unset(string $id): void
    {
        unset($this->items[$id]);
    }
}
