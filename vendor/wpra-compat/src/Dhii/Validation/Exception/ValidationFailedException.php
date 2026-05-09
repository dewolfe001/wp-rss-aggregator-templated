<?php
namespace Dhii\Validation\Exception;

class ValidationFailedException extends \RuntimeException implements ValidationFailedExceptionInterface
{
    protected $validationErrors;

    public function __construct(array $validationErrors, $message = 'Validation failed', $code = 0, \Exception $previous = null)
    {
        $this->validationErrors = $validationErrors;
        parent::__construct($message, $code, $previous);
    }

    public function getValidationErrors()
    {
        return $this->validationErrors;
    }
}
