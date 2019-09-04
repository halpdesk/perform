<?php

namespace Halpdesk\Perform\Contracts;

use Illuminate\Support\Collection;

interface Query {

    /**
     * Add a basic where clause to the query
     * @throws QueryException
     */
    public function where($key, $value) : Query;

    /**
     * Add an "order by" clause to the query
     * @throws QueryException
     */
    public function orderBy() : Query;

    /**
     * Alias to set the "limit" value of the query
     * @throws QueryException
     */
    public function take() : Query;




    /**
     * Execute query for a single record by id
     * Return null if record with $id cannot be found
     */
    public function find($id) : ?Model;

    /**
     * Execute query for a single record by id
     * @throws ModelNotFoundException
     */
    public function findOrFail($id) : Model;

    /**
     * Execute the query and return a collection
     */
    public function get() : Collection;

    /**
     * Execute the query and get the first result
     * Return null if record with $id cannot be found
     */
    public function first() : ?Model;
    /**
     * Execute the query and get the first result
     * @throws ModelNotFoundException
     */
    public function firstOrFail() : Model;





    /**
     * Insert a record to the repository
     * @throws QueryException
     */
    public function create(array $parameters) : Model;

    /**
     * Update records in the repository
     * @throws QueryException
     */
    public function update(array $parameters) : Model;

    /**
     * Delte a record by its id in the repository
     * @throws QueryException
     */
    public function delete($id) : bool;

}
