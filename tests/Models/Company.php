<?php

namespace Halpdesk\Tests\Models;

use Halpdesk\Perform\Abstracts\Model;
use Halpdesk\Perform\Contracts\Model as ModelContract;
use Halpdesk\Tests\Queries\CompanyQuery;
use Illuminate\Support\Collection;

class Company extends Model implements ModelContract
{
    static public $query = CompanyQuery::class;
    protected $fields = [
        'id',
        'name',
    ];
    protected $casts = [
        'id'   => 'integer',
        'name' => 'string',
    ];

    public function employees()
    {
        return Employee::where('productId', $this->id)->get();
    }

    public function doesNotWorkRelation()
    {
        return 'it-does-not-return-a-model-or-collection';
    }
}
