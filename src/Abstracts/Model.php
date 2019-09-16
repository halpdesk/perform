<?php

namespace Halpdesk\Perform\Abstracts;

use Illuminate\Support\Collection;
use Halpdesk\Perform\Exceptions\ModelNotFoundException;
use Halpdesk\Perform\Exceptions\QueryException;
use Halpdesk\Perform\Exceptions\NotImplementedException;
use Halpdesk\Perform\Exceptions\InvalidTypeException;
use Halpdesk\Perform\Exceptions\AttributeNotDefinedException;
use Halpdesk\Perform\Exceptions\RelationException;
use Halpdesk\Perform\Exceptions\QueryClassException;
use Halpdesk\Perform\Contracts\Query as QueryContract;
use Halpdesk\Perform\Contracts\Model as ModelContract;
use Carbon\Carbon;

abstract class Model {

    public $dateFormat = 'Y-m-d\TH:i:s.000';
    static public $query;
    protected $model;
    protected $transformer = ArrayTransformer::class;
    protected $fields = [];
    protected $casts = [];
    protected $dates = ['created_at', 'updated_at'];
    private $relations = [];
    private $vars = [];

    /**
     *  Model constructor
     *  @param  Mixed   $parameters    Array or object which to fill the model fields
     */
    public function __construct($parameters = [], $class = null)
    {
        if (!is_array($parameters)) {
            $parameters = (array)$parameters;
        }
        if (!empty($class)) {
            static::setQueryClass($class);
        }
        $this->model = get_class($this);
        $this->fill($parameters);
    }

    /**
     * Magic method get
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Magic method set
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Magic method isset
     */
    public function __isset($key)
    {
        return isset($this->vars[$key]);
    }

    /**
     * Set the query class
     * @param string $class The class to set as Query Class
     * @return ModelContract
    */
    static public function setQueryClass(string $class) : ModelContract
    {
        if (class_exists($class) && in_array(QueryContract::class, class_implements($class))) {

        } else {
            throw new QueryClassException('query_class_does_not_exist_or_does_not_implement_query_contract: ' . $class);
        }
        static::$query = $class;

        return new static();
    }

    /**
     * Get all of the models from the repository
     * @return Collection
     */
    static public function all() : Collection
    {
        $query = new static::$query;
        $data = $query->get();
        $collection = $data->map(function($item, $key) {
            // If items returned from $query->get() already is a Model, just return it
            if ($item instanceof ModelContract) {
                return $item;
            } else {
                return (new static($item));
            }
        });
        return $collection;
    }

    /**
     * Get the first model from the repository
     * @return ModelContract|null
     */
    static public function first() : ?ModelContract
    {
        $query = new static::$query;
        $model = $query->first();
        return $model;
    }

    /**
     * Get the first model from the repository or throw exception
     * @throws ModelNotFoundException
     * @return ModelContract
     */
    static public function firstOrFail() : ModelContract
    {
        $query = new static::$query;
        $model = $query->firstOrFail();
        return $model;
    }

    /**
     * Get the model corresonding to the $id from the repository
     * @param   $id     The identification number of the object to find
     * @return ModelContract|null
     */
    static public function find($id) : ?ModelContract
    {
        $query = new static::$query;
        $model = $query->find($id);
        return $model;
    }

    /**
     * Get the model corresonding to the $id from the repository or throw exception
     * @param   $id     The identification number of the object to find
     * @throws ModelNotFoundException
     * @return ModelContract
     */
    static public function findOrFail($id) : ModelContract
    {
        $query = new static::$query;
        $model = $query->findOrFail($id);
        return $model;
    }

    /**
     * Begin querying a model a basic where clause
     * @return QueryContract
     */
    static public function where($key, $value) : QueryContract
    {
        $query = new static::$query;
        $result = $query->where($key, $value);
        return $result;
    }

    /**
     * Begin querying a model with eager loading
     * @param  Mixed    $relations  Array of relation methods
     * @return QueryContract
     */
    static public function with(array $relations) : QueryContract
    {
        throw new NotImplementedException();
    }

    /**
     * Eager load relations on the model
     * @param Array     $relations  Array of relation methods
     * @return void
     */
    public function load(array $relations)
    {
        foreach ($relations as $relation) {
            if (empty($this->relations[$relation]) && method_exists($this, $relation)) {
                $model = call_user_func([$this, $relation]);

                // Only include it if the return is a Model or Collection
                if ($model instanceof ModelContract || $model instanceof Collection) {
                    $this->relations[$relation] = $model;
                    $this->$relation = $model;
                } else {
                    throw new RelationException('relation_method_does_not_return_model_or_collection: '.$relation);
                }
            }
        }
    }

    /**
     *  Checks wheter a relation is loaded or not
     * @param String    $relation   The relation to check
     * @return bool
     */
    public function relationLoaded(string $relation) : bool
    {
        return isset($this->relations[$relation]);
    }

    /**
     * Create a record in the repository
     *  @param  Mixed   $parameters    Array or object which to fill the model fields
     */
    static public function create($parameters) : ModelContract
    {
        $query = new static::$query;
        $model = $query->create($parameters);
        return $model;
    }

    /**
     * Create a model instance without creating a record in the repository
     *  @param  Mixed   $parameters    Array or object which to fill the model fields
     *  @return Model
     */
    static public function make($parameters) : ModelContract
    {
        $model = new static($parameters);
        return $model;
    }

    /**
     * Set a field without checking mutator, cast or format
     * Supposed to be used specifically within mutator methods
     * or otherwise within the class itself, why it is protected
     *
     * @param string $key    Key in the fields array for the attribute
     * @param Mixed  $value  Value to set attribute to
     * @return void
     */
    protected function setField(string $key, $value) : void
    {
        $this->vars[$key] = $value;
    }

    /**
     * Set a given attribute on the model
     * @param string $key    Key in the fields array for the attribute
     * @param Mixed  $value  Value to set attribute to
     * @return void
     */
    public function setAttribute(string $key, $value) : void
    {
        $mutatorMethod = 'set'.ucfirst(camel_case($key)).'Attribute';

        // If we have a mutator
        if (method_exists($this, $mutatorMethod)) {
            call_user_func([$this, $mutatorMethod], $value);
            $value = $this->vars[$key]; // fetch the value to use down below
        }

        if (in_array($key, $this->fields) || method_exists($this, $key)) {

            // Convert it by casts
            $this->vars[$key] = $this->convert($key, $value);

            // Same but camel_case. Observe! Do not mindlessly put this as extra operands in the first if-statement
            // Remember the day when 6 programmers took 15 minutes (1.5hrs) to discuss the term "operand"
        } else if (in_array(camel_case($key), $this->fields) || method_exists($this, camel_case($key))) {

            // Convert it by casts
            $this->vars[camel_case($key)] = $this->convert(camel_case($key), $value);
        }

    }

    /**
     * Get a given attribute on the model, cast by the casts definition
     * @param string    $key   Key in the fields array for the attribute
     * @throws AttributeNotDefinedException
     * @return Mixed
     */
    public function getAttribute(string $key)
    {
        $accessorMethod = 'get'.ucfirst(camel_case($key)).'Attribute';

        if ((in_array($key, $this->fields) || method_exists($this, $key)) && array_key_exists($key, $this->vars)) {

            // If we have an accessor
            if (method_exists($this, $accessorMethod)) {
                $value = call_user_func([$this, $accessorMethod], $this->vars[$key]);
            }

            // Convert it by casts
            $value = $this->convert($key, $value ?? $this->vars[$key]);

            // Format if date
            if (in_array($key, $this->dates) && ($value instanceof Carbon)) {
                return $value->format($this->dateFormat);
            }

            return $value;
        } else {
            throw new AttributeNotDefinedException('attribute_not_found: '.$key);
        }
    }

    /**
     * Get the date formats array
     * @return Array    The $date array
     */
    public function getDates()
    {
        return $this->dates;
    }

    /**
     * Update the model in the repository
     * @param Array     $parameters     Parameters to update model fields with
     * @return ModelContract
     */
    public function update(array $parameters)
    {
        $query = new static::$query;
        $model = $query->update($parameters);
        return $model;
    }

    /**
     * Save the model in the repository
     * @return bool
     */
    public function save() : bool
    {
        throw new NotImplementedException();
    }

    /**
     * Delete the model from the repository
     * @return bool
     */
    public function delete() : bool
    {
        $query = new static::$query;
        $bool = $query->delete($this->id);
        return $bool;
    }

    /**
     * Fill the model from an array with data
     * @param Array $data   The data array to fill the attribute fields with
     */
    public function fill(array $parameters) : ModelContract
    {
        if (is_array($parameters)) {
            foreach ($parameters as $key => $value) {
                $this->setAttribute($key, $value);
            }
        }
        // Fill rest with empty values
        foreach ($this->fields as $key) {
            if (!isset($this->vars[$key])) {
                $this->setAttribute($key, null);
            }
        }
        return $this;
    }

    /**
     * Convert the model instance to an array
     * @return Array
     */
    public function toArray() : array
    {
        $result = [];
        if (is_array($this->fields)) {
            foreach ($this->fields as $key) {
                $result[$key] = $this->getAttribute($key);
            }
        }
        return $result;
    }

    /**
     * Get the loaded relations for ths model
     * (for Fractal parseIncludes method)
     * @return String
     */
    public function getIncludes() : Array
    {
        return array_keys($this->relations);
    }

    /**
     * Get the model casts
     * @return Array
     */
    public function getCasts() : array
    {
        return $this->casts;
    }

    /**
     * Private method to convert a model attribute to the type defined in casts array
     * @param string    $field
     * @throws InvalidTypeException
     * @return Mixed
     */
    private function convert($field, $value)
    {
        $validTypes = InvalidTypeException::getValidTypes();
        if (is_array($this->casts) && in_array($field, array_keys($this->casts)) && !is_null($value)) {

            $type = $this->casts[$field];

            if (in_array($type, $validTypes)) {
                switch ($type) {
                    case 'string':
                        $value = (string)$value;
                        break;
                    case 'integer':
                    case 'int':
                        $value = (int)$value;
                        break;
                    case 'float':
                    case 'double':
                    case 'real':
                        $value = (float)$value;
                        break;
                    case 'bool':
                    case 'boolean':
                        $value = (boolean)$value;
                        break;
                    case 'array':
                        $value = (array)$value;
                        break;
                    case 'unset':
                        $value = null;
                        break;
                    case 'object':
                        $value = (object)$value;
                        break;
                    case 'datetime':
                        $value = Carbon::parse($value);
                        break;
                    default:
                        $value = (string)$value;
                }
            } else {
                throw new InvalidTypeException($type);
            }
        }
        return $value;
    }

}
