<?php

namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use GuzzleHttp\Client;
use Tests\TestCase;

// Helper
use App\Helpers\Audit;

class InventoryTest extends TestCase
{
    protected $httpClient;
    protected $room;
    protected $storage;
    protected $list_month;
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
        $this->list_month = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    }

    public function test_get_inventory_list(): void
    {
        // Exec
        $token = $this->login_trait("user");
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
            $check_object = ['id','inventory_name','inventory_vol','inventory_unit'];
            foreach ($check_object as $col) {
                $this->assertArrayHasKey($col, $dt);
            }

            $check_not_null_str = ['id','inventory_name','inventory_unit'];
            foreach ($check_not_null_str as $col) {
                $this->assertNotNull($dt[$col]);
                $this->assertIsString($dt[$col]);
            }

            $this->assertEquals(36,strlen($dt['id']));
    
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
        $token = $this->login_trait("user");
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
            $check_object = ['id','inventory_storage','layout','storage_desc'];
            foreach ($check_object as $col) {
                $this->assertArrayHasKey($col, $dt);
            }

            $check_not_null_str = ['id','inventory_storage','layout'];
            foreach ($check_not_null_str as $col) {
                $this->assertNotNull($dt[$col]);
                $this->assertIsString($dt[$col]);
            }

            $this->assertEquals(36,strlen($dt['id']));

            $this->assertNotNull($dt['layout']);
            $this->assertIsString($dt['layout']);
    
            if (!is_null($dt['storage_desc'])) {
                $this->assertIsString($dt['storage_desc']);
            }
        }

        Audit::auditRecordText("Test - Get Inventory By Room", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Inventory By Room", "TC-XXX", 'TC-XXX test_get_inventory_by_room', json_encode($data));
    }

    public function test_get_inventory_catalog(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $response = $this->httpClient->get("catalog", [
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

        $catalogs = ["room","category","storage"];
        foreach ($catalogs as $ct) {
            foreach($data['data'][$ct] as $dt) {
                $this->assertArrayHasKey('context', $dt);
                $this->assertArrayHasKey('total', $dt);

                if($ct !== "storage"){
                    $this->assertNotNull($dt['context']);
                    $this->assertIsString($dt['context']);
                }
        
                $this->assertNotNull($dt['total']);
                $this->assertIsInt($dt['total']);
                $this->assertGreaterThan(0, $dt['total']);
            }
        }

        Audit::auditRecordText("Test - Get Inventory Catalog", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Inventory Catalog", "TC-XXX", 'TC-XXX test_get_inventory_catalog', json_encode($data));
    }

    public function test_get_inventory_by_view_and_catalog(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $view = "category";
        $catalog = "Home%20Appliances";

        $response = $this->httpClient->get("catalog/$view/$catalog", [
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
            $check_object = ['id','inventory_name','inventory_category','inventory_room','inventory_storage','inventory_rack','inventory_price','inventory_image',
            'inventory_unit','inventory_vol','inventory_capacity_unit','inventory_capacity_vol','is_favorite', 'created_at','updated_at','deleted_at','reminder_type'];
            foreach ($check_object as $col) {
                $this->assertArrayHasKey($col, $dt);
            }

            $check_not_null_str = ['id','inventory_name','inventory_category','inventory_room','inventory_unit','created_at'];
            foreach ($check_not_null_str as $col) {
                $this->assertNotNull($dt[$col]);
                $this->assertIsString($dt[$col]);
            }

            $check_nullable_str = ['inventory_storage','inventory_rack','inventory_image','inventory_capacity_unit','updated_at','deleted_at','reminder_type'];
            foreach ($check_nullable_str as $col) {
                if (!is_null($dt[$col])) {
                    $this->assertIsString($dt[$col]);
                }
            }

            $check_not_null_int = ['inventory_price','inventory_vol','is_favorite'];
            foreach ($check_not_null_int as $col) {
                $this->assertNotNull($dt[$col]);
                $this->assertIsInt($dt[$col]);
                $this->assertGreaterThanOrEqual(0, $dt[$col]);
            }

            $this->assertEquals(36,strlen($dt['id']));
            $this->assertContains($dt['is_favorite'], [0, 1]);

            if (!is_null($dt['inventory_capacity_vol'])) {
                $this->assertIsInt($dt['inventory_capacity_vol']);
                $this->assertGreaterThanOrEqual(0, $dt['inventory_capacity_vol']);
            }
        }

        Audit::auditRecordText("Test - Get Inventory By View And Catalog", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Inventory By View And Catalog", "TC-XXX", 'TC-XXX test_get_inventory_by_view_and_catalog', json_encode($data));
    }

    public function test_get_inventory_by_storage(): void
    {
        // Exec
        $token = $this->login_trait("user");
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
            $check_object = ['id','inventory_name','inventory_vol','inventory_unit','inventory_category','inventory_price'];
            foreach ($check_object as $col) {
                $this->assertArrayHasKey($col, $dt);
            }

            $check_not_null_str = ['id','inventory_name','inventory_unit','inventory_category'];
            foreach ($check_not_null_str as $col) {
                $this->assertNotNull($dt[$col]);
                $this->assertIsString($dt[$col]);
            }

            $this->assertEquals(36,strlen($dt['id']));

            $check_not_null_int = ['inventory_vol','inventory_price'];
            foreach ($check_not_null_int as $col) {
                $this->assertNotNull($dt[$col]);
                $this->assertIsInt($dt[$col]);
                $this->assertGreaterThanOrEqual(0, $dt[$col]);
            }
        }

        Audit::auditRecordText("Test - Get Inventory By Storage", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Inventory By Storage", "TC-XXX", 'TC-XXX test_get_inventory_by_storage', json_encode($data));
    }

    public function test_get_inventory_calendar(): void
    {
        // Exec
        $token = $this->login_trait("user");
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
            $check_object = ['id','inventory_name','inventory_price','created_at'];
            foreach ($check_object as $col) {
                $this->assertArrayHasKey($col, $dt);
            }

            $check_not_null_str = ['id','inventory_name','created_at'];
            foreach ($check_not_null_str as $col) {
                $this->assertNotNull($dt[$col]);
                $this->assertIsString($dt[$col]);
            }

            $this->assertEquals(36,strlen($dt['id']));
            $this->assertNotNull($dt['inventory_price']);
            $this->assertIsInt($dt['inventory_price']);
            $this->assertGreaterThanOrEqual(0, $dt['inventory_price']);
        }

        Audit::auditRecordText("Test - Get Inventory Calendar", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Inventory Calendar", "TC-XXX", 'TC-XXX test_get_inventory_by_storage', json_encode($data));
    }

    public function test_get_all_inventory(): void
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
            $check_object = ['id','inventory_name','inventory_category','inventory_desc','inventory_merk','inventory_room','inventory_storage','inventory_rack','inventory_price',
                'inventory_image','inventory_unit','inventory_vol','inventory_capacity_unit','inventory_capacity_vol','inventory_color','is_favorite','is_reminder',
                'created_at','created_by','updated_at','deleted_at','reminder'];
            foreach ($check_object as $col) {
                $this->assertArrayHasKey($col, $dt);
            }

            $check_not_null_str = ['id','inventory_name','inventory_category','inventory_room','inventory_unit','created_at','created_by'];
            foreach ($check_not_null_str as $col) {
                $this->assertNotNull($dt[$col]);
                $this->assertIsString($dt[$col]);
            }

            $check_nullable_str = ['inventory_desc','inventory_merk','inventory_storage','inventory_rack',
            'inventory_image','inventory_capacity_unit','inventory_color','updated_at','deleted_at'];
            foreach ($check_nullable_str as $col) {
                if (!is_null($dt[$col])) {
                    $this->assertIsString($dt[$col]);
                }
            }

            $check_not_null_int = ['inventory_price','inventory_vol','is_favorite','is_reminder'];
            foreach ($check_not_null_int as $col) {
                $this->assertNotNull($dt[$col]);
                $this->assertIsInt($dt[$col]);
                $this->assertGreaterThanOrEqual(0, $dt[$col]);
            }

            $this->assertEquals(36,strlen($dt['id']));
            $this->assertContains($dt['is_favorite'], [0, 1]);
            $this->assertContains($dt['is_reminder'], [0, 1]);

            if (!is_null($dt['inventory_capacity_vol'])) {
                $this->assertIsInt($dt['inventory_capacity_vol']);
                $this->assertGreaterThanOrEqual(0, $dt['inventory_capacity_vol']);
            }

            $this->assertEquals(36,strlen($dt['created_by']));

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

    public function test_get_analyze_inventory_by_id(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $id = "09397f65-211e-3598-2fa5-b50cdba5183c";
        $response = $this->httpClient->get("analyze/$id", [
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

        $check_object = ['inventory_name','inventory_price','inventory_category','inventory_room','inventory_storage','inventory_rack','inventory_unit',
            'inventory_vol','inventory_capacity_unit','inventory_capacity_vol','created_at','updated_at','inventory_price_analyze','inventory_category_analyze','inventory_room_analyze',
            'inventory_unit_analyze','inventory_history_analyze','inventory_report','inventory_in_monthly_report','inventory_layout'];
        foreach ($check_object as $col) {
            $this->assertArrayHasKey($col, $data['data']);
        }

        $check_not_null_str = ['inventory_name','inventory_category','inventory_room','inventory_unit','created_at'];
        foreach ($check_not_null_str as $col) {
            $this->assertNotNull($data['data'][$col]);
            $this->assertIsString($data['data'][$col]);
        }

        $check_nullable_str = ['inventory_storage','inventory_rack','inventory_capacity_unit'];
        foreach ($check_nullable_str as $col) {
            if (!is_null($data['data'][$col])) {
                $this->assertIsString($data['data'][$col]);
            }
        }

        $check_not_null_int = ['inventory_price','inventory_vol'];
        foreach ($check_not_null_int as $col) {
            $this->assertNotNull($data['data'][$col]);
            $this->assertIsInt($data['data'][$col]);
            $this->assertGreaterThanOrEqual(0, $data['data'][$col]);
        }

        if (!is_null($data['data']['inventory_capacity_vol'])) {
            $this->assertIsInt($data['data']['inventory_capacity_vol']);
            $this->assertGreaterThanOrEqual(0, $data['data']['inventory_capacity_vol']);
        }

        if(!is_null($data['data']['inventory_price_analyze'])){
            $check_object = ['average_inventory_price','sub_total','max_inventory_price','min_inventory_price','diff_ammount_average_to_price','diff_status_average_to_price'];
            foreach ($check_object as $col) {
                $this->assertArrayHasKey($col, $data['data']['inventory_price_analyze']);
            }

            $check_not_null_int = ['average_inventory_price','sub_total','max_inventory_price','min_inventory_price','diff_ammount_average_to_price'];
            foreach ($check_not_null_int as $col) {
                $this->assertIsInt($data['data']['inventory_price_analyze'][$col]);
                $this->assertGreaterThanOrEqual(0, $data['data']['inventory_price_analyze'][$col]);
            }

            $this->assertIsString($data['data']['inventory_price_analyze']['diff_status_average_to_price']);
        }

        $check_total_avg_price_context = ['inventory_category_analyze','inventory_room_analyze','inventory_unit_analyze'];
        foreach ($check_total_avg_price_context as $dt) {
            if(!is_null($data['data'][$dt])){
                $check_object = ['total','average_price'];
                foreach ($check_object as $col) {
                    $this->assertArrayHasKey($col, $data['data'][$dt]);
                }
    
                $check_not_null_int = ['total','average_price'];
                foreach ($check_not_null_int as $col) {
                    $this->assertIsInt($data['data'][$dt][$col]);
                    $this->assertGreaterThanOrEqual(0, $data['data'][$dt][$col]);
                }
            }
        }

        if (!is_null($data['data']['inventory_history_analyze'])) {
            foreach ($data['data']['inventory_history_analyze'] as $dt) {
                $this->assertArrayHasKey('report_category', $dt);
                $this->assertIsString($dt['report_category']);
                $this->assertNotNull($dt['report_category']);

                $this->assertArrayHasKey('total', $dt);
                $this->assertIsInt($dt['total']);
                $this->assertGreaterThanOrEqual(0, $dt['total']);
            }
        }

        if (!is_null($data['data']['inventory_report'])) {
            foreach ($data['data']['inventory_report'] as $dt) {
                $check_object = ['report_title','report_category','created_at'];
                foreach ($check_object as $col) {
                    $this->assertArrayHasKey($col, $dt[$col]);
                }

                $check_not_null_str = ['report_title','report_category','created_at'];
                foreach ($check_not_null_str as $col) {
                    $this->assertNotNull($dt[$col]);
                    $this->assertIsString($dt[$col]);
                }
            }
        }

        if (!is_null($data['data']['inventory_in_monthly_report'])) {
            foreach ($data['data']['inventory_in_monthly_report'] as $dt) {
                $this->assertArrayHasKey('context', $dt);
                $this->assertIsString($dt['context']);
                $this->assertNotNull($dt['context']);
                $this->assertContains($dt['context'], $this->list_month);

                $this->assertArrayHasKey('total', $dt);
                $this->assertIsInt($dt['total']);
                $this->assertGreaterThanOrEqual(0, $dt['total']);
            }
        }

        if(!is_null($data['data']['inventory_layout'])){
            $check_object = ['inventory_storage','layout','storage_desc','created_at'];
            foreach ($check_object as $col) {
                $this->assertArrayHasKey($col, $data['data']['inventory_layout']);
            }

            $check_not_null_str = ['inventory_storage','layout','created_at'];
            foreach ($check_not_null_str as $col) {
                $this->assertNotNull($data['data']['inventory_layout'][$col]);
                $this->assertIsString($data['data']['inventory_layout'][$col]);
            }

            if(!is_null($data['data']['inventory_layout']['storage_desc'])){
                $this->assertIsString($data['data']['inventory_layout']['storage_desc']);
            }
        }

        Audit::auditRecordText("Test - Get Analyze Inventory By Id", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Analyze Inventory By Id", "TC-XXX", 'TC-XXX test_get_analyze_inventory_by_id', json_encode($data));
    }

    public function test_get_layout_room_doc(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $response = $this->httpClient->get("layout/$this->room/doc", [
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
        $this->assertIsString($data['data']);

        Audit::auditRecordText("Test - Get Layout Room Doc", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Layout Room Doc", "TC-XXX", 'TC-XXX test_get_layout_room_doc', json_encode($data));
    }

    public function test_get_inventory_detail_doc(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $id = "09397f65-211e-3598-2fa5-b50cdba5183c";
        $response = $this->httpClient->get("detail/$id/doc", [
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
        $this->assertIsString($data['data']);

        Audit::auditRecordText("Test - Get Inventory Detail Doc", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Inventory Detail Doc", "TC-XXX", 'TC-XXX test_get_inventory_detail_doc', json_encode($data));
    }

    public function test_soft_delete_inventory_by_id(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $id = "16d91674-a1d0-1d25-1db2-3837f3d35e21";
        $response = $this->httpClient->delete("delete/$id", [
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
        $this->assertEquals('inventory deleted',$data['message']);

        Audit::auditRecordText("Test - Soft Delete Inventory By ID", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Soft Delete Inventory By ID", "TC-XXX", 'TC-XXX test_soft_delete_inventory_by_id', json_encode($data));
    }

    public function test_hard_delete_inventory_by_id(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $id = "16d91674-a1d0-1d25-1db2-3837f3d35e21";
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
        $this->assertEquals('inventory permentally deleted',$data['message']);

        Audit::auditRecordText("Test - Hard Delete Inventory By ID", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Hard Delete Inventory By ID", "TC-XXX", 'TC-XXX test_hard_delete_inventory_by_id', json_encode($data));
    }

    public function test_put_fav_toogle_inventory_by_id(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $id = "09397f65-211e-3598-2fa5-b50cdba5183c";
        $body = [
            "is_favorite" => 1
        ];
        $response = $this->httpClient->put("fav_toggle/$id", [
            'headers' => [
                'Authorization' => "Bearer $token"
            ],
            'json' => $body
        ]);

        $data = json_decode($response->getBody(), true);

        // Test Parameter
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('success', $data['status']);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('inventory updated',$data['message']);

        Audit::auditRecordText("Test - Put Fav Toogle Inventory By ID", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Put Fav Toogle Inventory By ID", "TC-XXX", 'TC-XXX test_put_fav_toogle_inventory_by_id', json_encode($data));
    }

    public function test_put_recover_inventory_by_id(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $id = "09397f65-211e-3598-2fa5-b50cdba5183c";
        $response = $this->httpClient->put("recover/$id", [
            'headers' => [
                'Authorization' => "Bearer $token"
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        // Test Parameter
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('success', $data['status']);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('inventory recovered',$data['message']);

        Audit::auditRecordText("Test - Put Recover Inventory By ID", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Put Recover Inventory By ID", "TC-XXX", 'TC-XXX test_put_recover_inventory_by_id', json_encode($data));
    }

    public function test_put_edit_image_by_id(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $id = "09397f65-211e-3598-2fa5-b50cdba5183c";
        $filePath = public_path('images/Success.png'); 

        $this->assertFileExists($filePath, "The file does not exist at the specified path: $filePath");

        $response = $this->httpClient->put("edit_image/$id", [
            'headers' => [
                'Authorization' => "Bearer $token",
            ],
            'multipart' => [
                [
                    'name' => 'file', 
                    'contents' => fopen($filePath, 'r'), 
                    'filename' => basename($filePath), 
                ],
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        // Test Parameter
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('success', $data['status']);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('inventory image updated',$data['message']);

        Audit::auditRecordText("Test - Put Edit Image By ID", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Put Edit Image By ID", "TC-XXX", 'TC-XXX test_put_edit_image_by_id', json_encode($data));
    }

    public function test_put_edit_layout_by_id(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $id = "64741fe2-4982-131a-1332-59df765cdb30";
        $body = [
            "inventory_storage" => "Fridge Small",
            "storage_desc" => "Store frozen food & drink"
        ];
        $response = $this->httpClient->put("edit_layout/$id", [
            'headers' => [
                'Authorization' => "Bearer $token"
            ],
            'json' => $body
        ]);

        $data = json_decode($response->getBody(), true);

        // Test Parameter
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('success', $data['status']);
        $this->assertArrayHasKey('message', $data);
        $this->assertTrue(str_contains($data['message'], 'inventory layout updated and impacted to') || $data['message'] === 'nothing has change');

        Audit::auditRecordText("Test - Put Edit Layout By ID", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Put Edit Layout By ID", "TC-XXX", 'TC-XXX test_put_edit_layout_by_id', json_encode($data));
    }

    public function test_hard_del_inventory_layout_by_id_coor(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $id = "ab8b8d0e-d74d-11ed-afa1-0242ac110002";
        $coor = "D3";
        $response = $this->httpClient->delete("delete_layout/$id/$coor", [
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
        $this->assertStringContainsString('inventory layout coordinate deleted',$data['message']);

        Audit::auditRecordText("Test - Hard Delete Inventory Layout By ID Coor", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Hard Delete Inventory Layout By ID Coor", "TC-XXX", 'TC-XXX test_hard_del_inventory_layout_by_id_coor', json_encode($data));
    }

    public function test_post_inventory_layout(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $body = [
            "inventory_room" => "Main Room",
            "inventory_storage" => "Testing Storage",
            "storage_desc" => "This is for API Testing",
            "layout" => "A1:A2:A3:B1:B2:B3s"
        ];
        $response = $this->httpClient->post("layout", [
            'headers' => [
                'Authorization' => "Bearer $token"
            ],
            'json' => $body
        ]);

        $data = json_decode($response->getBody(), true);

        // Test Parameter
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('success', $data['status']);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('inventory layout coordinate created', $data['message']);

        Audit::auditRecordText("Test - Post Inventory Layout", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Post Inventory Layout", "TC-XXX", 'TC-XXX test_post_inventory_layout', json_encode($data));
    }

    public function test_post_inventory(): void
    {
        // Exec
        $token = $this->login_trait("user");
        
        // Create fake images
        $img1 = UploadedFile::fake()->image('image1.jpg');

        $form = [
            ['name' => 'inventory_name', 'contents' => 'Product B - Testing'],
            ['name' => 'inventory_category', 'contents' => 'Baby Care'],
            ['name' => 'inventory_desc', 'contents' => 'Testing Add Product'],
            ['name' => 'inventory_merk', 'contents' => 'Great Product'],
            ['name' => 'inventory_color', 'contents' => ''],
            ['name' => 'inventory_room', 'contents' => 'Main Room'],
            ['name' => 'inventory_storage', 'contents' => 'Shelf'],
            ['name' => 'inventory_rack', 'contents' => 'Bottom Rack'],
            ['name' => 'inventory_price', 'contents' => 25000],
            ['name' => 'inventory_unit', 'contents' => 'Pcs'],
            ['name' => 'inventory_vol', 'contents' => 5],
            ['name' => 'inventory_capacity_unit', 'contents' => 'Percentage'],
            ['name' => 'inventory_capacity_vol', 'contents' => 20],
            ['name' => 'is_favorite', 'contents' => 0],
            ['name' => 'created_at', 'contents' => '2024-05-19 21:05:53'],
            [
                'name'     => 'inventory_image',
                'contents' => fopen($img1->getPathname(), 'r'),
                'filename' => 'image1.jpg',
            ],
        ];

        $response = $this->httpClient->post("", [
            'headers' => [
                'Authorization' => "Bearer $token"
            ],
            'multipart' => $form,
        ]);

        $data = json_decode($response->getBody(), true);

        // Test Parameter
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('success', $data['status']);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals("inventory created, its called '".$form['inventory_name']."'", $data['message']);

        Audit::auditRecordText("Test - Post Inventory", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Post Inventory", "TC-XXX", 'TC-XXX test_post_inventory', json_encode($data));
    }

    public function test_put_update_inventory_by_id(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $id = "bfbbb920-b22d-cfa3-1b36-afad9e6cd963";
        $body = [
            "inventory_name" => "Product A - Testing", 
            "inventory_category" => "Baby Care", 
            "inventory_desc" => "Testing Add Product", 
            "inventory_merk" => "Great Product", 
            "inventory_color" => null, 
            "inventory_room" => "Main Room", 
            "inventory_storage" => "Shelf", 
            "inventory_rack" => "Bottom Rack", 
            "inventory_price" => 25000, 
            "inventory_unit" => "Pcs", 
            "inventory_vol" => 1, 
            "inventory_capacity_unit" => null, 
            "inventory_capacity_vol" => null, 
            "is_favorite" => 0, 
            "created_at" => "2024-05-19 21:05:53"
        ];
        $response = $this->httpClient->put("edit/$id", [
            'headers' => [
                'Authorization' => "Bearer $token"
            ],
            'json' => $body
        ]);

        $data = json_decode($response->getBody(), true);

        // Test Parameter
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('success', $data['status']);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals("inventory '".$body['inventory_name']."' is updated", $data['message']);

        Audit::auditRecordText("Test - Put Update Inventory By ID", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Put Update Inventory By ID", "TC-XXX", 'TC-XXX test_put_update_inventory_by_id', json_encode($data));
    }

    public function test_get_list_room(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $response = $this->httpClient->get("room", [
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
            $this->assertArrayHasKey('inventory_room', $dt);
            $this->assertNotNull($dt['inventory_room']);
            $this->assertIsString($dt['inventory_room']);
        }

        Audit::auditRecordText("Test - Get List Room", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get List Room", "TC-XXX", 'TC-XXX test_get_list_room', json_encode($data));
    }

    public function test_get_inventory_by_id(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $id = "09397f65-211e-3598-2fa5-b50cdba5183c";
        $response = $this->httpClient->get("detail/$id", [
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

        $check_object = ['id','inventory_name','inventory_category','inventory_desc','inventory_merk','inventory_room','inventory_storage','inventory_rack','inventory_price',
            'inventory_image','inventory_unit','inventory_vol','inventory_capacity_unit','inventory_capacity_vol','inventory_color','is_favorite','is_reminder',
            'created_at','updated_at'];
        foreach ($check_object as $col) {
            $this->assertArrayHasKey($col, $data['data']);
        }

        $check_not_null_str = ['id','inventory_name','inventory_category','inventory_room','inventory_unit','created_at'];
        foreach ($check_not_null_str as $col) {
            $this->assertNotNull($data['data'][$col]);
            $this->assertIsString($data['data'][$col]);
        }

        $check_nullable_str = ['inventory_desc','inventory_merk','inventory_storage','inventory_rack',
        'inventory_image','inventory_capacity_unit','inventory_color','updated_at'];
        foreach ($check_nullable_str as $col) {
            if (!is_null($data['data'][$col])) {
                $this->assertIsString($data['data'][$col]);
            }
        }

        $check_not_null_int = ['inventory_price','inventory_vol','is_favorite','is_reminder'];
        foreach ($check_not_null_int as $col) {
            $this->assertNotNull($data['data'][$col]);
            $this->assertIsInt($data['data'][$col]);
            $this->assertGreaterThanOrEqual(0, $data['data'][$col]);
        }

        $this->assertEquals(36,strlen($data['data']['id']));
        $this->assertContains($data['data']['is_favorite'], [0, 1]);
        $this->assertContains($data['data']['is_reminder'], [0, 1]);

        if (!is_null($data['data']['inventory_capacity_vol'])) {
            $this->assertIsInt($data['data']['inventory_capacity_vol']);
            $this->assertGreaterThanOrEqual(0, $data['data']['inventory_capacity_vol']);
        }

        if (!is_null($data['reminder'])) {
            foreach ($data['reminder'] as $rmd) {
                $this->assertIsString($rmd['id']);
                $this->assertEquals(36,strlen($rmd['id']));
                
                $this->assertIsString($rmd['reminder_type']);
                $this->assertContains($rmd['reminder_type'], ['Every Day','Every Week','Every Month','Every Year']);

                $this->assertIsString($rmd['reminder_desc']);
                $this->assertIsString($rmd['reminder_context']);
                $this->assertIsString($rmd['created_at']);   
            }
        }

        Audit::auditRecordText("Test - Get Inventory By ID", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Inventory By ID", "TC-XXX", 'TC-XXX test_get_inventory_by_id', json_encode($data));
    }
}
