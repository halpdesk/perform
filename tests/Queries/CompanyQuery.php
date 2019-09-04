<?php

namespace Halpdesk\Tests\Queries;

use Halpdesk\Perform\Abstracts\Query;
use Halpdesk\Perform\Contracts\Query as QueryContract;
use Halpdesk\Perform\Contracts\Model;
use Illuminate\Support\Collection;
use Halpdesk\Tests\Models\Company;

class CompanyQuery extends Query implements QueryContract
{
    protected $collection;
    protected $model = Company::class;

    public function get() : Collection
    {
        if (empty($this->collection)) {
            $body = file_get_contents('./tests/data/companies.json');
            $this->collection = new Collection(json_decode($body, true));
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
        if (is_array($data)) {
            $model = (new $this->model)->fill($data);
        } else {
            $model = null;
        }
        return $model;
    }

    public function first() : ?Model
    {
        $collection = empty($this->collection) ? $this->get() : $this->collection;
        $data = $collection->first();
        if (is_array($data)) {
            $model = (new $this->model)->fill($data);
        } else {
            $model = null;
        }
        return $model;
    }
}
