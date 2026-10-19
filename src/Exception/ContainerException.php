<?php

declare(strict_types=1);

namespace Sabroan\DI\Container\Exception;

use Psr\Container\ContainerExceptionInterface;

final class ContainerException extends \LogicException implements ContainerExceptionInterface {}
