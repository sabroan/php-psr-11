<?php

declare(strict_types=1);

namespace Sabroan\DI\Container\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Sabroan\DI\Container\AggregateContainer;
use Sabroan\DI\Container\Exception\NotFoundException;

#[CoversClass(AggregateContainer::class)]
final class AggregateContainerTest extends TestCase
{
    private function createAggregate(
        ContainerInterface ...$containers,
    ): AggregateContainer {
        return new AggregateContainer(...$containers);
    }

    private function createContainerStub(
        string $id,
        bool $has,
        mixed $value = null,
    ): ContainerInterface {
        $stub = self::createStub(ContainerInterface::class);
        $stub->method('has')->willReturnMap([[$id, $has]]);

        if ($has) {
            $stub->method('get')->willReturnMap([[$id, $value]]);
        }

        return $stub;
    }

    public function testHasReturnsFalseWhenEmpty(): void
    {
        $aggregate = $this->createAggregate();

        self::assertFalse($aggregate->has('non_existent_service'));
    }

    public function testHasReturnsFalseWhenNoContainerHasId(): void
    {
        $stub1 = $this->createContainerStub('service', false);
        $stub2 = $this->createContainerStub('service', false);

        $aggregate = $this->createAggregate($stub1, $stub2);

        self::assertFalse($aggregate->has('service'));
    }

    public function testHasReturnsTrueWhenOneContainerHasId(): void
    {
        $stub1 = $this->createContainerStub('service', false);
        $stub2 = $this->createContainerStub('service', true);

        $aggregate = $this->createAggregate($stub1, $stub2);

        self::assertTrue($aggregate->has('service'));
    }

    public function testGetReturnsValueFromMatchingContainer(): void
    {
        $expectedValue = new \stdClass();

        $stub1 = $this->createContainerStub('service', false);
        $stub2 = $this->createContainerStub('service', true, $expectedValue);

        $aggregate = $this->createAggregate($stub1, $stub2);

        self::assertSame($expectedValue, $aggregate->get('service'));
    }

    public function testGetReturnsValueFromFirstContainerPriority(): void
    {
        $stub1 = $this->createContainerStub('service', true, 'first_result');
        $stub2 = $this->createContainerStub('service', true, 'second_result');

        $aggregate = $this->createAggregate($stub1, $stub2);

        self::assertSame('first_result', $aggregate->get('service'));
    }

    public function testGetThrowsNotFoundExceptionWhenServiceIsMissing(): void
    {
        $stub = $this->createContainerStub('missing_service', false);

        $aggregate = $this->createAggregate($stub);

        self::expectException(NotFoundException::class);
        self::expectExceptionMessageIs("Item 'missing_service' not found");

        $aggregate->get('missing_service');
    }

    /**
     * @param list<bool> $containersHasMap
     */
    #[DataProvider('hasMethodProvider')]
    public function testHasBehaviorWithMultipleConfigurations(
        array $containersHasMap,
        string $id,
        bool $expected,
    ): void {
        $containers = array_map(
            fn(bool $has): ContainerInterface => $this->createContainerStub($id, $has),
            $containersHasMap,
        );

        $aggregate = $this->createAggregate(...$containers);

        self::assertSame($expected, $aggregate->has($id));
    }

    /**
     * @return array<string, array{list<bool>, string, bool}>
     */
    public static function hasMethodProvider(): array
    {
        return [
            'empty containers list' => [[], 'service_id', false],
            'single container missing service' => [[false], 'service_id', false],
            'single container matching service' => [[true], 'service_id', true],
            'first container missing second matching' => [[false, true], 'service_id', true],
            'multiple containers with none matching' => [
                [false, false, false],
                'service_id',
                false,
            ],
        ];
    }
}
