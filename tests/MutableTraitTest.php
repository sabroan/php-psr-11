<?php

declare(strict_types=1);

namespace Sabroan\DI\Container\Tests;

use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sabroan\DI\Container\Tests\Fixtures\MutableTraitFixture;
use Sabroan\DI\Container\Traits\MutableTrait;

#[CoversTrait(MutableTrait::class)]
final class MutableTraitTest extends TestCase
{
    private function createTraitInstance(): MutableTraitFixture
    {
        return new MutableTraitFixture();
    }

    #[DataProvider('setProvider')]
    public function testSetStoresValueInItems(
        string $id,
        mixed $value,
    ): void {
        $instance = $this->createTraitInstance();
        $instance->callSet($id, $value);

        self::assertSame($value, $instance->items[$id]);
    }

    /**
     * @return array<string, array{string, mixed}>
     */
    public static function setProvider(): array
    {
        return [
            'string' => ['service_a', 'value_a'],
            'null' => ['service_b', null],
            'object' => ['service_c', new \stdClass()],
        ];
    }

    public function testUnsetRemovesValueFromItems(): void
    {
        $instance = $this->createTraitInstance();
        $instance->callSet('key', 'value');

        self::assertArrayHasKey('key', $instance->items);

        $instance->callUnset('key');

        self::assertArrayNotHasKey('key', $instance->items);
    }

    public function testUnsetNonExistentKeyDoesNotError(): void
    {
        $instance = $this->createTraitInstance();
        $instance->callUnset('non_existent');

        self::assertArrayNotHasKey('non_existent', $instance->items);
    }
}
