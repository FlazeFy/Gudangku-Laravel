<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Tests\TestCase;

// Helpers
use App\Helpers\Audit;
use App\Helpers\Generator;
use App\Helpers\Validation;

class HelpersValidationTest extends TestCase
{
    // getValidateLogin
    public function test_validate_login_success_with_valid_data()
    {
        $request = Request::create('/test', 'POST', [
            'username' => 'validuser',
            'password' => 'validpass'
        ]);

        $validator = Validation::getValidateLogin($request);

        $this->assertFalse($validator->fails());
        Audit::auditRecordText("Test - Validation Login Success With Valid Data", "Validation-Login", "Request : ".json_encode($request->all()));
        Audit::auditRecordSheet("Test - Validation Login Success With Valid Data", "Validation-Login", json_encode($request->all()),'');
    }
    public function test_validate_login_failed_with_missing_username()
    {
        $request = Request::create('/test', 'POST', [
            'password' => 'validpass'
        ]);

        $validator = Validation::getValidateLogin($request);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('username', $validator->errors()->toArray());
        Audit::auditRecordText("Test - Validation Login Failed With Missing Username", "Validation-Login", "Request : ".json_encode($request->all()));
        Audit::auditRecordSheet("Test - Validation Login Failed With Missing Username", "Validation-Login", json_encode($request->all()),'');
    }
    public function test_validate_login_failed_with_invalid_long_char_password()
    {
        $request = Request::create('/test', 'POST', [
            'username' => 'validuser',
            'password' => '123'
        ]);

        $validator = Validation::getValidateLogin($request);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
        Audit::auditRecordText("Test - Validation Login Failed With Invalid Long Char Password", "Validation-Login", "Request : ".json_encode($request->all()));
        Audit::auditRecordSheet("Test - Validation Login Failed With Invalid Long Char Password", "Validation-Login", json_encode($request->all()),'');
    }

    // getValidateDictionary
    public function test_validate_dictionary_success_with_valid_data()
    {
        $request = Request::create('/test', 'POST', [
            'dictionary_name' => 'test',
            'dictionary_type' => 'inventory_category'
        ]);

        $validator = Validation::getValidateDictionary($request,'create');

        $this->assertFalse($validator->fails());
        Audit::auditRecordText("Test - Validation Dictionary Success With Valid Data", "Validation-Dictionary", "Request : ".json_encode($request->all()));
        Audit::auditRecordSheet("Test - Validation Dictionary Success With Valid Data", "Validation-Dictionary", json_encode($request->all()),'');
    }
    public function test_validate_dictionary_failed_with_invalid_long_char_dictionary_name()
    {
        $request = Request::create('/test', 'POST', [
            'dictionary_name' => 't',
            'dictionary_type' => 'inventory_category'
        ]);

        $validator = Validation::getValidateDictionary($request,'create');

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('dictionary_name', $validator->errors()->toArray());
        Audit::auditRecordText("Test - Validation Dictionary Failed With Invalid Long Char Dictionary Name", "Validation-Dictionary", "Request : ".json_encode($request->all()));
        Audit::auditRecordSheet("Test - Validation Dictionary Failed With Invalid Long Char Dictionary Name", "Validation-Dictionary", json_encode($request->all()),'');
    }
    public function test_validate_dictionary_failed_with_invalid_rules_dictionary_type()
    {
        $request = Request::create('/test', 'POST', [
            'dictionary_name' => 'test',
            'dictionary_type' => 'created_at'
        ]);

        $validator = Validation::getValidateDictionary($request,'create');

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('dictionary_type', $validator->errors()->toArray());
        Audit::auditRecordText("Test - Validation Dictionary Failed With Invalid Rules Dictionary Type", "Validation-Dictionary", "Request : ".json_encode($request->all()));
        Audit::auditRecordSheet("Test - Validation Dictionary Failed With Invalid Rules Dictionary Type", "Validation-Dictionary", json_encode($request->all()),'');
    }

    // getValidateUUID
    public function test_validate_uuid_success_with_valid_data()
    {
        $id = "550e8400-e29b-41d4-a716-446655440000";
        $validator = Validation::getValidateUUID($id);

        $this->assertEquals($validator,true);
        Audit::auditRecordText("Test - Validation UUID Success With Valid Data", "Validation-UUID", "Request : ".$id);
        Audit::auditRecordSheet("Test - Validation UUID Success With Valid Data", "Validation-UUID", $id,'');
    }
    public function test_validate_uuid_failed_with_invalid_data()
    {
        $id = "99a9f744abea401545x19cb-af545d00672c";
        $validator = Validation::getValidateUUID($id);

        $this->assertEquals($validator,false);
        Audit::auditRecordText("Test - Validation UUID Failed With Invalid Data", "Validation-UUID", "Request : ".$id);
        Audit::auditRecordSheet("Test - Validation UUID Failed With Invalid Data", "Validation-UUID", $id,'');
    }
}
