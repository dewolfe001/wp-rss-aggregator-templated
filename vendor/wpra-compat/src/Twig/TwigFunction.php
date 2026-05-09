<?php
namespace Twig;

class TwigFunction
{
    public $name;
    public $callable;
    public $options;
    public function __construct($name, $callable = null, array $options = array())
    {
        $this->name = $name;
        $this->callable = $callable;
        $this->options = $options;
    }
}
