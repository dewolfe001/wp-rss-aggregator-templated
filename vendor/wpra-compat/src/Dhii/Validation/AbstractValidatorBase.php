<?php
namespace Dhii\Validation;

use Dhii\Validation\Exception\ValidationFailedException;

abstract class AbstractValidatorBase implements ValidatorInterface
{
    public function validate($subject)
    {
        $errors = $this->_getValidationErrors($subject);
        if (!empty($errors)) {
            throw new ValidationFailedException((array) $errors);
        }

        return true;
    }

    abstract protected function _getValidationErrors($subject);
}
