<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use GuzzleHttp\Client;
use Tests\TestCase;
use App\Helpers\Audit;

class InventoryTest extends TestCase
{
    protected $httpClient;
    use LoginHelperTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = new Client([
            'base_uri' => 'http://127.0.0.1:8000/api/v1/inventory/',
            'http_errors' => false
        ]);
    }

    public function test_get_inventory_list(): void
    {
        // Exec
        $token = $this->login_trait();
        $response = $this->httpClient->get("list", [
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

        foreach ($data['data'] as $dt) {
            $this->assertArrayHasKey('id', $dt);
            $this->assertArrayHasKey('inventory_name', $dt);
            $this->assertArrayHasKey('inventory_vol', $dt);
            $this->assertArrayHasKey('inventory_unit', $dt);

            $this->assertNotNull($dt['id']);
            $this->assertIsString($dt['id']);

            $this->assertNotNull($dt['inventory_name']);
            $this->assertIsString($dt['inventory_name']);

            $this->assertNotNull($dt['inventory_unit']);
            $this->assertIsString($dt['inventory_unit']);
    
            $this->assertNotNull($dt['inventory_vol']);
            $this->assertIsInt($dt['inventory_vol']);
            $this->assertGreaterThanOrEqual(0, $dt['inventory_vol']);
        }

        Audit::auditRecordText("Test - Get Inventory List", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Inventory List", "TC-XXX", 'TC-XXX test_get_inventory_list', json_encode($data));
    }

    public function test_get_inventory_by_room(): void
    {
        // Exec
        $token = $this->login_trait();
        $response = $this->httpClient->get("layout/Main%20Room", [
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

        foreach ($data['data'] as $dt) {
            $this->assertArrayHasKey('id', $dt);
            $this->assertArrayHasKey('inventory_storage', $dt);
            $this->assertArrayHasKey('layout', $dt);
            $this->assertArrayHasKey('storage_desc', $dt);

            $this->assertNotNull($dt['id']);
            $this->assertIsString($dt['id']);

            $this->assertNotNull($dt['inventory_storage']);
            $this->assertIsString($dt['inventory_storage']);

            $this->assertNotNull($dt['layout']);
            $this->assertIsString($dt['layout']);
    
            if (!is_null($dt['storage_desc'])) {
                $this->assertIsString($dt['storage_desc']);
            }
        }

        Audit::auditRecordText("Test - Get Inventory By Room", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Inventory By Room", "TC-XXX", 'TC-XXX test_get_inventory_by_room', json_encode($data));
    }
}
