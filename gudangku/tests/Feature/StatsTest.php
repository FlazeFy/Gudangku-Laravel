<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use GuzzleHttp\Client;
use Tests\TestCase;
use App\Helpers\Audit;

class StatsTest extends TestCase
{
    protected $httpClient;
    use LoginHelperTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = new Client([
            'base_uri' => 'http://127.0.0.1:8000/',
            'http_errors' => false
        ]);
    }

    public function test_get_total_inventory_by_category(): void
    {
        // Exec
        $token = $this->login_trait();
        $response = $this->httpClient->get("/api/v1/stats/inventory/total_by_category/price", [
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
            $this->assertArrayHasKey('context', $dt);
            $this->assertArrayHasKey('total', $dt);

            $this->assertNotNull($dt['context']);
            $this->assertIsString($dt['context']);
    
            $this->assertNotNull($dt['total']);
            $this->assertIsInt($dt['total']);
        }

        Audit::auditRecordText("Test - Get Total Inventory By Category", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Total Inventory By Category", "TC-XXX", 'TC-XXX test_get_total_inventory_by_category', json_encode($data));
    }

    public function test_get_total_inventory_by_room(): void
    {
        // Exec
        $token = $this->login_trait();
        $response = $this->httpClient->get("/api/v1/stats/inventory/total_by_room/price", [
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
            $this->assertArrayHasKey('context', $dt);
            $this->assertArrayHasKey('total', $dt);

            $this->assertNotNull($dt['context']);
            $this->assertIsString($dt['context']);
    
            $this->assertNotNull($dt['total']);
            $this->assertIsInt($dt['total']);
        }

        Audit::auditRecordText("Test - Get Total Inventory By Room", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Total Inventory By Room", "TC-XXX", 'TC-XXX test_get_total_inventory_by_room', json_encode($data));
    }

    public function test_get_total_inventory_by_favorite(): void
    {
        // Exec
        $token = $this->login_trait();
        $response = $this->httpClient->get("/api/v1/stats/inventory/total_by_favorite/price", [
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
            $this->assertArrayHasKey('context', $dt);
            $this->assertArrayHasKey('total', $dt);

            $this->assertNotNull($dt['context']);
            $this->assertIsString($dt['context']);
    
            $this->assertNotNull($dt['total']);
            $this->assertIsInt($dt['total']);
        }

        Audit::auditRecordText("Test - Get Total Inventory By Favorite", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Total Inventory By Favorite", "TC-XXX", 'TC-XXX test_get_total_inventory_by_favorite', json_encode($data));
    }
}
