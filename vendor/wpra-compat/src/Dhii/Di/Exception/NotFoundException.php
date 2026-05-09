<?php

namespace Dhii\Di\Exception;

use Interop\Container\Exception\NotFoundException as InteropNotFoundException;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Exception thrown when a service cannot be found in a container.
 */
class NotFoundException extends ContainerException implements NotFoundExceptionInterface, InteropNotFoundException
{
}
