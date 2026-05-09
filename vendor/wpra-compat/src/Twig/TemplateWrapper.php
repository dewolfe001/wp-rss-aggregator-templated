<?php
namespace Twig;

class TemplateWrapper
{
    protected $env;
    protected $name;

    public function __construct(Environment $env, $name)
    {
        $this->env = $env;
        $this->name = $name;
    }

    public function render($context = array())
    {
        return $this->env->render($this->name, is_array($context) ? $context : array());
    }
}
