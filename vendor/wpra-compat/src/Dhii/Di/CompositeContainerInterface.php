<?php

namespace Dhii\Di;

use Interop\Container\ContainerInterface;

/**
 * A container that delegates lookups to child containers.
 */
interface CompositeContainerInterface extends ContainerInterface
{
    /**
     * @return ContainerInterface[]
     */
    public function getContainers();
}
