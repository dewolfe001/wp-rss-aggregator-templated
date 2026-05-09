<?php
namespace Twig\Extension;

class CoreExtension extends AbstractExtension
{
    protected $timezone;
    public function setTimezone($timezone) { $this->timezone = $timezone; }
    public function getTimezone() { return $this->timezone; }
}
