<?php

namespace Halpdesk\Perform\Contracts;

use Illuminate\Support\Collection;
use Closure;

interface Model {

    /**
     * Set the query class
     */
    static public function setQueryClass(string $class) : Model;

    /**
     * Set the query class
     */
    static public function newCollection(array $data = []) : Collection;

    /**
     * Get all of the models from the repository
     */
    static public function all() : Collection;

    /**
     * Iterate through all items in the collection
     */
    static public function each(Closure $closure) : void;

    /**
     * Returns number of items in current collection
     */
    static public function count() : int;

    /**
     * Get the model corresonding to the $id from the repository
     */
    static public function find($id) : ?Model;

    /**
     * Get the model corresonding to the $id from the repository or throw error
     * @throws ModelNotFoundException
     */
    static public function findOrFail($id) : Model;

    /**
     * Get the first model from the repository
     */
    static public function first() : ?Model;

    /**
     * Get the first model from the repository or throw error
     * @throws ModelNotFoundException
     */
    static public function firstOrFail() : Model;

    /**
     * Begin querying a model a basic where clause
     */
    static public function where($key, $value) : Query;

    /**
     * Begin querying a model with eager loading
     */
    static public function with(array $relations) : Query;

    /**
     * Eager load relations on the model
     */
    public function load(array $relations) : void;

    /**
     * Checks wheter a relation is loaded or not
     */
    public function relationLoaded(string $relation) : bool;

    /**
     * Create a record in the repository
     */
    static public function create($parameters) : Model;

    /**
     * Create a model instance without creating a record in the repository
     */
    static public function make($parameters) : Model;

    /**
     * Set a given attribute on the model
     */
    public function setAttribute(string $key, $value) : void;

    /**
     * Get a given attribute on the model
     */
    public function getAttribute(string $key);

    /**
     * Get the dates array
     */
    public function getDates() : array;

    /**
     * Update the model in the repository
     */
    public function update(array $parameters) : Model;

    /**
     * Save the model in the repository
     */
    public function save() : bool;

    /**
     * Delete the model from the repository
     */
    public function delete() : bool;


    /**
     * Fill the model from an array with data
     */
    public function fill(array $data) : Model;

    /**
     * Convert the model instance to an array
     */
    public function toArray() : array;

    /**
     * Get the loaded relations for ths model
     */
    public function getLoadedRelations() : array;

    /**
     * Get the model casts
     */
    public function getCasts() : array;

}
