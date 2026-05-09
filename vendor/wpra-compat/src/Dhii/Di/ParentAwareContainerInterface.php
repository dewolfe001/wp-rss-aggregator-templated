<?php

namespace Dhii\Di;

use Interop\Container\ContainerInterface;

/**
 * A container that can expose a parent container.
 */
interface ParentAwareContainerInterface
{
    /**
     * @return ContainerInterface|null
     */
    public function getParentContainer();
}
