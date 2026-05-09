<?php

namespace Dhii\Di;

use Dhii\Di\Exception\ContainerException;
use Dhii\Di\Exception\NotFoundException;
use Exception as BaseException;
use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;

/**
 * Minimal parent-aware DI container used when Composer dependencies are absent.
 */
abstract class AbstractParentAwareContainer implements ContainerInterface
{
    /**
     * @var array<string, mixed>
     */
    protected $definitions = array();

    /**
     * @var array<string, mixed>
     */
    protected $services = array();

    /**
     * @var ContainerInterface|null
     */
    protected $parentContainer;

    /**
     * @param string $id The service ID.
     *
     * @return bool
     */
    protected function _has($id)
    {
        if (array_key_exists($id, $this->services) || array_key_exists($id, $this->definitions)) {
            return true;
        }

        $parent = $this->_getParentContainer();

        return $parent !== null && $parent->has($id);
    }

    /**
     * @param string $id The service ID.
     *
     * @return mixed
     */
    protected function _get($id)
    {
        if (array_key_exists($id, $this->services)) {
            return $this->services[$id];
        }

        if (array_key_exists($id, $this->definitions)) {
            return $this->services[$id] = $this->_resolve($id, array());
        }

        $parent = $this->_getParentContainer();
        if ($parent !== null && $parent->has($id)) {
            return $parent->get($id);
        }

        throw $this->_createNotFoundException(sprintf('Service "%s" was not found', $id));
    }

    /**
     * @param string $id     The service ID.
     * @param array  $config Runtime service configuration.
     *
     * @return mixed
     */
    protected function _make($id, array $config = array())
    {
        if (array_key_exists($id, $this->definitions)) {
            return $this->_resolve($id, $config);
        }

        $parent = $this->_getParentContainer();
        if ($parent instanceof FactoryInterface && $parent->has($id)) {
            return $parent->make($id, $config);
        }

        if ($parent !== null && $parent->has($id)) {
            return $parent->get($id);
        }

        throw $this->_createNotFoundException(sprintf('Service "%s" was not found', $id));
    }

    /**
     * @param ServiceProvider $serviceProvider The provider to register.
     *
     * @return $this
     */
    protected function _register(ServiceProvider $serviceProvider)
    {
        foreach ((array) $serviceProvider->getServices() as $id => $definition) {
            $this->_set($id, $definition);
        }

        return $this;
    }

    /**
     * @param string $id         The service ID.
     * @param mixed  $definition The service definition.
     *
     * @return $this
     */
    protected function _set($id, $definition)
    {
        $this->definitions[$id] = $definition;
        unset($this->services[$id]);

        return $this;
    }

    /**
     * @param ContainerInterface|null $container The parent container.
     *
     * @return $this
     */
    protected function _setParentContainer(ContainerInterface $container = null)
    {
        $this->parentContainer = $container;

        return $this;
    }

    /**
     * @return ContainerInterface|null
     */
    protected function _getParentContainer()
    {
        return $this->parentContainer;
    }

    /**
     * @param string $id     The service ID.
     * @param array  $config Runtime service configuration.
     *
     * @return mixed
     */
    protected function _resolve($id, array $config = array())
    {
        $definition = $this->definitions[$id];

        try {
            if (is_callable($definition)) {
                return call_user_func($definition, $this, null, $config);
            }

            return $definition;
        } catch (NotFoundException $exception) {
            throw $exception;
        } catch (ContainerException $exception) {
            throw $exception;
        } catch (BaseException $exception) {
            throw $this->_createContainerException(
                sprintf('Could not create service "%s": %s', $id, $exception->getMessage()),
                0,
                $exception
            );
        }
    }

    /**
     * @return NotFoundException
     */
    protected function _createNotFoundException($message, $code = 0, BaseException $innerException = null)
    {
        return new NotFoundException($message, $code, $innerException);
    }

    /**
     * @return ContainerException
     */
    protected function _createContainerException($message, $code = 0, BaseException $innerException = null)
    {
        return new ContainerException($message, $code, $innerException);
    }
}
