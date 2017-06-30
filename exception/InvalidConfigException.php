<?php

namespace base\exception;

/**
 * InvalidConfigException represents an exception caused by invalid parameters passed to a method.
 */
class InvalidConfigException extends \BadMethodCallException implements ExceptionInterface
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Invalid Configuration';
    }
}
