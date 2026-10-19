<?php

declare(strict_types=1);

namespace Sabroan\DI\Container\Exception;

use Psr\Container\NotFoundExceptionInterface;

final class NotFoundException extends \OutOfRangeException implements NotFoundExceptionInterface {}
