<?php

namespace Halpdesk\Tests\Queries;

use Halpdesk\Perform\Abstracts\Query;
use Halpdesk\Perform\Contracts\Query as QueryContract;
use Halpdesk\Perform\Contracts\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Validator;
use Halpdesk\Tests\Models\TicketModel;
use Carbon\Carbon;

class EmployeeQuery extends QueryAbstract implements Query
{
    protected $collection;
    protected $model = Employee::class;

    public function create(array $parameters) : Model
    {
        Validator::make($parameters, [
            'companyId'     => 'required|integer',
            'name'          => 'required|string',
            'hiredAt'       => 'required|datetime',
            'hired'         => 'required|boolean',
            'salary'        => 'required|float',
        ])->validate();
        $model = (new $this->model)->fill($parameters);
        return $model;
    }

    public function get() : Collection
    {
        if (empty($this->collection)) {
            $body = file_get_contents('./tests/data/employees.json');
            $rows = json_decode($body, true);
            $tickets = [];
            foreach ($rows as $row) {
                $tickets[] = new $this->model($row);
            }
            $this->collection = collect($tickets);
        }
        return $this->collection;
    }

    public function where($key, $value) : Query
    {
        $collection = $this->get();
        $this->collection = $collection->where($key, $value);
        return $this;
    }

    public function find($id) : ?Model
    {
        $collection = empty($this->collection) ? $this->get() : $this->collection;
        $data = $collection->where('id', $id)->first();
        $model = (is_array($data)) ? (new $this->model)->fill($data) : null;
        return $model;
    }

    public function first() : ?Model
    {
        $collection = empty($this->collection) ? $this->get() : $this->collection;
        $data = $collection->first();
        if ($data instanceof $this->model) {
            return $data;
        } else if (is_array($data)) {
            $model = (new $this->model)->fill($data);
        } else {
            $model = null;
        }
        return $model;
    }
}
