<?php

namespace Dhii\Di\Exception;

use Exception;
use Interop\Container\Exception\ContainerException as InteropContainerException;
use Psr\Container\ContainerExceptionInterface;

/**
 * Exception thrown when a container cannot complete an operation.
 */
class ContainerException extends Exception implements ContainerExceptionInterface, InteropContainerException
{
}
