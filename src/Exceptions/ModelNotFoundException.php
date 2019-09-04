<?php

namespace Halpdesk\Perform\Exceptions;

use Exception;
use Halpdesk\Perform\Contracts\Exception as ExceptionContract;

class ModelNotFoundException extends Exception implements ExceptionContract
{
    private $model;
    private $ids;

    public function __construct($message = 'not_found', $model = null, $ids = null, int $code = 404, Exception $previous = null)
    {
        if (stristr($ids, ",")) {
            $ids = explode(",", $ids);
            array_walk($ids, function(&$id) {
                $id = (int)$id;
            });
        } else if (!is_numeric($ids)) {
            $ids = null;
        }
        $this->ids   = $ids;
        $this->model = $model;
        parent::__construct($message, $code, $previous);
    }

    public function getModel()
    {
        return $this->model;
    }

    public function getIds()
    {
        return $this->ids;
    }
}
