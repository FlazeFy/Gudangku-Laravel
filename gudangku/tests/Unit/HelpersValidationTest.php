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
    public function test_validation_login_request(): void
    {
        $check = new Request([
            'username' => 'testuser',
            'password' => 'password123'
        ]);
    
        // Exec
        $validator = Validation::getValidateLogin($check);
    
        Audit::auditRecordText("Test - Validation Helper", "Validation-Login", "Request : ".json_encode($check->all()));
        Audit::auditRecordSheet("Test - Validation Helper", "Validation-Login", json_encode($check->all()),'');
        $this->assertTrue($validator->passes());
    }
}
