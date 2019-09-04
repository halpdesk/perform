<?php

namespace Halpdesk\Perform\Exceptions;

use Exception;
use Halpdesk\Perform\Contracts\Exception as ExceptionContract;

class InvalidTypeException extends Exception implements ExceptionContract
{
    private $type;

    public function __construct($type = null, int $code = 1, Exception $previous = null)
    {
        $this->type = $type;
        $message = 'invalid_type: '.$type;
        parent::__construct($message, $code, $previous);
    }

    public function getType()
    {
        return $this->type;
    }

    public static function getValidTypes()
    {
        return [
            'string',
            'int',
            'integer',
            'float',
            'double',
            'real',
            'bool',
            'boolean',
            'array',
            'unset',
            'object',
            'datetime',
        ];
    }
}
