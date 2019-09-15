<?php

namespace Halpdesk\Tests\Queries;

use Halpdesk\Perform\Abstracts\Query;
use Halpdesk\Perform\Contracts\Query as QueryContract;
use Halpdesk\Perform\Contracts\Model as ModelContract;
use Illuminate\Support\Collection;
use Halpdesk\Tests\Models\Company;

class OtherCompanyQuery extends CompanyQuery implements QueryContract
{
    protected $collection;
    protected $model = Company::class;

    public function get() : Collection
    {
        if (empty($this->collection)) {
            $body = file_get_contents('./tests/data/other-companies.json');
            $rows = json_decode($body, true);
            $companies = [];
            foreach ($rows as $row) {
                $companies[] = new $this->model($row);
            }
            $this->collection = collect($companies);
        }
        return $this->collection;
    }
}
