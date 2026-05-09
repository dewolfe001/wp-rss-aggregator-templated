<?php

namespace Dhii\Di;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;

/**
 * A container whose definitions can be extended at runtime.
 */
interface WritableContainerInterface extends ContainerInterface
{
    /**
     * @param ServiceProvider $serviceProvider The provider to register.
     *
     * @return $this
     */
    public function register(ServiceProvider $serviceProvider);

    /**
     * @param string $id         The service ID.
     * @param mixed  $definition The service definition.
     *
     * @return $this
     */
    public function set($id, $definition);
}
