<?php

namespace Halpdesk\Tests\Queries;

use Halpdesk\Perform\Abstracts\Query;
use Halpdesk\Perform\Contracts\Query as QueryContract;
use Halpdesk\Perform\Contracts\Model as ModelContract;
use Illuminate\Support\Collection;
use Halpdesk\Tests\Models\Company;

class CompanyQuery extends Query implements QueryContract
{
    protected $model = Company::class;

    public function load() : void
    {
        $companies = json_file_to_array('./tests/data/companies.json');
        $this->setData(collect($companies));
    }

    // public function where($key, $value) : QueryContract
    // {
    //     $collection = $this->get();
    //     $this->collection = $collection->where($key, $value);
    //     return $this;
    // }

    // public function find($id) : ?ModelContract
    // {
    //     $collection = empty($this->collection) ? $this->get() : $this->collection;
    //     $data = $collection->where('id', $id)->first();
    //     if (is_array($data)) {
    //         return (new $this->model)->fill($data);
    //     } else if ($data instanceof $this->model) {
    //         return $data;
    //     } else {
    //         return null;
    //     }
    // }

    // public function first() : ?ModelContract
    // {
    //     $collection = empty($this->collection) ? $this->get() : $this->collection;
    //     $data = $collection->first();
    //     if (is_array($data)) {
    //         $model = (new $this->model)->fill($data);
    //     } else {
    //         $model = null;
    //     }
    //     return $model;
    // }
}
