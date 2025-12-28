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

    // TC-001
    public function test_post_login()
    {
        // Exec
        $param = [
            'username' => 'richardkyle',
            'password' => 'nopass123'
        ];
        $response = $this->httpClient->post("/api/v1/login", [
            'json' => $param
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
        Audit::auditRecordSheet("Test - Post Login", "TC-001", json_encode($param), $data['token']);
        return $data['token'];
    }

    // TC-002
    public function test_get_sign_out(): void
    {
        // Exec
        $token = $this->test_post_login();
        $response = $this->httpClient->get("/api/v1/logout", [
            'headers' => [
                'Authorization' => "Bearer $token"
            ]
        ]);

        $data = json_decode($response->getBody(), true);

        // Test Parameter
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('message', $data);

        Audit::auditRecordText("Test - Sign Out", "TC-002", "message : ".json_encode($data));
        Audit::auditRecordSheet("Test - Sign Out", "TC-002", 'TC-001 test_post_login', json_encode($data));
    }
}
