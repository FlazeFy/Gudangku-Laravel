<?php

namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use GuzzleHttp\Client;
use Tests\TestCase;

// Helper
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

    public function test_get_dashboard(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $response = $this->httpClient->get("dashboard", [
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

        $check_object = ['total_item','total_fav','total_low','last_added','most_category','highest_price'];
        foreach ($check_object as $dt) {
            $this->assertArrayHasKey($dt, $data['data']);
        }

        $check_not_null_int = ['total_item','total_fav','total_low'];
        foreach ($check_not_null_int as $dt){
            $this->assertIsInt($data['data'][$dt]);
            $this->assertGreaterThanOrEqual(0, $data['data'][$dt]);
        }

        $this->assertIsString($data['data']['last_added']);

        $check_not_null_object = ['most_category','highest_price'];
        foreach ($check_not_null_object as $dt) {
            $this->assertIsArray($data['data'][$dt]); // object as an array
        }

        $check_object_most_category = ['context','total'];
        foreach ($check_object_most_category as $dt) {
            $this->assertArrayHasKey($dt, $data['data']['most_category']);
        }
        $this->assertIsInt($data['data']['most_category']['total']);
        $this->assertIsString($data['data']['most_category']['context']);

        $check_object_highest_price = ['inventory_name','inventory_price'];
        foreach ($check_object_highest_price as $dt) {
            $this->assertArrayHasKey($dt, $data['data']['highest_price']);
        }
        $this->assertIsInt($data['data']['highest_price']['inventory_price']);
        $this->assertIsString($data['data']['highest_price']['inventory_name']);

        Audit::auditRecordText("Test - Get Dashboard", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Dashboard", "TC-XXX", 'TC-XXX test_get_dashboard', json_encode($data));
    }

    public function test_get_tree_map(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $response = $this->httpClient->get("inventory/tree_map", [
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
        $this->assertIsArray($data['data']);

        $validateNode = function ($dt) use (&$validateNode) {
            $this->assertArrayHasKey('id', $dt);
            $this->assertArrayHasKey('name', $dt);

            $this->assertIsString($dt['id']);
            $this->assertIsString($dt['name']);

            $idParts = explode('_', $dt['id']);
            $this->assertCount(2, $idParts);
            $this->assertEquals(32, strlen($idParts[1]));

            if (array_key_exists('children', $dt)) {
                $this->assertIsArray($dt['children']);

                foreach ($dt['children'] as $child) {
                    $validateNode($child);
                }
            }
        };

        // Check until the deepest
        foreach ($data['data'] as $rootNode) {
            $validateNode($rootNode);
        }

        Audit::auditRecordText("Test - Get Tree Map", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Tree Map", "TC-XXX", 'TC-XXX test_get_tree_map', json_encode($data));
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

    public function test_get_total_report_created_per_month(): void
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

        Audit::auditRecordText("Test - Get Total Report Created Per Month", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Total Report Created Per Month", "TC-XXX", 'TC-XXX test_get_total_report_created_per_month', json_encode($data));
    }

    public function test_get_total_inventory_created_per_month(): void
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

        Audit::auditRecordText("Test - Get Total Inventory Created Per Month", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Total Inventory Created Per Month", "TC-XXX", 'TC-XXX test_get_total_inventory_created_per_month', json_encode($data));
    }

    public function test_get_total_activity_per_month(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $response = $this->httpClient->get("history/total_activity_per_month/2024", [
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

        Audit::auditRecordText("Test - Get Total Activity Per Month", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Total Activity Per Month", "TC-XXX", 'TC-XXX test_get_total_activity_per_month', json_encode($data));
    }

    public function test_get_total_favorite_inventory_comparison(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $response = $this->httpClient->get("inventory/favorite_inventory_comparison", [
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
        $this->assertEquals(2,count($data['data']));

        foreach ($data['data'] as $dt) {
            $this->assertArrayHasKey('context', $dt);
            $this->assertArrayHasKey('total', $dt);

            $this->assertNotNull($dt['context']);
            $this->assertIsString($dt['context']);
    
            $this->assertNotNull($dt['total']);
            $this->assertIsInt($dt['total']);
            $this->assertGreaterThanOrEqual(0, $dt['total']);
        }

        Audit::auditRecordText("Test - Get Total Favorite Inventory Comparison", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Total Favorite Inventory Comparison", "TC-XXX", 'TC-XXX test_get_total_favorite_inventory_comparison', json_encode($data));
    }

    public function test_get_total_low_capacity_inventory_comparison(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $response = $this->httpClient->get("inventory/low_capacity_inventory_comparison", [
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
        $this->assertEquals(2,count($data['data']));

        foreach ($data['data'] as $dt) {
            $this->assertArrayHasKey('context', $dt);
            $this->assertArrayHasKey('total', $dt);

            $this->assertNotNull($dt['context']);
            $this->assertIsString($dt['context']);
    
            $this->assertNotNull($dt['total']);
            $this->assertIsInt($dt['total']);
            $this->assertGreaterThanOrEqual(0, $dt['total']);
        }

        Audit::auditRecordText("Test - Get Total Low Capacity Inventory Comparison", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Total Low Capacity Inventory Comparison", "TC-XXX", 'TC-XXX test_get_total_low_capacity_inventory_comparison', json_encode($data));
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
