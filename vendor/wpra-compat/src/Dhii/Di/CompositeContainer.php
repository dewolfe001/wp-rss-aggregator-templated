<?php

namespace Dhii\Di;

use Interop\Container\ContainerInterface;

/**
 * Writable composite container implementation.
 */
class CompositeContainer extends AbstractCompositeContainer implements WritableCompositeContainerInterface
{
    /**
     * @param ContainerInterface|null $parent The parent container.
     */
    public function __construct(ContainerInterface $parent = null)
    {
        if ($parent !== null) {
            $this->_setParentContainer($parent);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        return $this->_getDelegated($id);
    }

    /**
     * {@inheritdoc}
     */
    public function has($id)
    {
        return $this->_hasDelegated($id);
    }


    /**
     * {@inheritdoc}
     */
    public function getParentContainer()
    {
        return $this->_getParentContainer();
    }

    /**
     * {@inheritdoc}
     */
    public function getContainers()
    {
        return $this->_getContainers();
    }

    /**
     * {@inheritdoc}
     */
    public function add(ContainerInterface $container)
    {
        $this->_add($container);

        return $this;
    }
}
