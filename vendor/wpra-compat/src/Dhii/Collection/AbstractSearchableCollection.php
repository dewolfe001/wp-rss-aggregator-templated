<?php
namespace Dhii\Collection;

abstract class AbstractSearchableCollection
{
    protected $items = array();
    protected $itemCache = null;

    protected function _construct() {}

    protected function _addItem($item)
    {
        if (!$this->_hasItem($item)) {
            $this->items[] = $item;
        }
    }

    protected function _addItems($items)
    {
        foreach ($items as $item) {
            if (method_exists($this, '_validateItem')) {
                $this->_validateItem($item);
            }
            $this->_addItem($item);
        }
    }

    protected function _removeItem($item)
    {
        foreach ($this->items as $idx => $stored) {
            if ($stored === $item || $stored == $item) {
                unset($this->items[$idx]);
            }
        }
        $this->items = array_values($this->items);
    }

    protected function _hasItem($item)
    {
        foreach ($this->items as $stored) {
            if ($stored === $item || $stored == $item) {
                return true;
            }
        }
        return false;
    }

    protected function _getItems()
    {
        return $this->items;
    }

    protected function _clearItemCache()
    {
        $this->itemCache = null;
    }

    protected function _getCachedItems()
    {
        if ($this->itemCache === null) {
            $this->itemCache = $this->_getItems();
        }
        return $this->itemCache;
    }

    protected function _count()
    {
        return count($this->_getCachedItems());
    }

    protected function _search($callback)
    {
        $result = array();
        $isContinue = true;
        foreach ($this->_getItems() as $idx => $item) {
            $value = call_user_func_array($callback, array($idx, $item, &$isContinue));
            if ($value !== null) {
                $result[] = $value;
            }
            if (!$isContinue) {
                break;
            }
        }
        return $result;
    }

    protected function _arrayConvert($items)
    {
        if (is_array($items)) {
            return $items;
        }
        if ($items instanceof \Traversable) {
            return iterator_to_array($items);
        }
        if ($items instanceof SetInterface) {
            return $items->items();
        }
        return (array) $items;
    }
}
