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
            'base_uri' => 'http://127.0.0.1:8000/api/v1/stats/',
            'http_errors' => false
        ]);
    }

    public function test_get_total_inventory_by_category(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $response = $this->httpClient->get("inventory/total_by_category/price", [
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
            $this->assertGreaterThanOrEqual(0, $dt['total']);
        }

        Audit::auditRecordText("Test - Get Total Inventory By Category", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Total Inventory By Category", "TC-XXX", 'TC-XXX test_get_total_inventory_by_category', json_encode($data));
    }

    public function test_get_total_inventory_by_room(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $response = $this->httpClient->get("inventory/total_by_room/price", [
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
            $this->assertGreaterThanOrEqual(0, $dt['total']);
        }

        Audit::auditRecordText("Test - Get Total Inventory By Room", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Total Inventory By Room", "TC-XXX", 'TC-XXX test_get_total_inventory_by_room', json_encode($data));
    }

    public function test_get_total_inventory_by_favorite(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $response = $this->httpClient->get("inventory/total_by_favorite/price", [
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
            $this->assertGreaterThanOrEqual(0, $dt['total']);
        }

        Audit::auditRecordText("Test - Get Total Inventory By Favorite", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Total Inventory By Favorite", "TC-XXX", 'TC-XXX test_get_total_inventory_by_favorite', json_encode($data));
    }

    public function test_get_total_report_created_at_month(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $response = $this->httpClient->get("report/total_created_per_month/2024", [
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
        $this->assertEquals(12,count($data['data']));

        foreach ($data['data'] as $dt) {
            $this->assertArrayHasKey('context', $dt);
            $this->assertArrayHasKey('total_report', $dt);
            $this->assertArrayHasKey('total_item', $dt);

            $this->assertNotNull($dt['context']);
            $this->assertIsString($dt['context']);
    
            $this->assertNotNull($dt['total_item']);
            $this->assertIsInt($dt['total_item']);
            $this->assertGreaterThanOrEqual(0, $dt['total_item']);

            $this->assertNotNull($dt['total_report']);
            $this->assertIsInt($dt['total_report']);
            $this->assertGreaterThanOrEqual(0, $dt['total_report']);
        }

        Audit::auditRecordText("Test - Get Total Report Created At Month", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Total Report Created At Month", "TC-XXX", 'TC-XXX test_get_total_report_created_at_month', json_encode($data));
    }

    public function test_get_total_report_spending_by_month(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $response = $this->httpClient->get("report/total_spending_per_month/2024", [
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
        $this->assertEquals(12,count($data['data']));

        foreach ($data['data'] as $dt) {
            $this->assertArrayHasKey('context', $dt);
            $this->assertArrayHasKey('total_price', $dt);
            $this->assertArrayHasKey('total_item', $dt);
            $this->assertArrayHasKey('average_price_per_item', $dt);

            $this->assertNotNull($dt['context']);
            $this->assertIsString($dt['context']);
    
            $this->assertNotNull($dt['total_item']);
            $this->assertIsInt($dt['total_item']);
            $this->assertGreaterThanOrEqual(0, $dt['total_item']);

            $this->assertNotNull($dt['total_price']);
            $this->assertIsInt($dt['total_price']);
            $this->assertGreaterThanOrEqual(0, $dt['total_price']);

            $this->assertNotNull($dt['average_price_per_item']);
            $this->assertThat(
                $dt['average_price_per_item'],
                $this->logicalOr(
                    $this->isType('float'),
                    $this->isType('int'),
                    $this->equalTo(0)
                )
            );          
            $this->assertGreaterThanOrEqual(0, $dt['average_price_per_item']);
        }

        Audit::auditRecordText("Test - Get Total Report Spending By Month", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Total Report Spending By Month", "TC-XXX", 'TC-XXX test_get_total_report_spending_by_month', json_encode($data));
    }

    public function test_get_total_report_used_by_month(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $response = $this->httpClient->get("report/total_used_per_month/2024", [
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
        $this->assertEquals(12,count($data['data']));

        foreach ($data['data'] as $dt) {
            $this->assertArrayHasKey('context', $dt);
            $this->assertArrayHasKey('total_checkout', $dt);
            $this->assertArrayHasKey('total_washlist', $dt);

            $this->assertNotNull($dt['context']);
            $this->assertIsString($dt['context']);
    
            $this->assertNotNull($dt['total_checkout']);
            $this->assertIsInt($dt['total_checkout']);
            $this->assertGreaterThanOrEqual(0, $dt['total_checkout']);

            $this->assertNotNull($dt['total_washlist']);
            $this->assertIsInt($dt['total_washlist']);
            $this->assertGreaterThanOrEqual(0, $dt['total_washlist']);
        }

        Audit::auditRecordText("Test - Get Total Report Checkout By Month", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Total Report Checkout By Month", "TC-XXX", 'TC-XXX test_get_total_report_checkout_by_month', json_encode($data));
    }
}
