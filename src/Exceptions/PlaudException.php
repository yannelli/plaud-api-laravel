<?php

namespace Yannelli\LaravelPlaud\Exceptions;

use Exception;

class PlaudException extends Exception
{
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
