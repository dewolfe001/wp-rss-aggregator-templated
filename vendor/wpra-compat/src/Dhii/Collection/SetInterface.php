<?php
namespace Dhii\Collection;

interface SetInterface extends \Countable
{
    public function add($item);
    public function addMany($items);
    public function remove($item);
    public function removeMany($items);
    public function has($item);
    public function clear();
    public function items();
}
