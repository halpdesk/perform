<?php

namespace Halpdesk\Tests;

use Halpdesk\Perform\Contracts\Model as ModelContract;
use Halpdesk\Perform\Abstracts\Model as Model;
use Halpdesk\Tests\Transformers\ProductsTransformer;
use Halpdesk\Tests\Transformers\TicketsTransformer;
use Halpdesk\Tests\Models\Employee;
use Halpdesk\Tests\Models\Company;
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
}
