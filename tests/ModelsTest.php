<?php

namespace Halpdesk\Tests;

use Halpdesk\Perform\Contracts\Model as ModelContract;
use Halpdesk\Perform\Abstracts\Model as Model;
use Halpdesk\Perform\Contracts\Query as QueryContract;
use Halpdesk\Perform\Abstracts\Query as Query;
use Halpdesk\Tests\Transformers\ProductsTransformer;
use Halpdesk\Tests\Transformers\TicketsTransformer;
use Halpdesk\Tests\Models\Employee;
use Halpdesk\Tests\Models\Company;
use Halpdesk\Tests\Queries\OtherCompanyQuery;
use PHPUnit\Framework\TestCase;
use Carbon\Carbon;
use Halpdesk\Perform\Exceptions\ModelNotFoundException;
use Halpdesk\Perform\Exceptions\RelationException;

/**
 * @author Daniel Leppänen
 */
class ModelsTest extends TestCase
{
    /**
     * @covers \Halpdesk\Perform\Abstracts\Model::__construct()
     * @covers \Halpdesk\Perform\Abstracts\Model::fill()
     */
    public function testConstructModel()
    {
        $employeeData = json_file_to_array(__DIR__."/data/employees.json")[0];
        $employee = new Employee($employeeData);
        $this->assertTrue($employee instanceof Employee);
        $this->assertTrue(in_array(ModelContract::class, class_implements($employee)));
        $this->assertTrue(in_array(Model::class, class_parents($employee)));
    }

    /**
     * @covers \Halpdesk\Perform\Abstracts\Model::__construct()
     * @covers \Halpdesk\Perform\Abstracts\Model::make()
     * @covers \Halpdesk\Perform\Abstracts\Model::fill()
     */
    public function testMakeModel()
    {
        // Array
        $employeeArray = json_file_to_array(__DIR__."/data/employees.json")[0];
        $employeeFromArray = Employee::make($employeeArray);
        $this->assertTrue($employeeFromArray instanceof Employee);
        $this->assertTrue(in_array(ModelContract::class, class_implements($employeeFromArray)));
        $this->assertTrue(in_array(Model::class, class_parents($employeeFromArray)));
        $this->assertEquals($employeeFromArray->name, $employeeArray["name"]);

        // Object
        $employeeObject = (object)json_file_to_array(__DIR__."/data/employees.json")[0];
        $this->assertEquals(gettype($employeeObject), 'object');
        $employeeFromObject = Employee::make($employeeObject);
        $this->assertTrue($employeeFromObject instanceof Employee);
        $this->assertTrue(in_array(ModelContract::class, class_implements($employeeFromObject)));
        $this->assertTrue(in_array(Model::class, class_parents($employeeFromObject)));
        $this->assertEquals($employeeFromObject->name, $employeeObject->name);
    }

    /**
     * @covers \Halpdesk\Perform\Abstracts\Model::toArray()
     * @covers \Halpdesk\Perform\Abstracts\Model::getAttribute()
     * @covers \Halpdesk\Perform\Abstracts\Model::setAttribute()
     * @covers \Halpdesk\Perform\Abstracts\Model::convert()
     * @covers \Halpdesk\Perform\Abstracts\Model::toArray()
     */
    public function testConstructEmptyModel()
    {
        $employee = new Employee;
        foreach ($employee->toArray() as $key => $value) {

            // but only if it is not an mutator or accessor
            if (
                !method_exists($employee, 'get'.ucfirst(camel_case($key)).'Attribute')
                && !method_exists($employee, 'set'.ucfirst(camel_case($key)).'Attribute')
            ) {
                $this->assertEquals($employee->$key, null);
            }
        }
    }

    /**
     * @covers \Halpdesk\Perform\Abstracts\Model::__get()
     * @covers \Halpdesk\Perform\Abstracts\Model::getAttribute()
     * @covers \Halpdesk\Perform\Abstracts\Model::toArray()
     * @covers \Halpdesk\Perform\Abstracts\Model::fill()
     * @covers \Halpdesk\Perform\Abstracts\Model::convert()
     */
    public function testCasts()
    {
        $employeeData = json_file_to_array(__DIR__."/data/employees.json")[0];
        $employee = new Employee($employeeData);
        $casts = $employee->getCasts();

        foreach ($employee->toArray() as $key => $value) {

            $keyValueType = get_orm_value_type($employee->$key);
            if (isset($casts[$key]) && !is_null($employee->$key)) {
                $this->assertEquals($keyValueType, $casts[$key]);
            }

            $valueType = get_orm_value_type($value);
            if (isset($casts[$key]) && !is_null($value)) {
                $this->assertEquals($valueType, $casts[$key]);
            }
        }
    }

    /**
     * @covers \Halpdesk\Perform\Abstracts\Model::__get()
     * @covers \Halpdesk\Perform\Abstracts\Model::getAttribute()
     * @covers \Halpdesk\Perform\Abstracts\Model::toArray()
     * @covers \Halpdesk\Perform\Abstracts\Model::fill()
     * @covers \Halpdesk\Perform\Abstracts\Model::getDates()
     * @covers \Halpdesk\Perform\Abstracts\Model::convert()
     */
    public function testDateFormat()
    {
        $employeeData = json_file_to_array(__DIR__."/data/employees.json")[0];
        $employee = new Employee($employeeData);
        $dates = $employee->getDates();
        $this->assertEquals(Carbon::parse($employeeData['hiredAt'])->format($employee->dateFormat), $employee->hiredAt);
    }

    /**
     * @covers \Halpdesk\Perform\Abstracts\Model::__get()
     * @covers \Halpdesk\Perform\Abstracts\Model::getAttribute()
     * @covers \Halpdesk\Perform\Abstracts\Model::convert()
     */
    public function testCustomAccessorMethod()
    {
        $companyData = json_file_to_array(__DIR__."/data/companies.json")[0];
        $company = new Company($companyData);
        $this->assertEquals(strtoupper($companyData["name"]), $company->name);
    }

    /**
     * @covers \Halpdesk\Perform\Abstracts\Model::__set()
     * @covers \Halpdesk\Perform\Abstracts\Model::setAttribute()
     * @covers \Halpdesk\Perform\Abstracts\Model::setVar()
     */
    public function testCustomMutatorMethod()
    {
        $employeeData = json_file_to_array(__DIR__."/data/employees.json")[0];
        $employee = new Employee($employeeData);
        $employee->salary = 99.5;
        $this->assertEquals(floor(99.5), $employee->salary);
    }

    /**
     * @covers \Halpdesk\Perform\Abstracts\Model::__construct()
     * @covers \Halpdesk\Perform\Abstracts\Model::fill()
     * @covers \Halpdesk\Perform\Abstracts\Query::get()
     * @covers \Halpdesk\Perform\Abstracts\Query::find()
     * @covers \Halpdesk\Perform\Abstracts\Query::findOrFail()
     * @covers \Halpdesk\Perform\Abstracts\Query::where()
     */
    public function testLoadRelation()
    {
        $employee = Employee::findOrFail(1);

        $employee->load(["company"]);
        $this->assertTrue($employee->relationLoaded("company"));

        $employeeCompany = $employee->company; // possible to call without function since it's loaded
        $expectedCompany = Company::findOrFail(1);
        $this->assertEquals($employeeCompany, $expectedCompany);

        $notExpectedCompany = Company::findOrFail(2);
        $this->assertNotEquals($employeeCompany, $notExpectedCompany);
    }

    /**
     * @covers \Halpdesk\Perform\Abstracts\Model::__construct()
     * @covers \Halpdesk\Perform\Abstracts\Model::fill()
     * @covers \Halpdesk\Perform\Abstracts\Query::get()
     * @covers \Halpdesk\Perform\Abstracts\Query::find()
     * @covers \Halpdesk\Perform\Abstracts\Query::findOrFail()
     * @covers \Halpdesk\Perform\Abstracts\Query::where()
     */
    public function testLoadOneToManyRelation()
    {
        $company = Company::findOrFail(1);

        $company->load(["employees"]);
        $this->assertTrue($company->relationLoaded("employees"));

        $companyEmployees = $company->employees; // possible to call without function since it's loaded
        $expectedEmployees = Employee::where("companyId", $company->id)->get();
        $this->assertEquals($companyEmployees, $expectedEmployees);
    }

    /**
     * @covers \Halpdesk\Perform\Abstracts\Model::__construct()
     * @covers \Halpdesk\Perform\Abstracts\Model::first()
     * @covers \Halpdesk\Perform\Abstracts\Model::fill()
     */
    public function testLoadRelationThatIsNeitherModelOrCollection()
    {
        $company = Company::findOrFail(1);
        $exceptionThrown = false;
        try {
            $company->load(['doesNotWorkRelation']);
        } catch (RelationException $e) {
            $this->assertEquals($e->getMessage(), 'relation_method_does_not_return_model_or_collection: doesNotWorkRelation');
            $exceptionThrown = true;
        }
        $this->assertTrue($exceptionThrown);
    }

    /**
     * @covers \Halpdesk\Perform\Abstracts\Model::all()
     * @covers \Halpdesk\Perform\Abstracts\Model::findOrFail()
     * @covers \Halpdesk\Perform\Abstracts\Query::get()
     * @covers \Halpdesk\Perform\Abstracts\Query::findOrFail()
     * @covers \Halpdesk\Perform\Abstracts\Query::find()
     */
    public function testFindOrFailThrowsExceptionIfModelNotFound()
    {
        $employees = Employee::all();
        $unexistingId = $employees->count() + 10;

        $exceptionThrown = false;
        try {

            Employee::findOrFail($unexistingId);
        } catch (ModelNotFoundException $e) {
            $this->assertEquals($e->getIds(), $unexistingId);
            $this->assertEquals($e->getModel(), Employee::class);
            $exceptionThrown = true;
        }
        $this->assertTrue($exceptionThrown);
    }

    /**
     * @covers \Halpdesk\Perform\Abstracts\Model::__construct()
     * @covers \Halpdesk\Perform\Abstracts\Model::setQueryClass()
     * @covers \Halpdesk\Perform\Abstracts\Query::get()
     */
    public function testConstructModelWithOtherQueryClass()
    {
        $companyQuery = Company::setQueryClass(OtherCompanyQuery::class)->where("id", 1);
        $this->assertTrue($companyQuery instanceof OtherCompanyQuery);
        $this->assertTrue(in_array(QueryContract::class, class_implements($companyQuery)));
        $this->assertTrue(in_array(Query::class, class_parents($companyQuery)));
        $companyData = json_file_to_array(__DIR__."/data/other-companies.json")[0];
        $this->assertEquals(new Company($companyData), $companyQuery->get()->first());
    }

    /**
     * @covers \Halpdesk\Perform\Abstracts\Model::all()
     * @covers \Halpdesk\Perform\Abstracts\Query::get()
     */
    public function testAll()
    {
            // Assert that each object can convert to array and display the same data as the data source
        $employees = Employee::all();
        $employeeData = json_file_to_array(__DIR__."/data/employees.json");

        foreach($employees as $i => $employee) {
            $this->assertEquals(new Employee($employeeData[$i]), $employee);
        }
    }

    /**
     * @covers \Halpdesk\Perform\Abstracts\Model::all()
     * @covers \Halpdesk\Perform\Abstracts\Query::get()
     */
    public function testWhere()
    {
        $employee = Employee::where("id", 2)->first();
        $employeeData = json_file_to_array(__DIR__."/data/employees.json");

        $key = array_search(2, array_column($employeeData, 'id'));

        $this->assertEquals(new Employee($employeeData[$key]), $employee);
    }

    /**
     * @covers \Halpdesk\Perform\Abstracts\Model::all()
     * @covers \Halpdesk\Perform\Abstracts\Query::get()
     */
    public function testChainWhere()
    {
        $foundEmployee = Employee::where("id", 2)->where("name", "Billy")->first();
        $this->assertTrue($foundEmployee instanceof Employee);
        $notFoundEmployee = Employee::where("id", 2)->where("name", "Charlie")->first();
        $this->assertTrue(empty($notFoundEmployee));
    }
}
