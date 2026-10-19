<?php

declare(strict_types=1);

namespace Sabroan\DI\Container\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Sabroan\DI\Container\Exception\ContainerException;
use Sabroan\DI\Container\NoLoopContainer;

#[CoversClass(NoLoopContainer::class)]
final class NoLoopContainerTest extends TestCase
{
    private function createGuardContainer(
        ContainerInterface $container,
    ): NoLoopContainer {
        return new NoLoopContainer($container);
    }

    #[DataProvider('hasProvider')]
    public function testHasDelegatesToWrappedContainer(bool $expected): void
    {
        $wrapped = self::createMock(ContainerInterface::class);
        $wrapped->expects($this->once())
            ->method('has')
            ->with('service')
            ->willReturn($expected);

        $guard = $this->createGuardContainer($wrapped);

        self::assertSame($expected, $guard->has('service'));
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

    public function testGetReturnsValueFromWrappedContainer(): void
    {
        $wrapped = self::createMock(ContainerInterface::class);
        $wrapped->expects($this->once())
            ->method('get')
            ->with('service')
            ->willReturn('scalar_value');

        $guard = $this->createGuardContainer($wrapped);

        self::assertSame('scalar_value', $guard->get('service'));
    }

    public function testGetInitializesObjectResult(): void
    {
        $object = new \stdClass();
        $wrapped = self::createMock(ContainerInterface::class);
        $wrapped->expects($this->once())
            ->method('get')
            ->with('service')
            ->willReturn($object);

        $guard = $this->createGuardContainer($wrapped);

        self::assertSame($object, $guard->get('service'));
    }

    public function testGetCleansUpStateAllowingSequentialCalls(): void
    {
        $wrapped = self::createMock(ContainerInterface::class);
        $wrapped->expects($this->exactly(2))
            ->method('get')
            ->with('service')
            ->willReturn('value');

        $guard = $this->createGuardContainer($wrapped);

        $first = $guard->get('service');
        $second = $guard->get('service');

        self::assertSame('value', $first);
        self::assertSame('value', $second);
    }

    public function testGetThrowsContainerExceptionOnCircularDependency(): void
    {
        $wrapped = new class implements ContainerInterface {
            public ?NoLoopContainer $guard = null;

            public function get(string $id): mixed
            {
                return $this->guard?->get($id);
            }

            public function has(string $id): bool
            {
                return true;
            }
        };

        $guard = $this->createGuardContainer($wrapped);
        $wrapped->guard = $guard;

        self::expectException(ContainerException::class);
        self::expectExceptionMessageIs('Circular dependency detected for recursive_service');

        $guard->get('recursive_service');
    }

    public function testGetResetsGuardStateWhenWrappedContainerThrowsException(): void
    {
        $wrapped = new class implements ContainerInterface {
            public bool $shouldThrow = true;

            public function get(string $id): mixed
            {
                if ($this->shouldThrow) {
                    throw new \RuntimeException('Resolution failed');
                }

                return 'success';
            }

            public function has(string $id): bool
            {
                return true;
            }
        };

        $guard = $this->createGuardContainer($wrapped);

        try {
            $guard->get('failing_service');
        } catch (\RuntimeException) {
        }

        $wrapped->shouldThrow = false;

        self::assertSame('success', $guard->get('failing_service'));
    }
}
