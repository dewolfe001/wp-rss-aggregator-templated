<?php

namespace Psr\Container;

/**
 * Describes the interface of a container that exposes methods to read its entries.
 *
 * This local copy provides the PSR-11 interface required by the bundled
 * container-interop compatibility interfaces when a Composer autoloader is not
 * available in the WordPress installation.
 */
interface ContainerInterface
{
    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return mixed Entry.
     */
    public function get($id);

    /**
     * Returns true if the container can return an entry for the given identifier.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has($id);
}
