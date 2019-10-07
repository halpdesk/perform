<?php

namespace Halpdesk\Perform\Exceptions;

use Exception;
use Halpdesk\Perform\Contracts\Exception as ExceptionContract;

class NotImplementedException extends Exception implements ExceptionContract
{
    private $class;
    private $method;

    public function __construct($class = null, $method = null, $message = 'method_not_implemented', $code = 1, Exception $previous = null)
    {
        $this->class  = $class;
        $this->method = $method;
        parent::__construct($message, $code, $previous);
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getClass()
    {
        return $this->class;
    }
}
