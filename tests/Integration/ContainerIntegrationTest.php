<?php

declare(strict_types=1);

namespace Sabroan\DI\Container\Tests\Integration;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Sabroan\DI\Container\AggregateContainer;
use Sabroan\DI\Container\Exception\ContainerException;
use Sabroan\DI\Container\Exception\NotFoundException;
use Sabroan\DI\Container\ImmutableContainer;
use Sabroan\DI\Container\NoLoopContainer;
use Sabroan\DI\Container\RecursiveContainer;
use Sabroan\DI\Container\ReferenceContainer;

#[CoversClass(AggregateContainer::class)]
#[CoversClass(NoLoopContainer::class)]
#[CoversClass(RecursiveContainer::class)]
#[CoversClass(ReferenceContainer::class)]
#[CoversClass(ImmutableContainer::class)]
final class ContainerIntegrationTest extends TestCase
{
    private function createIntegrationStack(
        ContainerInterface ...$additionalContainers,
    ): ContainerInterface {
        $configContainer = new ImmutableContainer([
            'db.host' => '127.0.0.1',
            'db.port' => 5432,
            'db.alias' => 'db.host',
        ]);

        $aggregate = new AggregateContainer($configContainer, ...$additionalContainers);
        $reference = new ReferenceContainer($aggregate);
        $guard     = new NoLoopContainer($reference);
        $recursive = new RecursiveContainer($guard);

        return $recursive;
    }

    public function testResolvesStaticConfigurationValues(): void
    {
        $container = $this->createIntegrationStack();

        self::assertTrue($container->has('db.host'));
        self::assertSame('127.0.0.1', $container->get('db.host'));
        self::assertSame(5432, $container->get('db.port'));
    }

    public function testResolvesAliasesRecursivelyAcrossStack(): void
    {
        $container = $this->createIntegrationStack();

        self::assertSame('127.0.0.1', $container->get('db.alias'));
    }

    public function testCachesObjectInstancesAsWeakReferences(): void
    {
        $service = new \stdClass();
        $customContainer = new ImmutableContainer(['service' => $service]);

        $container = $this->createIntegrationStack($customContainer);

        $firstResolution = $container->get('service');
        $secondResolution = $container->get('service');

        self::assertSame($service, $firstResolution);
        self::assertSame($firstResolution, $secondResolution);
    }

    public function testDetectsCircularDependencyInAliasChain(): void
    {
        $circularConfig = new ImmutableContainer([
            'service_a' => 'service_b',
            'service_b' => 'service_a',
        ]);

        $container = $this->createIntegrationStack($circularConfig);

        self::expectException(ContainerException::class);
        self::expectExceptionMessageIs('Circular dependency detected for service_a');

        $container->get('service_a');
    }

    public function testThrowsNotFoundExceptionWhenServiceIsMissingFromStack(): void
    {
        $container = $this->createIntegrationStack();

        self::assertFalse($container->has('non_existent_service'));

        self::expectException(NotFoundException::class);
        self::expectExceptionMessageIs("Item 'non_existent_service' not found");

        $container->get('non_existent_service');
    }
}
