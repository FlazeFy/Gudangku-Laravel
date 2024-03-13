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
        $env = 'web';
        $response = $this->httpClient->post("/api/v1/login/$env", [
            'json' => [
                'username' => 'flazefy',
                'password' => 'nopass123',
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('token', $data);
        $this->assertArrayHasKey('role', $data);

        Audit::auditRecord("Test - Returned Data", "TC-001", "Token : ".$data['token']);
        return $data['token'];
    }

    // TC-002
    public function test_get_sign_out(): void
    {
        $env = 'web';
        $token = $this->test_post_login();
        $response = $this->httpClient->get("/api/v1/logout/$env", [
            'headers' => [
                'Authorization' => "Bearer $token"
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('message', $data);
    }
}
