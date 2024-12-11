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
    protected $room;
    protected $storage;
    use LoginHelperTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = new Client([
            'base_uri' => 'http://127.0.0.1:8000/api/v1/inventory/',
            'http_errors' => false
        ]);
        $this->room = "Main%20Room";
        $this->storage = "Main%20Table";
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
            $this->assertEquals(36,strlen($dt['id']));

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
        $response = $this->httpClient->get("layout/$this->room", [
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
            $this->assertEquals(36,strlen($dt['id']));

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

    public function test_get_inventory_by_storage(): void
    {
        // Exec
        $token = $this->login_trait();
        $response = $this->httpClient->get("search/by_room_storage/$this->room/$this->storage", [
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
            $this->assertArrayHasKey('inventory_category', $dt);
            $this->assertArrayHasKey('inventory_price', $dt);

            $this->assertNotNull($dt['id']);
            $this->assertIsString($dt['id']);
            $this->assertEquals(36,strlen($dt['id']));

            $this->assertNotNull($dt['inventory_name']);
            $this->assertIsString($dt['inventory_name']);

            $this->assertNotNull($dt['inventory_unit']);
            $this->assertIsString($dt['inventory_unit']);
    
            $this->assertNotNull($dt['inventory_vol']);
            $this->assertIsInt($dt['inventory_vol']);
            $this->assertGreaterThanOrEqual(0, $dt['inventory_vol']);

            $this->assertNotNull($dt['inventory_category']);
            $this->assertIsString($dt['inventory_category']);

            $this->assertNotNull($dt['inventory_price']);
            $this->assertIsInt($dt['inventory_price']);
            $this->assertGreaterThanOrEqual(0, $dt['inventory_price']);
        }

        Audit::auditRecordText("Test - Get Inventory By Storage", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Inventory By Storage", "TC-XXX", 'TC-XXX test_get_inventory_by_storage', json_encode($data));
    }

    public function test_get_inventory_calendar(): void
    {
        // Exec
        $token = $this->login_trait();
        $response = $this->httpClient->get("calendar", [
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
            $this->assertArrayHasKey('inventory_name', $dt);
            $this->assertArrayHasKey('inventory_price', $dt);

            $this->assertNotNull($dt['inventory_name']);
            $this->assertIsString($dt['inventory_name']);

            $this->assertNotNull($dt['inventory_price']);
            $this->assertIsInt($dt['inventory_price']);
            $this->assertGreaterThanOrEqual(0, $dt['inventory_price']);

            $this->assertNotNull($dt['created_at']);
            $this->assertIsString($dt['created_at']);
        }

        Audit::auditRecordText("Test - Get Inventory Calendar", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Inventory Calendar", "TC-XXX", 'TC-XXX test_get_inventory_by_storage', json_encode($data));
    }

    public function test_get_all_inventory(): void
    {
        // Exec
        $token = $this->login_trait();
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
            $this->assertArrayHasKey('inventory_name', $dt);
            $this->assertArrayHasKey('inventory_category', $dt);
            $this->assertArrayHasKey('inventory_desc', $dt);
            $this->assertArrayHasKey('inventory_merk', $dt);
            $this->assertArrayHasKey('inventory_room', $dt);
            $this->assertArrayHasKey('inventory_storage', $dt);
            $this->assertArrayHasKey('inventory_rack', $dt);
            $this->assertArrayHasKey('inventory_price', $dt);
            $this->assertArrayHasKey('inventory_image', $dt);
            $this->assertArrayHasKey('inventory_unit', $dt);
            $this->assertArrayHasKey('inventory_vol', $dt);
            $this->assertArrayHasKey('inventory_capacity_unit', $dt);
            $this->assertArrayHasKey('inventory_capacity_vol', $dt);
            $this->assertArrayHasKey('inventory_color', $dt);
            $this->assertArrayHasKey('is_favorite', $dt);
            $this->assertArrayHasKey('is_reminder', $dt);
            $this->assertArrayHasKey('created_at', $dt);
            $this->assertArrayHasKey('created_by', $dt);
            $this->assertArrayHasKey('updated_at', $dt);
            $this->assertArrayHasKey('deleted_at', $dt);
            $this->assertArrayHasKey('reminder', $dt);

            $this->assertNotNull($dt['id']);
            $this->assertIsString($dt['id']);
            $this->assertEquals(36,strlen($dt['id']));

            $this->assertNotNull($dt['inventory_name']);
            $this->assertIsString($dt['inventory_name']);

            $this->assertNotNull($dt['inventory_category']);
            $this->assertIsString($dt['inventory_category']);

            if (!is_null($dt['inventory_desc'])) {
                $this->assertIsString($dt['inventory_desc']);
            }

            if (!is_null($dt['inventory_merk'])) {
                $this->assertIsString($dt['inventory_merk']);
            }

            $this->assertNotNull($dt['inventory_room']);
            $this->assertIsString($dt['inventory_room']);


            if (!is_null($dt['inventory_storage'])) {
                $this->assertIsString($dt['inventory_storage']);
            }

            if (!is_null($dt['inventory_rack'])) {
                $this->assertIsString($dt['inventory_rack']);
            }

            $this->assertNotNull($dt['inventory_price']);
            $this->assertIsInt($dt['inventory_price']);
            $this->assertGreaterThanOrEqual(0, $dt['inventory_price']);


            if (!is_null($dt['inventory_image'])) {
                $this->assertIsString($dt['inventory_image']);
            }

            $this->assertNotNull($dt['inventory_unit']);
            $this->assertIsString($dt['inventory_unit']);
    
            $this->assertNotNull($dt['inventory_vol']);
            $this->assertIsInt($dt['inventory_vol']);
            $this->assertGreaterThanOrEqual(0, $dt['inventory_vol']);

            if (!is_null($dt['inventory_capacity_unit'])) {
                $this->assertIsString($dt['inventory_capacity_unit']);
            }

            if (!is_null($dt['inventory_capacity_vol'])) {
                $this->assertIsInt($dt['inventory_capacity_vol']);
                $this->assertGreaterThanOrEqual(0, $dt['inventory_capacity_vol']);
            }

            if (!is_null($dt['inventory_color'])) {
                $this->assertIsString($dt['inventory_color']);
            }

            $this->assertNotNull($dt['is_favorite']);
            $this->assertIsInt($dt['is_favorite']);
            $this->assertContains($dt['is_favorite'], [0, 1]);

            $this->assertNotNull($dt['is_reminder']);
            $this->assertIsInt($dt['is_reminder']);
            $this->assertContains($dt['is_reminder'], [0, 1]);

            $this->assertNotNull($dt['created_at']);
            $this->assertIsString($dt['created_at']);

            $this->assertNotNull($dt['created_by']);
            $this->assertIsString($dt['created_by']);
            $this->assertEquals(36,strlen($dt['created_by']));

            if (!is_null($dt['updated_at'])) {
                $this->assertIsString($dt['updated_at']);
            }

            if (!is_null($dt['deleted_at'])) {
                $this->assertIsString($dt['deleted_at']);
            }

            if (!is_null($dt['reminder'])) {
                foreach ($dt['reminder'] as $rmd) {
                    $this->assertIsString($rmd['id']);
                    $this->assertEquals(36,strlen($rmd['id']));
                    
                    $this->assertIsString($rmd['reminder_type']);
                    $this->assertContains($rmd['reminder_type'], ['Every Day','Every Week','Every Month','Every Year']);

                    $this->assertIsString($rmd['reminder_desc']);
                    $this->assertIsString($rmd['reminder_context']);
                    $this->assertIsString($rmd['created_at']);   
                }
            }
        }

        Audit::auditRecordText("Test - Get All Inventory", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get All Inventory", "TC-XXX", 'TC-XXX test_get_all_inventory', json_encode($data));
    }
}
