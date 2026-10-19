<?php

declare(strict_types=1);

namespace Sabroan\DI\Container\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\NotFoundExceptionInterface;
use Sabroan\DI\Container\Exception\NotFoundException;

#[CoversClass(NotFoundException::class)]
final class NotFoundExceptionTest extends TestCase
{
    private function createException(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
    ): NotFoundException {
        return new NotFoundException($message, $code, $previous);
    }

    public function testImplementsPsrNotFoundExceptionInterface(): void
    {
        $exception = $this->createException();

        self::assertInstanceOf(NotFoundExceptionInterface::class, $exception);
    }

    public function testStoresExceptionMetadataCorrectly(): void
    {
        $previous = new \RuntimeException('Internal resolution error');
        $exception = $this->createException(
            'Item "missing_service" not found',
            404,
            $previous,
        );

        self::assertSame('Item "missing_service" not found', $exception->getMessage());
        self::assertSame(404, $exception->getCode());
        self::assertSame($previous, $exception->getPrevious());
    }
}
