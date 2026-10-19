<?php

declare(strict_types=1);

namespace Sabroan\DI\Container\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Sabroan\DI\Container\ReferenceContainer;

#[CoversClass(ReferenceContainer::class)]
final class ReferenceContainerTest extends TestCase
{
    private function createReferenceContainer(
        ContainerInterface $container,
    ): ReferenceContainer {
        return new ReferenceContainer($container);
    }

    #[DataProvider('hasProvider')]
    public function testHasDelegatesToWrappedContainer(bool $wrappedHas, bool $expected): void
    {
        $wrapped = self::createMock(ContainerInterface::class);
        $wrapped->expects($this->once())
            ->method('has')
            ->with('service')
            ->willReturn($wrappedHas);

        $container = $this->createReferenceContainer($wrapped);

        self::assertSame($expected, $container->has('service'));
    }

    /**
     * @return array<string, array{bool, bool}>
     */
    public static function hasProvider(): array
    {
        return [
            'missing' => [false, false],
            'existing' => [true, true],
        ];
    }

    public function testHasReturnsTrueWhenCachedLocally(): void
    {
        $service = new \stdClass();

        $wrapped = self::createMock(ContainerInterface::class);
        $wrapped->expects($this->never())->method('has');
        $wrapped->expects($this->once())
            ->method('get')
            ->with('service')
            ->willReturn($service);

        $container = $this->createReferenceContainer($wrapped);

        $container->get('service');

        self::assertTrue($container->has('service'));
    }

    public function testGetReturnsNonObjectWithoutCachingWeakReference(): void
    {
        $wrapped = self::createMock(ContainerInterface::class);
        $wrapped->expects($this->once())
            ->method('get')
            ->with('scalar_service')
            ->willReturn('string_value');

        $container = $this->createReferenceContainer($wrapped);

        self::assertSame('string_value', $container->get('scalar_service'));
    }

    public function testGetCachesObjectInWeakReference(): void
    {
        $wrapped = new class implements ContainerInterface {
            public int $calls = 0;

            public function get(string $id): mixed
            {
                $this->calls++;
                return new \stdClass();
            }

            public function has(string $id): bool
            {
                return true;
            }
        };

        $container = $this->createReferenceContainer($wrapped);

        $firstCall = $container->get('service');
        $secondCall = $container->get('service');

        self::assertSame(1, $wrapped->calls);
        self::assertSame($firstCall, $secondCall);
    }

    public function testGetUnsetsGarbageCollectedWeakReferenceAndRefetches(): void
    {
        $wrapped = new class implements ContainerInterface {
            public int $calls = 0;

            public function get(string $id): mixed
            {
                $this->calls++;
                return new \stdClass();
            }

            public function has(string $id): bool
            {
                return true;
            }
        };

        $container = $this->createReferenceContainer($wrapped);

        $firstCall = $container->get('service');
        self::assertSame(1, $wrapped->calls);

        unset($firstCall);
        gc_collect_cycles();

        $secondCall = $container->get('service');

        self::assertSame(2, $wrapped->calls);
        self::assertInstanceOf(\stdClass::class, $secondCall);
    }
}
