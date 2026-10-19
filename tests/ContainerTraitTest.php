<?php

declare(strict_types=1);

namespace Sabroan\DI\Container\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sabroan\DI\Container\Exception\NotFoundException;
use Sabroan\DI\Container\Tests\Fixtures\ContainerTraitFixture;

#[CoversClass(ContainerTraitFixture::class)]
final class ContainerTraitTest extends TestCase
{
    /** @param array<string, mixed> $items */
    private function createTraitInstance(
        array $items,
    ): ContainerTraitFixture {
        return new ContainerTraitFixture($items);
    }

    /**
     * @return array<string, array{array<string, mixed>, string, bool}>
     */
    public static function hasProvider(): array
    {
        return [
            'string' => [['foo' => 'bar'], 'foo', true],
            'null' => [['foo' => null], 'foo', true],
            'missing' => [['foo' => 'bar'], 'baz', false],
            'empty array' => [[], 'foo', false],
        ];
    }

    /**
     * @param array<string, mixed> $items
     */
    #[DataProvider('hasProvider')]
    public function testHasReturnsExpectedBoolean(
        array $items,
        string $id,
        bool $expected,
    ): void {
        $container = $this->createTraitInstance($items);

        self::assertSame($expected, $container->has($id));
    }

    /**
     * @return array<string, array{array<string, mixed>, string, mixed}>
     */
    public static function getSuccessProvider(): array
    {
        $dummyObject = new \stdClass();

        return [
            'string' => [['service' => 'instance'], 'service', 'instance'],
            'null' => [['null' => null], 'null', null],
            'object' => [['object' => $dummyObject], 'object', $dummyObject],
        ];
    }

    /**
     * @param array<string, mixed> $items
     */
    #[DataProvider('getSuccessProvider')]
    public function testGetReturnsItemValue(
        array $items,
        string $id,
        mixed $expected,
    ): void {
        $container = $this->createTraitInstance($items);

        self::assertSame($expected, $container->get($id));
    }

    public function testGetThrowsNotFoundExceptionWhenKeyDoesNotExist(): void
    {
        $container = $this->createTraitInstance([]);

        self::expectException(NotFoundException::class);
        self::expectExceptionMessageIs("Item 'missing_id' not found");

        $container->get('missing_id');
    }
}
