<?php
/**
 * Lightweight bundled autoloader for runtime compatibility dependencies.
 *
 * This plugin is distributed without a Composer install step, so the runtime
 * dependency shims in vendor/wpra-compat are loaded from here.
 */

spl_autoload_register(function ($class) {
    static $prefixes = array(
        'Psr\\Container\\' => __DIR__ . '/wpra-compat/src/Psr/Container/',
        'Psr\\Log\\' => __DIR__ . '/wpra-compat/src/Psr/Log/',
        'Interop\\Container\\' => __DIR__ . '/wpra-compat/src/Interop/Container/',
        'Dhii\\Di\\' => __DIR__ . '/wpra-compat/src/Dhii/Di/',
        'Dhii\\Collection\\' => __DIR__ . '/wpra-compat/src/Dhii/Collection/',
        'Dhii\\Output\\' => __DIR__ . '/wpra-compat/src/Dhii/Output/',
        'Dhii\\Transformer\\' => __DIR__ . '/wpra-compat/src/Dhii/Transformer/',
        'Dhii\\Validation\\' => __DIR__ . '/wpra-compat/src/Dhii/Validation/',
        'Dhii\\Util\\Normalization\\' => __DIR__ . '/wpra-compat/src/Dhii/Util/Normalization/',
        'Symfony\\Component\\Translation\\' => __DIR__ . '/wpra-compat/src/Symfony/Component/Translation/',
        'Twig\\' => __DIR__ . '/wpra-compat/src/Twig/',
    );

    static $classMap = array(
        'Parsedown' => __DIR__ . '/wpra-compat/src/Parsedown.php',
        'Twig_SimpleFilter' => __DIR__ . '/wpra-compat/src/Twig/Legacy.php',
        'Twig_SimpleFunction' => __DIR__ . '/wpra-compat/src/Twig/Legacy.php',
        'Twig_Token' => __DIR__ . '/wpra-compat/src/Twig/Legacy.php',
        'Twig_Extensions_Node_Trans' => __DIR__ . '/wpra-compat/src/Twig/Legacy.php',
        'Twig_Extensions_TokenParser_Trans' => __DIR__ . '/wpra-compat/src/Twig/Legacy.php',
    );

    if (isset($classMap[$class])) {
        require_once $classMap[$class];
        return;
    }

    foreach ($prefixes as $prefix => $baseDir) {
        if (strpos($class, $prefix) !== 0) {
            continue;
        }

        $relativeClass = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
        return;
    }
});

return true;
