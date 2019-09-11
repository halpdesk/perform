<?php

namespace Halpdesk\Tests\Queries;

use Halpdesk\Perform\Abstracts\Query;
use Halpdesk\Perform\Contracts\Query as QueryContract;
use Halpdesk\Perform\Contracts\Model as ModelContract;
use Illuminate\Support\Collection;
use Illuminate\Support\Validator;
use Halpdesk\Tests\Models\Employee;
use Carbon\Carbon;

class EmployeeQuery extends Query implements QueryContract
{
    protected $collection;
    protected $model = Employee::class;

    public function create(array $parameters) : ModelContract
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
            $employees = [];
            foreach ($rows as $row) {
                $employees[] = new $this->model($row);
            }
            $this->collection = collect($employees);
        }
        return $this->collection;
    }

    public function where($key, $value) : QueryContract
    {
        $collection = empty($this->collection) ? $this->get() : $this->collection;
        $this->collection = $collection->where($key, $value);
        return $this;
    }

    public function find($id) : ?ModelContract
    {
        $collection = empty($this->collection) ? $this->get() : $this->collection;
        $model = $collection->where('id', $id)->first();
        return $model;
    }

    public function first() : ?ModelContract
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
