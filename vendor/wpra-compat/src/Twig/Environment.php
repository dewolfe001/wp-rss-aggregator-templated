<?php
namespace Twig;

use Twig\Error\LoaderError;
use Twig\Extension\CoreExtension;

class Environment
{
    protected $loader;
    protected $options;
    protected $extensions = array();

    public function __construct($loader = null, array $options = array())
    {
        $this->loader = $loader;
        $this->options = $options;
        $this->extensions[CoreExtension::class] = new CoreExtension();
        $this->extensions['\Twig\Extension\CoreExtension'] = $this->extensions[CoreExtension::class];
    }

    public function addExtension($extension)
    {
        $this->extensions[get_class($extension)] = $extension;
        if (method_exists($extension, 'getName')) {
            $this->extensions[$extension->getName()] = $extension;
        }
    }

    public function getExtension($name)
    {
        if (isset($this->extensions[$name])) {
            return $this->extensions[$name];
        }
        $trimmed = ltrim($name, '\\');
        if (isset($this->extensions[$trimmed])) {
            return $this->extensions[$trimmed];
        }
        throw new \RuntimeException(sprintf('Twig extension "%s" is not registered', $name));
    }

    public function load($name)
    {
        if (!$this->loader || !method_exists($this->loader, 'getSourceContext')) {
            throw new LoaderError(sprintf('Cannot load Twig template "%s"', $name));
        }

        // Load the source now so missing templates fail before a wrapper is returned,
        // matching Twig's behavior closely enough for existence checks.
        $this->loader->getSourceContext($name);

        return new TemplateWrapper($this, $name);
    }

    public function render($name, array $context = array())
    {
        if (!$this->loader || !method_exists($this->loader, 'getSourceContext')) {
            throw new LoaderError(sprintf('Cannot load Twig template "%s"', $name));
        }

        $source = $this->loader->getSourceContext($name)->getCode();
        return $this->renderString($source, $context);
    }

    public function renderString($source, array $context = array())
    {
        $source = preg_replace_callback('/\{#.*?#\}/s', function () { return ''; }, $source);
        $source = preg_replace_callback('/\{\{\s*(.+?)\s*\}\}/s', function ($matches) use ($context) {
            $value = $this->resolveExpression($matches[1], $context);
            if (is_array($value) || is_object($value)) {
                return htmlspecialchars(json_encode($value), ENT_QUOTES, 'UTF-8');
            }
            return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        }, $source);

        return preg_replace('/\{%.*?%\}/s', '', $source);
    }

    protected function resolveExpression($expr, array $context)
    {
        $expr = trim(preg_replace('/\|.+$/', '', $expr));
        $first = isset($expr[0]) ? $expr[0] : '';
        if ($first === '"' || $first === "'") {
            return trim($expr, '"\'');
        }

        $parts = explode('.', $expr);
        $value = isset($context[$parts[0]]) ? $context[$parts[0]] : '';
        array_shift($parts);
        foreach ($parts as $part) {
            if (is_array($value) && array_key_exists($part, $value)) {
                $value = $value[$part];
            } elseif (is_object($value) && isset($value->$part)) {
                $value = $value->$part;
            } elseif (is_object($value)) {
                $method = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $part)));
                $value = method_exists($value, $method) ? $value->$method() : '';
            } else {
                return '';
            }
        }

        return $value;
    }
}
