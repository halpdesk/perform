<?php

namespace Halpdesk\Perform\Abstracts;

use Illuminate\Support\Collection;
use Halpdesk\Perform\Exceptions\ModelNotFoundException;
use Halpdesk\Perform\Exceptions\QueryException;
use Halpdesk\Perform\Exceptions\NotImplementedException;
use Halpdesk\Perform\Contracts\Query as QueryContract;
use Halpdesk\Perform\Contracts\Model as ModelContract;
use Closure;

abstract class Query
{
    /**
     * Variable used for internal store
     * @var Collection $data
     */
    protected $data;
    protected $model;

    /**
     * Method to return a clone of query with current collection
     * @return QueryContract
     */
    private function newQuery($forceLoad = false) : QueryContract
    {
        $query = clone $this;
        if (empty($query->data) || $forceLoad) {
            $query->load();
        }
        return $query;
    }

    /**
     * Method to set data
     * @return void
     */
    public function setData(Collection $data) : void
    {
        $items = [];
        foreach ($data->toArray() as $row) {
            if (is_array($row)) {
                $items[] = (new $this->model)->fill($row);
            } else if ($row instanceof $this->model) {
                $items[] = $row;
            } else if (empty($row)) {
                $items[] = null;
            }
        }
        $this->data = collect($items);
    }

    /**
     * The method which to load / fetch data from repository source
     * @return void
     */
    public function load() : void
    {
        $this->setData(collect([]));
    }

    /**
     * Method to get a complete new query with loaded data from source
     * @return void
     */
    public function fresh() : QueryContract
    {
        $query = $this->newQuery(true); // force load
        return $query;
    }

    /**
     * Add a basic where clause to the query
     * @throws QueryException
     * @return QueryContract
     */
    public function where($key, $value) : QueryContract
    {
        $query = $this->newQuery();
        $query->data = $query->get()->where($key, $value);
        return $query;
    }

    /**
     * Execute the query and return a collection
     * @return Collection
     */
    public function get() : Collection
    {
        $query = $this->newQuery();
        return $query->data;
    }

    /**
     * Iterate through all items in the collection
     * @return void
     */
    public function each(Closure $closure) : void
    {
        $query = $this->newQuery();
        $query->data->each(function($item, $key) {
            call_user_func($closure, $item, $key);
        });
    }

    /**
     * Execute query for a single record by id
     * Return null if record with $id cannot be found
     * @return ModelContract|null
     */
    public function find($id) : ?ModelContract
    {
        $query = $this->newQuery();
        return $query->data->where('id', $id)->first();
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
     * Execute the query and get the first result
     * Return null if record with $id cannot be found
     * @return ModelContract|null
     */
    public function first() : ?ModelContract
    {
        $query = $this->newQuery();
        return $query->get()->first();
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
