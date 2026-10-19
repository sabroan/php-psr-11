<?php

declare(strict_types=1);

namespace Sabroan\DI\Container\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Sabroan\DI\Container\Exception\NotFoundException;
use Sabroan\DI\Container\ReflectionContainer;
use Sabroan\DI\Container\Tests\Fixtures\AbstractReflectionDummy;

#[CoversClass(ReflectionContainer::class)]
final class ReflectionContainerTest extends TestCase
{
    private function createReflectionContainer(
        ContainerInterface $container,
    ): ReflectionContainer {
        return new ReflectionContainer($container);
    }

    #[DataProvider('hasProvider')]
    public function testHasReturnsExpectedValue(
        string $id,
        bool $expected,
    ): void {
        $wrapped = self::createStub(ContainerInterface::class);
        $container = $this->createReflectionContainer($wrapped);

        self::assertSame($expected, $container->has($id));
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public static function hasProvider(): array
    {
        return [
            'non-existent' => ['NonExistentClassNamespace\Class', false],
            'interface' => [ContainerInterface::class, false],
            'abstract' => [AbstractReflectionDummy::class, false],
            'instantiable' => [\stdClass::class, true],
        ];
    }

    public function testGetThrowsNotFoundExceptionWhenClassIsNotInstantiable(): void
    {
        $wrapped = self::createStub(ContainerInterface::class);
        $container = $this->createReflectionContainer($wrapped);

        self::expectException(NotFoundException::class);
        self::expectExceptionMessageIs("Item 'NonExistentClass' not found");

        $container->get('NonExistentClass');
    }

    public function testGetInstantiatesClassWithoutConstructor(): void
    {
        $wrapped = self::createStub(ContainerInterface::class);
        $container = $this->createReflectionContainer($wrapped);

        $result = $container->get(\stdClass::class);

        self::assertInstanceOf(\stdClass::class, $result);
    }
}
