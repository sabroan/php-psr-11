<?php

declare(strict_types=1);

namespace Sabroan\DI\Container\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Sabroan\DI\Container\AbstractImmutableContainer;
use Sabroan\DI\Container\Exception\NotFoundException;
use Sabroan\DI\Container\ImmutableContainer;

#[CoversClass(ImmutableContainer::class)]
#[CoversClass(AbstractImmutableContainer::class)]
final class ImmutableContainerTest extends TestCase
{
    /**
     * @param array<string, mixed> $items
     */
    private function createContainer(array $items = []): ImmutableContainer
    {
        return new ImmutableContainer($items);
    }

    public function testContainerImplementsPsrInterface(): void
    {
        $container = $this->createContainer();

        self::assertInstanceOf(ContainerInterface::class, $container);
    }

    public function testHasReturnsTrueWhenEntryExists(): void
    {
        $container = $this->createContainer(['db.host' => '127.0.0.1']);

        self::assertTrue($container->has('db.host'));
    }

    public function testHasReturnsFalseWhenEntryIsMissing(): void
    {
        $container = $this->createContainer();

        self::assertFalse($container->has('db.host'));
    }

    public function testGetRetrievesConfiguredValue(): void
    {
        $service = new \stdClass();
        $container = $this->createContainer(['service' => $service]);

        self::assertSame($service, $container->get('service'));
    }

    public function testGetThrowsNotFoundExceptionWhenEntryIsMissing(): void
    {
        $container = $this->createContainer();

        self::expectException(NotFoundException::class);
        self::expectExceptionMessageIs("Item 'undefined' not found");

        $container->get('undefined');
    }
}
