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
    public function test_validate_dictionary_create_success_with_valid_data()
    {
        $request = Request::create('/test', 'POST', [
            'dictionary_name' => 'test',
            'dictionary_type' => 'inventory_category'
        ]);

        $validator = Validation::getValidateDictionary($request,'create');

        $this->assertFalse($validator->fails());
        Audit::auditRecordText("Test - Validation Dictionary Create Success With Valid Data", "Validation-Dictionary Create", "Request : ".json_encode($request->all()));
        Audit::auditRecordSheet("Test - Validation Dictionary Create Success With Valid Data", "Validation-Dictionary Create", json_encode($request->all()),'');
    }
    public function test_validate_dictionary_create_failed_with_invalid_long_char_dictionary_name()
    {
        $request = Request::create('/test', 'POST', [
            'dictionary_name' => 't',
            'dictionary_type' => 'inventory_category'
        ]);

        $validator = Validation::getValidateDictionary($request,'create');

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('dictionary_name', $validator->errors()->toArray());
        Audit::auditRecordText("Test - Validation Dictionary Create Failed With Invalid Long Char Dictionary Name", "Validation-Dictionary Create", "Request : ".json_encode($request->all()));
        Audit::auditRecordSheet("Test - Validation Dictionary Create Failed With Invalid Long Char Dictionary Name", "Validation-Dictionary Create", json_encode($request->all()),'');
    }
    public function test_validate_dictionary_create_failed_with_invalid_rules_dictionary_type()
    {
        $request = Request::create('/test', 'POST', [
            'dictionary_name' => 'test',
            'dictionary_type' => 'created_at'
        ]);

        $validator = Validation::getValidateDictionary($request,'create');

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('dictionary_type', $validator->errors()->toArray());
        Audit::auditRecordText("Test - Validation Dictionary Create Failed With Invalid Rules Dictionary Type", "Validation-Dictionary Create", "Request : ".json_encode($request->all()));
        Audit::auditRecordSheet("Test - Validation Dictionary Create Failed With Invalid Rules Dictionary Type", "Validation-Dictionary Create", json_encode($request->all()),'');
    }
    public function test_validate_dictionary_delete_success_with_valid_data()
    {
        $request = Request::create('/test', 'POST', [
            'id' => 1,
        ]);

        $validator = Validation::getValidateDictionary($request,'delete');

        $this->assertFalse($validator->fails());
        Audit::auditRecordText("Test - Validation Dictionary Delete Success With Valid Data", "Validation-Dictionary Delete", "Request : ".json_encode($request->all()));
        Audit::auditRecordSheet("Test - Validation Dictionary Delete Success With Valid Data", "Validation-Dictionary Delete", json_encode($request->all()),'');
    }
    public function test_validate_dictionary_delete_failed_with_invalid_long_char_id()
    {
        $request = Request::create('/test', 'POST', [
            'id' => '1124',
        ]);

        $validator = Validation::getValidateDictionary($request,'delete');

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('id', $validator->errors()->toArray());
        Audit::auditRecordText("Test - Validation Dictionary Delete Failed With Long Char Id", "Validation-Dictionary Delete", "Request : ".json_encode($request->all()));
        Audit::auditRecordSheet("Test - Validation Dictionary Delete Failed With Long Char Id", "Validation-Dictionar Deletey", json_encode($request->all()),'');
    }

    // getValidateReport
    public function test_validate_report_create_success_with_valid_data()
    {
        $request = Request::create('/test', 'POST', [
            'report_title' => 'test',
            'report_desc' => 'test desc',
            'report_category' => 'Shopping Cart',
            'is_reminder' => 1,
            'reminder_at' => '2025-07-17 14:00:00',
            'report_item' => json_encode(['task' => 'Write code'])
        ]);

        $validator = Validation::getValidateReport($request, 'create');

        $this->assertFalse($validator->fails());
        Audit::auditRecordText("Test - Validation Report Create Success With Valid Data", "Validation-Report Create", "Request : ".json_encode($request->all()));
        Audit::auditRecordSheet("Test - Validation Report Create Success With Valid Data", "Validation-Report Create", json_encode($request->all()),'');
    }

    public function test_validate_report_create_failed_with_invalid_is_reminder()
    {
        $request = Request::create('/test', 'POST', [
            'report_title' => 'test',
            'report_desc' => 'test desc',
            'report_category' => 'Shopping Cart',
            'is_reminder' => 2,
            'reminder_at' => '2025-07-17 14:00:00',
            'report_item' => json_encode(['task' => 'Write code'])
        ]);

        $validator = Validation::getValidateReport($request, 'create');

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('is_reminder', $validator->errors()->toArray());
        Audit::auditRecordText("Test - Validation Report Create Failed With Invalid is_reminder", "Validation-Report Create", "Request : ".json_encode($request->all()));
        Audit::auditRecordSheet("Test - Validation Report Create Failed With Invalid is_reminder", "Validation-Report Create", json_encode($request->all()),'');
    }

    public function test_validate_report_create_failed_with_invalid_reminder_at()
    {
        $request = Request::create('/test', 'POST', [
            'report_title' => 'test',
            'report_desc' => 'test desc',
            'report_category' => 'Shopping Cart',
            'is_reminder' => 1,
            'reminder_at' => 'not-a-date',
            'report_item' => json_encode(['task' => 'Write code'])
        ]);

        $validator = Validation::getValidateReport($request, 'create');

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('reminder_at', $validator->errors()->toArray());
        Audit::auditRecordText("Test - Validation Report Create Failed With Invalid reminder_at", "Validation-Report Create", "Request : ".json_encode($request->all()));
        Audit::auditRecordSheet("Test - Validation Report Create Failed With Invalid reminder_at", "Validation-Report Create", json_encode($request->all()),'');
    }

    public function test_validate_report_create_failed_with_invalid_report_category()
    {
        $request = Request::create('/test', 'POST', [
            'report_title' => 'test',
            'report_desc' => 'test desc',
            'report_category' => 'main_room',
            'is_reminder' => 1,
            'reminder_at' => '2025-07-17 14:00:00',
            'report_item' => json_encode(['task' => 'Write code'])
        ]);

        $validator = Validation::getValidateReport($request, 'create');

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('report_category', $validator->errors()->toArray());
        Audit::auditRecordText("Test - Validation Report Create Failed With Invalid report_category", "Validation-Report Create", "Request : ".json_encode($request->all()));
        Audit::auditRecordSheet("Test - Validation Report Create Failed With Invalid report_category", "Validation-Report Create", json_encode($request->all()),'');
    }

    public function test_validate_report_create_failed_with_missing_report_title()
    {
        $request = Request::create('/test', 'POST', [
            'report_category' => 'Shopping Cart',
            'report_desc' => 'test desc',
            'is_reminder' => 1,
            'reminder_at' => '2025-07-17 14:00:00',
            'report_item' => json_encode(['task' => 'Write code'])
        ]);

        $validator = Validation::getValidateReport($request, 'create');

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('report_title', $validator->errors()->toArray());
        Audit::auditRecordText("Test - Validation Report Create Failed With Missing report_title", "Validation-Report Create", "Request : ".json_encode($request->all()));
        Audit::auditRecordSheet("Test - Validation Report Create Failed With Missing report_title", "Validation-Report Create", json_encode($request->all()),'');
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

    // getValidateTimezone
    public function test_validate_timezone_success_with_valid_data()
    {
        $time = "+07:00";
        $validator = Validation::getValidateTimezone($time);

        $this->assertEquals($validator,true);
        Audit::auditRecordText("Test - Validation Timezone Success With Valid Data", "Validation-Timezone", "Request : ".$time);
        Audit::auditRecordSheet("Test - Validation Timezone Success With Valid Data", "Validation-Timezone", $time,'');
    }
    public function test_validate_timezone_failed_with_invalid_data()
    {
        $time = "-15:00";
        $validator = Validation::getValidateTimezone($time);

        $this->assertEquals($validator,false);
        Audit::auditRecordText("Test - Validation Timezone Failed With Invalid Data", "Validation-Timezone", "Request : ".$time);
        Audit::auditRecordSheet("Test - Validation Timezone Failed With Invalid Data", "Validation-Timezone", $time,'');
    }
}
