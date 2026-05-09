<?php

namespace Dhii\Di;

use Dhii\Di\Exception\ContainerException;
use Dhii\Di\Exception\NotFoundException;
use Exception as BaseException;
use Interop\Container\ContainerInterface;

/**
 * Minimal composite container used when Composer dependencies are absent.
 */
abstract class AbstractCompositeContainer implements CompositeContainerInterface, ParentAwareContainerInterface
{
    /**
     * @var ContainerInterface[]
     */
    protected $containers = array();

    /**
     * @var ContainerInterface|null
     */
    protected $parentContainer;

    /**
     * @param string $id The service ID.
     *
     * @return bool
     */
    protected function _hasDelegated($id)
    {
        foreach ($this->_getContainers() as $container) {
            if ($container->has($id)) {
                return true;
            }
        }

        $parent = $this->_getParentContainer();

        return $parent !== null && $parent->has($id);
    }

    /**
     * @param string $id The service ID.
     *
     * @return mixed
     */
    protected function _getDelegated($id)
    {
        foreach ($this->_getContainers() as $container) {
            if ($container->has($id)) {
                return $container->get($id);
            }
        }

        $parent = $this->_getParentContainer();
        if ($parent !== null && $parent->has($id)) {
            return $parent->get($id);
        }

        throw $this->_createNotFoundException(sprintf('Service "%s" was not found', $id));
    }

    /**
     * @return ContainerInterface[]
     */
    protected function _getContainers()
    {
        return $this->containers;
    }

    /**
     * @param ContainerInterface $container The child container.
     *
     * @return $this
     */
    protected function _add(ContainerInterface $container)
    {
        $this->containers[] = $container;

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
