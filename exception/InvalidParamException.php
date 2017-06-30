<?php

namespace base\exception;

/**
 * InvalidParamException represents an exception caused by invalid parameters passed to a method.
 */
class InvalidParamException extends \BadMethodCallException implements ExceptionInterface
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Invalid Parameter';
    }
}
