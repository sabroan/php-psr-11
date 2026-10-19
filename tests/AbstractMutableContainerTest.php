<?php

declare(strict_types=1);

namespace Sabroan\DI\Container\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Sabroan\DI\Container\AbstractMutableContainer;
use Sabroan\DI\Container\Exception\NotFoundException;
use Sabroan\DI\Container\Tests\Fixtures\AbstractMutableContainerFixture;

#[CoversClass(AbstractMutableContainer::class)]
final class AbstractMutableContainerTest extends TestCase
{
    private function createContainer(): AbstractMutableContainerFixture
    {
        return new AbstractMutableContainerFixture();
    }

    public function testImplementsContainerInterface(): void
    {
        $container = $this->createContainer();

        self::assertInstanceOf(ContainerInterface::class, $container);
    }

    public function testHasReturnsFalseWhenEmpty(): void
    {
        $container = $this->createContainer();

        self::assertFalse($container->has('service'));
    }

    public function testSetAndGetRetrievesService(): void
    {
        $container = $this->createContainer();
        $service = new \stdClass();

        $container->publicSet('service', $service);

        self::assertTrue($container->has('service'));
        self::assertSame($service, $container->get('service'));
    }

    public function testUnsetRemovesService(): void
    {
        $container = $this->createContainer();
        $container->publicSet('service', 'value');

        $container->publicUnset('service');

        self::assertFalse($container->has('service'));
    }

    public function testGetThrowsNotFoundExceptionWhenServiceIsUnset(): void
    {
        $container = $this->createContainer();
        $container->publicSet('service', 'value');
        $container->publicUnset('service');

        self::expectException(NotFoundException::class);
        self::expectExceptionMessageIs("Item 'service' not found");

        $container->get('service');
    }
}
