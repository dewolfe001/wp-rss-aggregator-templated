<?php
namespace Dhii\Util\Normalization;

trait NormalizeIterableCapableTrait
{
    protected function _normalizeIterable($iterable)
    {
        if (is_array($iterable)) {
            return $iterable;
        }
        if ($iterable instanceof \Traversable) {
            return iterator_to_array($iterable);
        }
        return array();
    }
}
