<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use GuzzleHttp\Client;
use Tests\TestCase;
use App\Helpers\Audit;

class ApiTest extends TestCase
{
    /**
     * A basic feature test example.
     */
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
        $response = $this->httpClient->post("/api/v1/login", [
            'json' => [
                'username' => 'flazefy',
                'password' => 'nopass123',
            ]
        ]);

        $data = json_decode($response->getBody(), true);

        // Test Parameter
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('token', $data);
        $this->assertArrayHasKey('role', $data);

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

        Audit::auditRecordText("Test - Sign Out", "TC-002", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Sign Out", "TC-002", 'TC-001 test_post_login', json_encode($data));
    }
}
