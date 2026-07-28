<?php

namespace Tests\Integration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use GuzzleHttp\Client;
use Tests\TestCase;

// Helper
use App\Helpers\Audit;
use App\Helpers\TestDataReader;

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

        // Pre-Condition: User already sign in
        $this->token = $this->login_trait("user");
        // Pre-Condition: At least a history exists
        $this->historyId = TestDataReader::getValue('history_id') ?? "";
    }

    public function test_get_all_history(): void
    {
        // Exec
        $response = $this->httpClient->get("", [
            'headers' => [
                'Authorization' => "Bearer ".$this->token
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
            $check_object = ['id','history_type','history_context','created_at','created_by'];
            foreach ($check_object as $col) {
                $this->assertArrayHasKey($col, $dt);
            }

            $check_not_null_string = ['id','history_type','history_context','created_at','created_by'];
            foreach ($check_not_null_string as $col) {
                $this->assertNotNull($dt[$col]);
                $this->assertIsString($dt[$col]);
            }

            $check_uuid = ['id','created_by'];
            foreach ($check_uuid as $col) {
                $this->assertEquals(36,strlen($dt[$col]));
            }
        }

        // Store founded history
        TestDataReader::setValue('history_id', $data['data']['data'][0]['id']);

        Audit::auditRecordText("Test - Get All History", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get All History", "TC-XXX", 'TC-XXX test_get_all_history', json_encode($data));
    }

    public function test_hard_delete_history_by_id(): void
    {
        // Exec
        $response = $this->httpClient->delete("destroy/".$this->historyId, [
            'headers' => [
                'Authorization' => "Bearer ".$this->token
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
