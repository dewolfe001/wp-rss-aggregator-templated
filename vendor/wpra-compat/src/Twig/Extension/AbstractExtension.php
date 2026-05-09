<?php
namespace Twig\Extension;

abstract class AbstractExtension
{
    public function getName() { return get_class($this); }
    public function getFunctions() { return array(); }
    public function getFilters() { return array(); }
    public function getTokenParsers() { return array(); }
}
