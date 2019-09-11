<?php

namespace Halpdesk\Tests\Models;

use Halpdesk\Perform\Abstracts\Model;
use Halpdesk\Perform\Contracts\Model as ModelContract;
use Halpdesk\Tests\Queries\EmployeeQuery;
use Carbon\Carbon;

class Employee extends Model implements ModelContract
{
    static public $query = EmployeeQuery::class;
    protected $fields = [
        'id',
        'companyId',
        'name',
        'hiredAt',
        'hiredAtString',
        'hired',
        'salary',
    ];

    protected $casts = [
        'id'            => 'integer',
        'companyId'     => 'integer',
        'name'          => 'string',
        'hiredAt'       => 'datetime',
        'hired'         => 'boolean',
        'salary'        => 'float',
    ];

    protected $dates = [
        'hiredAt',
    ];

    public function company()
    {
        return Company::find($this->companyId);
    }

    public function setHiredAtAttribute($value = 'now')
    {
        return Carbon::parse($value);
    }

    public function setSalaryAttribute($value)
    {
        $this->setVar("salary", floor($value));
    }
}
