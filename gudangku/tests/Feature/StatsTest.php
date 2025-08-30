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

    public function test_get_total_inventory_by_merk(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $response = $this->httpClient->get("inventory/total_by_merk/price", [
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

        Audit::auditRecordText("Test - Get Total Inventory By Merk", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Total Inventory By Merk", "TC-XXX", 'TC-XXX test_get_total_inventory_by_merk', json_encode($data));
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

    public function test_get_most_expensive_inventory_per_context(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $context = "inventory_category";
        $response = $this->httpClient->get("inventory/most_expensive/$context", [
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
            $this->assertArrayHasKey('inventory_price', $dt);

            $this->assertNotNull($dt['context']);
            $this->assertIsString($dt['context']);
    
            $this->assertNotNull($dt['inventory_price']);
            $this->assertIsInt($dt['inventory_price']);
            $this->assertGreaterThanOrEqual(0, $dt['inventory_price']);
        }

        Audit::auditRecordText("Test - Get Most Expensive Inventory Per Context", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Most Expensive Inventory Per Context", "TC-XXX", 'TC-XXX test_get_most_expensive_inventory_per_context', json_encode($data));
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

    public function test_get_total_inventory_created_at_month(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $response = $this->httpClient->get("inventory/total_created_per_month/2024", [
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
            $this->assertArrayHasKey('total', $dt);

            $this->assertNotNull($dt['context']);
            $this->assertIsString($dt['context']);
    
            $this->assertNotNull($dt['total']);
            $this->assertIsInt($dt['total']);
            $this->assertGreaterThanOrEqual(0, $dt['total']);
        }

        Audit::auditRecordText("Test - Get Total Inventory Created At Month", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Total Inventory Created At Month", "TC-XXX", 'TC-XXX test_get_total_inventory_created_at_month', json_encode($data));
    }

    public function test_get_last_login_user(): void
    {
        // Exec
        $token = $this->login_trait("admin");
        $response = $this->httpClient->get("user/last_login", [
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
            $string_col = ['username','login_at'];
            foreach ($string_col as $col) {
                $this->assertArrayHasKey($col, $dt);
                $this->assertNotNull($dt[$col]);
                $this->assertIsString($dt[$col]);
            }
        }

        Audit::auditRecordText("Test - Get Last Login User", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Last Login User", "TC-XXX", 'TC-XXX test_get_last_login_user', json_encode($data));
    }

    public function test_get_leaderboard(): void
    {
        // Exec
        $token = $this->login_trait("admin");
        $response = $this->httpClient->get("user/leaderboard", [
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

        $context = ['inventory','report'];
        foreach ($context as $ctx) {
            foreach ($data['data']['user_with_most_'.$ctx] as $dt) {
                $this->assertArrayHasKey('username', $dt);
                $this->assertArrayHasKey('total', $dt);

                $this->assertNotNull($dt['username']);
                $this->assertIsString($dt['username']);
        
                $this->assertNotNull($dt['total']);
                $this->assertIsInt($dt['total']);
                $this->assertGreaterThanOrEqual(0, $dt['total']);
            }
        }

        Audit::auditRecordText("Test - Get Leaderboard", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Leaderboard", "TC-XXX", 'TC-XXX test_get_leaderboard', json_encode($data));
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
