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

    // getValidateUser
    public function test_validate_user_create_success_with_valid_data()
    {
        $request = Request::create('/test', 'POST', [
            'username' => 'validuser',
            'password' => 'validpass',
            'email' => 'test@gmail.com'
        ]);

        $validator = Validation::getValidateUser($request,'create');

        $this->assertFalse($validator->fails());
        Audit::auditRecordText("Test - Validation User Create Success With Valid Data", "Validation-User Create", "Request : ".json_encode($request->all()));
        Audit::auditRecordSheet("Test - Validation User Create Success With Valid Data", "Validation-User Create", json_encode($request->all()),'');
    }
    public function test_validate_user_create_failed_with_missing_email()
    {
        $request = Request::create('/test', 'POST', [
            'username' => 'validuser',
            'password' => 'validpass',
        ]);

        $validator = Validation::getValidateUser($request,'create');

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
        Audit::auditRecordText("Test - Validation User Create Failed With Missing Email", "Validation-User Create", "Request : ".json_encode($request->all()));
        Audit::auditRecordSheet("Test - Validation User Create Failed With Missing Email", "Validation-User Create", json_encode($request->all()),'');
    }
    public function test_validate_user_create_failed_with_invalid_long_char_username()
    {
        $request = Request::create('/test', 'POST', [
            'username' => 'valid',
            'password' => 'validpass',
            'email' => 'test@gmail.com'
        ]);

        $validator = Validation::getValidateUser($request,'create');

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('username', $validator->errors()->toArray());
        Audit::auditRecordText("Test - Validation User Create Failed With Invalid Long Char Username", "Validation-User Create", "Request : ".json_encode($request->all()));
        Audit::auditRecordSheet("Test - Validation User Create Failed With Invalid Long Char Username", "Validation-User Create", json_encode($request->all()),'');
    }
    public function test_validate_user_update_success_with_valid_data()
    {
        $request = Request::create('/test', 'POST', [
            'username' => 'validuser',
            'email' => 'test@gmail.com'
        ]);

        $validator = Validation::getValidateUser($request,'update');

        $this->assertFalse($validator->fails());
        Audit::auditRecordText("Test - Validation User Update Success With Valid Data", "Validation-User Update", "Request : ".json_encode($request->all()));
        Audit::auditRecordSheet("Test - Validation User Update Success With Valid Data", "Validation-User Update", json_encode($request->all()),'');
    }
    public function test_validate_user_update_failed_with_invalid_long_char_email()
    {
        $request = Request::create('/test', 'POST', [
            'username' => 'validuser',
            'email' => 'gmail.com'
        ]);

        $validator = Validation::getValidateUser($request,'update');

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
        Audit::auditRecordText("Test - Validation User Update Failed With Invalid Long Char Email", "Validation-User Update", "Request : ".json_encode($request->all()));
        Audit::auditRecordSheet("Test - Validation User Update Failed With Invalid Long Char Email", "Validation-User Update", json_encode($request->all()),'');
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
        Audit::auditRecordText("Test - Validation Report Create Failed With Invalid Is Reminder", "Validation-Report Create", "Request : ".json_encode($request->all()));
        Audit::auditRecordSheet("Test - Validation Report Create Failed With Invalid Is Reminder", "Validation-Report Create", json_encode($request->all()),'');
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
        Audit::auditRecordText("Test - Validation Report Create Failed With Invalid Reminder At", "Validation-Report Create", "Request : ".json_encode($request->all()));
        Audit::auditRecordSheet("Test - Validation Report Create Failed With Invalid Reminder At", "Validation-Report Create", json_encode($request->all()),'');
    }
    public function test_validate_report_create_failed_with_invalid_rules_report_category()
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
        Audit::auditRecordText("Test - Validation Report Create Failed With Invalid Rules Report Category", "Validation-Report Create", "Request : ".json_encode($request->all()));
        Audit::auditRecordSheet("Test - Validation Report Create Failed With Invalid Rules Report Category", "Validation-Report Create", json_encode($request->all()),'');
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
    public function test_validate_report_update_success_with_valid_data()
    {
        $request = Request::create('/test', 'POST', [
            'report_title' => 'test',
            'report_desc' => 'test desc',
            'report_category' => 'Shopping Cart',
            'created_at' => '2025-07-17 14:00:00',
        ]);

        $validator = Validation::getValidateReport($request, 'update');

        $this->assertFalse($validator->fails());
        Audit::auditRecordText("Test - Validation Report Update Success With Valid Data", "Validation-Report Update", "Request : ".json_encode($request->all()));
        Audit::auditRecordSheet("Test - Validation Report Update Success With Valid Data", "Validation-Report Update", json_encode($request->all()),'');
    }
    public function test_validate_report_update_failed_with_invalid_created_at()
    {
        $request = Request::create('/test', 'POST', [
            'report_title' => 'test',
            'report_desc' => 'test desc',
            'report_category' => 'Shopping Cart',
            'reminder_at' => '2025-07-1-7 14:00:00',
        ]);

        $validator = Validation::getValidateReport($request, 'update');

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('created_at', $validator->errors()->toArray());
        Audit::auditRecordText("Test - Validation Report Update Failed With Invalid Created At", "Validation-Report Update", "Request : ".json_encode($request->all()));
        Audit::auditRecordSheet("Test - Validation Report Update Failed With Invalid Created At", "Validation-Report Update", json_encode($request->all()),'');
    }

    // getValidateReminder
    public function test_validate_reminder_create_success_with_valid_data()
    {
        $request = Request::create('/test', 'POST', [
            'inventory_id' => '01102511-7d68-2fdc-3dfe-ba98b7aff205',
            'reminder_desc' => 'test desc',
            'reminder_type' => 'Every Week',
            'reminder_context' => 'Every Day 1',
        ]);

        $validator = Validation::getValidateReminder($request, 'create');

        $this->assertFalse($validator->fails());
        Audit::auditRecordText("Test - Validation Reminder Create Success With Valid Data", "Validation-Reminder Create", "Request : ".json_encode($request->all()));
        Audit::auditRecordSheet("Test - Validation Reminder Create Success With Valid Data", "Validation-Reminder Create", json_encode($request->all()),'');
    }
    public function test_validate_reminder_create_failed_with_missing_reminder_desc()
    {
        $request = Request::create('/test', 'POST', [
            'inventory_id' => '01102511-7d68-2fdc-3dfe-ba98b7aff205',
            'reminder_type' => 'Every Week',
            'reminder_context' => 'Every Day 1',
        ]);

        $validator = Validation::getValidateReminder($request, 'create');

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('reminder_desc', $validator->errors()->toArray());
        Audit::auditRecordText("Test - Validation Reminder Create Failed With Missing Reminder Desc", "Validation-Reminder Create", "Request : ".json_encode($request->all()));
        Audit::auditRecordSheet("Test - Validation Reminder Create Failed With Missing Reminder Desc", "Validation-Reminder Create", json_encode($request->all()),'');
    }
    public function test_validate_reminder_create_failed_with_invalid_rules_reminder_type()
    {
        $request = Request::create('/test', 'POST', [
            'inventory_id' => '01102511-7d68-2fdc-3dfe-ba98b7aff205',
            'reminder_desc' => 'test desc',
            'reminder_type' => 'Every Test',
            'reminder_context' => 'Every Day 1',
        ]);

        $validator = Validation::getValidateReminder($request, 'create');

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('reminder_type', $validator->errors()->toArray());
        Audit::auditRecordText("Test - Validation Reminder Create Failed With Invalid Rules Reminder Type", "Validation-Reminder Create", "Request : ".json_encode($request->all()));
        Audit::auditRecordSheet("Test - Validation Reminder Create Failed With Invalid Rules Reminder Type", "Validation-Reminder Create", json_encode($request->all()),'');
    }
    public function test_validate_reminder_create_failed_with_invalid_long_char_inventory_id()
    {
        $request = Request::create('/test', 'POST', [
            'inventory_id' => '01102511-7d68-2fdc-3dfe-ba98b7aff2',
            'reminder_desc' => 'test desc',
            'reminder_type' => 'Every Test',
            'reminder_context' => 'Every Day 1',
        ]);

        $validator = Validation::getValidateReminder($request, 'create');

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('inventory_id', $validator->errors()->toArray());
        Audit::auditRecordText("Test - Validation Reminder Create Failed With Invalid Long Char Inventory Id", "Validation-Reminder Create", "Request : ".json_encode($request->all()));
        Audit::auditRecordSheet("Test - Validation Reminder Create Failed With Invalid Long Char Inventory Id", "Validation-Reminder Create", json_encode($request->all()),'');
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
