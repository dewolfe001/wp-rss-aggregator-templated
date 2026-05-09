<?php

if (!class_exists('Twig_SimpleFilter') && class_exists('Twig\TwigFilter')) {
    class Twig_SimpleFilter extends Twig\TwigFilter {}
}
if (!class_exists('Twig_SimpleFunction') && class_exists('Twig\TwigFunction')) {
    class Twig_SimpleFunction extends Twig\TwigFunction {}
}
if (!class_exists('Twig_Token')) {
    class Twig_Token {}
}
if (!class_exists('Twig_Extensions_Node_Trans')) {
    class Twig_Extensions_Node_Trans {}
}
if (!class_exists('Twig_Extensions_TokenParser_Trans')) {
    class Twig_Extensions_TokenParser_Trans {}
}
