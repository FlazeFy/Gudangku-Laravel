<?php

namespace Tests\Integration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use GuzzleHttp\Client;
use Tests\TestCase;

// Helper
use App\Helpers\Audit;
use App\Helpers\TestDataReader;

class ClosingTest extends TestCase
{
    protected $httpClient;
    protected $token;
    use LoginHelperTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = new Client([
            'base_uri' => 'http://127.0.0.1:8000/api/v1/',
            'http_errors' => false
        ]);

        // Pre-Condition: User already sign in
        $this->token = $this->login_trait("user");
        // Pre-Condition: At least an inventory exists
        $this->inventoryId = TestDataReader::getValue('inventory_id') ?? "";
    }

    public function test_hard_delete_inventory_by_id(): void
    {
        // Exec
        $response = $this->httpClient->delete("inventory/destroy/".$this->inventoryId, [
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
        $this->assertEquals('inventory permentally deleted',$data['message']);

        Audit::auditRecordText("Test - Hard Delete Inventory By ID", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Hard Delete Inventory By ID", "TC-XXX", 'TC-XXX test_hard_delete_inventory_by_id', json_encode($data));
    }

    public function test_post_sign_out(): void
    {
        // Exec
        $response = $this->httpClient->post("/api/v1/logout", [
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
        $this->assertEquals('logout success',$data['message']);

        Audit::auditRecordText("Test - Post Sign Out", "TC-002", "message : ".json_encode($data));
        Audit::auditRecordSheet("Test - Post Sign Out", "TC-002", 'TC-001 test_post_sign_out', json_encode($data));
    }

    public function test_hard_delete_user_by_id(): void
    {
        // Exec
        $token = $this->login_trait("admin");
        // Pre-Condition: At least an user exists
        $userId = TestDataReader::getValue('user_id') ?? "";
        $response = $this->httpClient->delete("destroy/".$userId, [
            'headers' => [
                'Authorization' => "Bearer ".$token
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        // Test Parameter
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('success', $data['status']);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('user deleted',$data['message']);

        Audit::auditRecordText("Test - Hard Delete User By Id", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Hard Delete User By Id", "TC-XXX", 'TC-XXX test_hard_delete_user_by_id', json_encode($data));
    }
}
