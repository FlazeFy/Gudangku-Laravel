<?php

namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use GuzzleHttp\Client;
use Tests\TestCase;

// Helper
use App\Helpers\Audit;

class AuthTest extends TestCase
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

    public function test_post_login()
    {
        $body = [
            'username' => 'flazefy',
            'password' => 'nopass123'
        ];

        // Exec
        $response = $this->httpClient->post("/api/v1/login", [
            'json' => $body
        ]);
        $data = json_decode($response->getBody(), true);

        // Test Parameter
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('token', $data);
        $this->assertArrayHasKey('role', $data);
        $this->assertArrayHasKey('message', $data);

        $check_object = ['id','username','email','telegram_user_id','telegram_is_valid','firebase_fcm_token','line_user_id','phone','timezone','created_at','updated_at'];
        foreach ($check_object as $col) {
            $this->assertArrayHasKey($col, $data['message']);
        }

        $check_not_null_str = ['id','username','email','created_at'];
        foreach ($check_not_null_str as $col) {
            $this->assertNotNull($col, $data['message'][$col]);
            $this->assertIsString($col, $data['message'][$col]);
        }

        $check_nullable_str = ['telegram_user_id','firebase_fcm_token','line_user_id','phone','timezone','updated_at'];
        foreach ($check_nullable_str as $col) {
            if(!is_null($data['message'][$col])){
                $this->assertIsString($col, $data['message'][$col]);
            }
        }

        Audit::auditRecordText("Test - Post Login", "TC-001", "Token : ".$data['token']);
        Audit::auditRecordSheet("Test - Post Login", "TC-001", json_encode($body), $data['token']);
        
        return $data['token'];
    }

    public function test_post_sign_out(): void
    {
        // Exec
        $token = $this->test_post_login();
        $response = $this->httpClient->post("/api/v1/logout", [
            'headers' => [
                'Authorization' => "Bearer $token"
            ]
        ]);
        $data = json_decode($response->getBody(), true);

        // Test Parameter
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('success', $data['status']);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('logout success',$data['message']);

        Audit::auditRecordText("Test - Post Sign Out", "TC-002", "message : ".json_encode($data));
        Audit::auditRecordSheet("Test - Post Sign Out", "TC-002", 'TC-001 test_post_sign_out', json_encode($data));
    }

    public function test_post_register_validation_token(): void
    {
        $body = [
            'username' => 'tester@gmail.com',
            'email' => 'flazen.work@gmail.com'
        ];

        // Exec
        $response = $this->httpClient->post("/api/v1/register/token", [
            'json' => $body
        ]);

        $data = json_decode($response->getBody(), true);

        // Test Parameter
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('success', $data['status']);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals("the validation token has been sended to ".$body['email']." email account",$data['message']);

        Audit::auditRecordText("Test - Post Register Validation Token", "TC-XXX", "message : ".json_encode($data));
        Audit::auditRecordSheet("Test - Post Register Validation Token", "TC-XXX", 'TC-001 test_post_register_validation_token', json_encode($data));
    }

    public function test_post_validate_register_account(): void
    {
        $body = [
            'username' => 'tester@gmail.com',
            'email' => 'flazen.work@gmail.com',
            'token' => 'SO9KWH',
            'password' => 'nopass123'
        ];

        // Exec
        $response = $this->httpClient->post("/api/v1/register/account", [
            'json' => $body
        ]);

        $data = json_decode($response->getBody(), true);

        // Test Parameter
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('success', $data['status']);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('account is registered',$data['message']);

        Audit::auditRecordText("Test - Post Validate Register Account", "TC-XXX", "message : ".json_encode($data));
        Audit::auditRecordSheet("Test - Post Validate Register Account", "TC-XXX", 'TC-001 test_post_validate_register_account', json_encode($data));
    }

    public function test_post_regenerate_register_token(): void
    {
        $body = [
            'username' => 'tester@gmail.com',
            'email' => 'flazen.work@gmail.com',
        ];

        // Exec
        $response = $this->httpClient->post("/api/v1/register/regen_token", [
            'json' => $body
        ]);

        $data = json_decode($response->getBody(), true);

        // Test Parameter
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('success', $data['status']);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals("the validation token has been sended to ".$body['email']." email account",$data['message']);

        Audit::auditRecordText("Test - Post Regenerate Register Token", "TC-XXX", "message : ".json_encode($data));
        Audit::auditRecordSheet("Test - Post Regenerate Register Token", "TC-XXX", 'TC-001 test_post_regenerate_register_token', json_encode($data));
    }
}
