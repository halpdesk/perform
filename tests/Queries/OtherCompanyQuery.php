<?php

namespace Halpdesk\Tests\Queries;

use Halpdesk\Perform\Abstracts\Query;
use Halpdesk\Perform\Contracts\Query as QueryContract;
use Halpdesk\Perform\Contracts\Model as ModelContract;
use Illuminate\Support\Collection;
use Halpdesk\Tests\Models\Company;

class OtherCompanyQuery extends CompanyQuery implements QueryContract
{
    protected $model = Company::class;

    public function load() : void
    {
        $otherCompanies = json_file_to_array('./tests/data/other-companies.json');
        $this->setData(collect($otherCompanies));
    }
}
