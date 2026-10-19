<?php

declare(strict_types=1);

namespace Sabroan\DI\Container\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Sabroan\DI\Container\RecursiveContainer;

#[CoversClass(RecursiveContainer::class)]
final class RecursiveContainerTest extends TestCase
{
    private function createRecursiveContainer(
        ContainerInterface $container,
    ): RecursiveContainer {
        return new RecursiveContainer($container);
    }

    #[DataProvider('hasProvider')]
    public function testHasDelegatesToWrappedContainer(bool $expected): void
    {
        $wrapped = self::createMock(ContainerInterface::class);
        $wrapped->expects($this->once())
            ->method('has')
            ->with('service')
            ->willReturn($expected);

        $container = $this->createRecursiveContainer($wrapped);

        self::assertSame($expected, $container->has('service'));
    }

    /**
     * @return array<string, array{bool}>
     */
    public static function hasProvider(): array
    {
        return [
            'exists' => [true],
            'missing' => [false],
        ];
    }

    public function testGetReturnsNonStringValueDirectly(): void
    {
        $expected = new \stdClass();
        $wrapped = self::createMock(ContainerInterface::class);
        $wrapped->expects($this->once())
            ->method('get')
            ->with('service')
            ->willReturn($expected);

        $container = $this->createRecursiveContainer($wrapped);

        self::assertSame($expected, $container->get('service'));
    }

    public function testGetReturnsStringValueWhenIdMatchesValue(): void
    {
        $wrapped = self::createMock(ContainerInterface::class);
        $wrapped->expects($this->once())
            ->method('get')
            ->with('same_key')
            ->willReturn('same_key');

        $container = $this->createRecursiveContainer($wrapped);

        self::assertSame('same_key', $container->get('same_key'));
    }

    public function testGetReturnsStringValueWhenValueIsNotInContainer(): void
    {
        $wrapped = self::createMock(ContainerInterface::class);
        $wrapped->expects($this->once())
            ->method('get')
            ->with('alias')
            ->willReturn('target_service');
        $wrapped->expects($this->once())
            ->method('has')
            ->with('target_service')
            ->willReturn(false);

        $container = $this->createRecursiveContainer($wrapped);

        self::assertSame('target_service', $container->get('alias'));
    }

    public function testGetResolvesAliasRecursively(): void
    {
        $resolvedObject = new \stdClass();

        $wrapped = self::createStub(ContainerInterface::class);
        $wrapped->method('has')->willReturnMap([
            ['alias1', true],
            ['alias2', true],
            ['concrete_service', true],
        ]);

        $wrapped->method('get')->willReturnMap([
            ['alias1', 'alias2'],
            ['alias2', 'concrete_service'],
            ['concrete_service', $resolvedObject],
        ]);

        $container = $this->createRecursiveContainer($wrapped);

        self::assertSame($resolvedObject, $container->get('alias1'));
    }
}
