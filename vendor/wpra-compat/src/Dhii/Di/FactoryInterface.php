<?php

namespace Dhii\Di;

/**
 * Creates services from container definitions with optional runtime config.
 */
interface FactoryInterface
{
    /**
     * @param string $id     The service ID to create.
     * @param array  $config Runtime configuration for the service definition.
     *
     * @return mixed
     */
    public function make($id, array $config = array());
}
