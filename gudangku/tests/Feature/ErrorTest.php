<?php

namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use GuzzleHttp\Client;
use Tests\TestCase;

// Helper
use App\Helpers\Audit;

class ErrorTest extends TestCase
{
    protected $httpClient;
    use LoginHelperTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = new Client([
            'base_uri' => 'http://127.0.0.1:8000/api/v1/error/',
            'http_errors' => false
        ]);
    }

    public function test_get_all_error(): void
    {
        // Exec
        $token = $this->login_trait("admin");
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
            $check_object = ['id','message','stack_trace','file','line','is_fixed','created_at','faced_by'];
            foreach ($check_object as $col) {
                $this->assertArrayHasKey($col, $dt);
            }

            $check_not_null_str = ['message','stack_trace','file','created_at'];
            foreach ($check_not_null_str as $col) {
                $this->assertNotNull($dt[$col]);
                $this->assertIsString($dt[$col]);
            }

            if (!is_null($dt['faced_by'])) {
                $this->assertIsString($dt['faced_by']);
                $this->assertEquals(36,strlen($dt['faced_by']));
            }

            $check_not_null_int = ['id','line','is_fixed'];
            foreach ($check_not_null_int as $col) {
                $this->assertNotNull($dt[$col]);
                $this->assertIsInt($dt[$col]);
                $this->assertGreaterThanOrEqual(0, $dt[$col]);
            }

            $this->assertContains($dt['is_fixed'], [0, 1]);
        }

        Audit::auditRecordText("Test - Get All Error", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get All Error", "TC-XXX", 'TC-XXX test_get_all_error', json_encode($data));
    }

    public function test_hard_delete_error_by_id(): void
    {
        // Exec
        $token = $this->login_trait("admin");
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
        $this->assertEquals('error permentally deleted',$data['message']);

        Audit::auditRecordText("Test - Hard Delete Error By Id", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Hard Delete Error By Id", "TC-XXX", 'TC-XXX test_hard_delete_error_by_id', json_encode($data));
    }
}
