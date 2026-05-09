<?php

namespace Dhii\Di;

use Interop\Container\ContainerInterface;

/**
 * A composite container that accepts additional child containers.
 */
interface WritableCompositeContainerInterface extends CompositeContainerInterface
{
    /**
     * @param ContainerInterface $container The child container to add.
     *
     * @return $this
     */
    public function add(ContainerInterface $container);
}
