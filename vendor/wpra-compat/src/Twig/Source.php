<?php
namespace Twig;

class Source
{
    protected $code;
    protected $name;
    protected $path;

    public function __construct($code, $name, $path = '')
    {
        $this->code = $code;
        $this->name = $name;
        $this->path = $path;
    }

    public function getCode() { return $this->code; }
    public function getName() { return $this->name; }
    public function getPath() { return $this->path; }
}
