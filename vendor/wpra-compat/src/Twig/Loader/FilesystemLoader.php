<?php
namespace Twig\Loader;

use Twig\Error\LoaderError;
use Twig\Source;

class FilesystemLoader
{
    protected $paths = array();

    public function __construct($paths = array())
    {
        foreach ((array) $paths as $path) {
            $this->addPath($path);
        }
    }

    public function addPath($path, $namespace = '__main__')
    {
        $this->paths[$namespace][] = rtrim($path, '/\\');
    }

    public function getSourceContext($name)
    {
        foreach ($this->paths as $paths) {
            foreach ($paths as $path) {
                $file = $path . DIRECTORY_SEPARATOR . ltrim($name, '/\\');
                if (is_file($file)) {
                    return new Source(file_get_contents($file), $name, $file);
                }
            }
        }
        throw new LoaderError(sprintf('Unable to find template "%s".', $name));
    }
}
