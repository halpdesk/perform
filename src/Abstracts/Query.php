<?php

namespace Halpdesk\Perform\Abstracts;

use Illuminate\Support\Collection;
use Halpdesk\Perform\Exceptions\ModelNotFoundException;
use Halpdesk\Perform\Exceptions\QueryException;
use Halpdesk\Perform\Exceptions\NotImplementedException;
use Halpdesk\Perform\Contracts\Query as QueryContract;
use Halpdesk\Perform\Contracts\Model as ModelContract;

abstract class Query
{
    protected $collection;
    protected $model;

    /**
     * Add a basic where clause to the query
     * @throws QueryException
     * @return QueryContract
     */
    public function where($key, $value) : QueryContract
    {
        // Must implement get() first
        $this->collection = $this->get()->where($key, $value);
        return $this;
    }

    /**
     * Add an "order by" clause to the query
     * @throws QueryException
     * @return QueryContract
     */
    public function orderBy() : QueryContract
    {
        throw new NotImplementedException();
    }

    /**
     * Alias to set the "limit" value of the query
     * @throws QueryException
     * @return QueryContract
     */
    public function take() : QueryContract
    {
        throw new NotImplementedException();
    }

    /**
     * Execute query for a single record by id
     * Return null if record with $id cannot be found
     * @return ModelContract|null
     */
    public function find($id) : ?ModelContract
    {
        return $this->where('id', $id)->first();
    }

    /**
     * Execute query for a single record by id
     * @throws ModelNotFoundException
     * @return ModelContract
     */
    public function findOrFail($id) : ModelContract
    {
        $model = $this->find($id);
        if (!($model instanceof $this->model)) {
            throw new ModelNotFoundException('not_found: '.$this->model.' ('.$id.')', $this->model, $id);
        }
        return $model;
    }

    /**
     * Execute the query and return a collection
     * @return Collection
     */
    public function get() : Collection
    {
        throw new NotImplementedException();
    }

    /**
     * Execute the query and get the first result
     * Return null if record with $id cannot be found
     * @return ModelContract|null
     */
    public function first() : ?ModelContract
    {
        return $this->get()->first();
    }

    /**
     * Execute the query and get the first result
     * @throws ModelNotFoundException
     * @return ModelContract
     */
    public function firstOrFail() : ModelContract
    {
        $model = $this->first();
        if (!($model instanceof $this->model)) {
            throw new ModelNotFoundException('not_found', $this->model, null);
        }
        return $model;
    }

    /**
     * Insert a record to the repository
     * @throws QueryException
     * @return ModelContract
     */
    public function create(array $parameters) : ModelContract
    {
        throw new NotImplementedException();
    }

    /**
     * Update records in the repository
     * @throws QueryException
     * @return ModelContract
     */
    public function update(array $parameters) : ModelContract
    {
        throw new NotImplementedException();
    }

    /**
     * Delte a record by its id in the repository
     * @throws QueryException
     * @return bool
     */
    public function delete($id) : bool
    {
        throw new NotImplementedException();
    }

}
