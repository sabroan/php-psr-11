<?php

declare(strict_types=1);

namespace Sabroan\DI\Container\Traits;

use Sabroan\DI\Container\Exception\NotFoundException;

/**
 * @property-read array<string, mixed> $items
 */
trait ContainerTrait
{
    public function get(string $id): mixed
    {
        if (!$this->has($id)) {
            throw new NotFoundException("Item '{$id}' not found");
        }

        return $this->items[$id];
    }

    public function has(string $id): bool
    {
        return isset($this->items[$id]) || \array_key_exists($id, $this->items);
    }
}
