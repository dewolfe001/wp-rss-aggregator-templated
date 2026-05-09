<?php

namespace Interop\Container;

/**
 * Describes a provider that exposes service definitions to a container.
 *
 * WP RSS Aggregator's legacy DI layer uses the container-interop service
 * provider contract. Bundling this small interface prevents a fatal error when
 * the plugin is installed without Composer dependencies present.
 */
interface ServiceProvider
{
    /**
     * Returns the service definitions provided by this instance.
     *
     * @return array<string, mixed>
     */
    public function getServices();
}
