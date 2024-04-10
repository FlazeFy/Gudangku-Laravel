<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Tests\TestCase;

// Helpers
use App\Helpers\Audit;
use App\Helpers\Generator;

class HelpersGeneratorTest extends TestCase
{ 
    protected $httpClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = new Client([
            'base_uri' => 'http://127.0.0.1:8000/',
            'http_errors' => false
        ]);
    }

    public function test_generator_get_uuid(): void
    {   
        $check_length = 36;

        // Exec
        $check = Generator::getUUID();

        // Test Parameter
        $this->assertIsString($check);
        $this->assertEquals($check_length, strlen($check));

        Audit::auditRecordText("Test - Generator Helper", "Generator-getUUID", "Result : ".$check);
        Audit::auditRecordSheet("Test - Generator Helper", "Generator-getUUID",'',$check);
    }

    public function test_generator_get_user_email(): void
    {   
        $check_length = 36;

        // Exec
        $response = $this->httpClient->post("/api/v1/login", [
            'json' => [
                'username' => 'flazefy',
                'password' => 'nopass123',
            ]
        ]);

        $data = json_decode($response->getBody(), true);

        // Test Parameter
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('id', $data['result']);

        $user_id = $data['result']['id'];

        // Exec
        $check = Generator::getUserEmail($user_id);

        // Test Parameter
        $this->assertIsString($check);

        Audit::auditRecordText("Test - Generator Helper", "Generator-getUserEmail", "Request : $user_id, Result: $check");
        Audit::auditRecordSheet("Test - Generator Helper", "Generator-getUserEmail", $user_id,$check);
    }
}
