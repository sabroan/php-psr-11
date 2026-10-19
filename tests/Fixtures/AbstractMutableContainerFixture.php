<?php

declare(strict_types=1);

namespace Sabroan\DI\Container\Tests\Fixtures;

use Sabroan\DI\Container\AbstractMutableContainer;

final class AbstractMutableContainerFixture extends AbstractMutableContainer
{
    public function publicSet(string $id, mixed $value): void
    {
        $this->set($id, $value);
    }

    public function publicUnset(string $id): void
    {
        $this->unset($id);
    }
}
