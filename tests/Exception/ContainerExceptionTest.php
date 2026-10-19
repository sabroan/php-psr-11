<?php

declare(strict_types=1);

namespace Sabroan\DI\Container\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Sabroan\DI\Container\Exception\ContainerException;

#[CoversClass(ContainerException::class)]
final class ContainerExceptionTest extends TestCase
{
    private function createException(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
    ): ContainerException {
        return new ContainerException($message, $code, $previous);
    }

    public function testImplementsPsrContainerExceptionInterface(): void
    {
        $exception = $this->createException();

        self::assertInstanceOf(ContainerExceptionInterface::class, $exception);
    }

    public function testStoresExceptionMetadataCorrectly(): void
    {
        $previous = new \ReflectionException('Class does not exist');
        $exception = $this->createException(
            'Circular dependency detected',
            500,
            $previous,
        );

        self::assertSame('Circular dependency detected', $exception->getMessage());
        self::assertSame(500, $exception->getCode());
        self::assertSame($previous, $exception->getPrevious());
    }
}
