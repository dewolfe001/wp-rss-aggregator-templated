<?php
namespace Dhii\Validation\Exception;

interface ValidationFailedExceptionInterface
{
    public function getValidationErrors();
}
