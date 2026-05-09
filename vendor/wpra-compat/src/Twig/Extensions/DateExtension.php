<?php
namespace Twig\Extensions;

use Twig\Extension\AbstractExtension;

class DateExtension extends AbstractExtension
{
    protected $translator;
    public function __construct($translator = null) { $this->translator = $translator; }
}
