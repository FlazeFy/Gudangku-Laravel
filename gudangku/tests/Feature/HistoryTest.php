<?php

namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use GuzzleHttp\Client;
use Tests\TestCase;

// Helper
use App\Helpers\Audit;

class HistoryTest extends TestCase
{
    protected $httpClient;
    use LoginHelperTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = new Client([
            'base_uri' => 'http://127.0.0.1:8000/api/v1/history/',
            'http_errors' => false
        ]);
    }

    public function test_get_all_history(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $response = $this->httpClient->get("", [
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
        $this->assertArrayHasKey('data', $data);

        foreach ($data['data']['data'] as $dt) {
            $this->assertArrayHasKey('id', $dt);
            $this->assertArrayHasKey('history_type', $dt);
            $this->assertArrayHasKey('history_context', $dt);
            $this->assertArrayHasKey('created_at', $dt);
            $this->assertArrayHasKey('created_by', $dt);

            $this->assertNotNull($dt['id']);
            $this->assertIsString($dt['id']);
            $this->assertEquals(36,strlen($dt['id']));

            $this->assertNotNull($dt['history_type']);
            $this->assertIsString($dt['history_type']);

            $this->assertNotNull($dt['history_context']);
            $this->assertIsString($dt['history_context']);

            $this->assertNotNull($dt['created_at']);
            $this->assertIsString($dt['created_at']);

            $this->assertNotNull($dt['created_by']);
            $this->assertIsString($dt['created_by']);
            $this->assertEquals(36,strlen($dt['created_by']));
        }

        Audit::auditRecordText("Test - Get All History", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get All History", "TC-XXX", 'TC-XXX test_get_all_history', json_encode($data));
    }

    public function test_hard_delete_history_by_id(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $id = "69dc1f34-9d1d-674e-1d7b-1ccfc3880a0c";
        $response = $this->httpClient->delete("destroy/$id", [
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
        $this->assertEquals('history permentally deleted',$data['message']);

        Audit::auditRecordText("Test - Hard Delete History By Id", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Hard Delete History By Id", "TC-XXX", 'TC-XXX test_hard_delete_history_by_id', json_encode($data));
    }
}
