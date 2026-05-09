<?php

namespace Interop\Container\Exception;

use Psr\Container\NotFoundExceptionInterface;

/**
 * Compatibility alias for the historical container-interop not-found contract.
 */
interface NotFoundException extends ContainerException, NotFoundExceptionInterface
{
}
