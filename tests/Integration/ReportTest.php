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

class ReportTest extends TestCase
{
    protected $httpClient;
    protected $token;
    protected $room;
    protected $storage;
    protected $inventoryId;
    protected $inventoryName;
    protected $inventoryDesc;
    protected $inventoryIdB;
    protected $inventoryNameB;
    protected $inventoryDescB;
    protected $reportId;
    use LoginHelperTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = new Client([
            'base_uri' => 'http://127.0.0.1:8000/api/v1/report/',
            'http_errors' => false
        ]);
        $this->room = "Main%20Room";
        $this->storage = "Main%20Table";

        // Pre-Condition: User already sign in
        $this->token = $this->login_trait("user");
        // Pre-Condition: At least an inventory exists
        $this->inventoryId = TestDataReader::getValue('inventory_id') ?? "";
        $this->inventoryName = TestDataReader::getValue('inventory_name') ?? "";
        $this->inventoryDesc = TestDataReader::getValue('inventory_desc') ?? "";

        $this->inventoryIdB = TestDataReader::getValue('inventory_id_b') ?? "";
        $this->inventoryNameB = TestDataReader::getValue('inventory_name_b') ?? "";
        $this->inventoryDescB = TestDataReader::getValue('inventory_desc_b') ?? "";
        // Pre-Condition: At least a report exists
        $this->reportId = TestDataReader::getValue('report_id') ?? "";
    }

    public function test_post_report(): void
    {
        // Exec
        // Create fake images
        $reportImage = UploadedFile::fake()->image('image1.jpg');

        $form = [
            ['name' => 'report_title', 'contents' => 'Test Add Report A'],
            ['name' => 'report_desc', 'contents' => 'Test Add Report'],
            ['name' => 'report_category', 'contents' => 'Checkout'],
            ['name' => 'is_reminder', 'contents' => 1],
            ['name' => 'report_item', 'contents' => json_encode([
                'inventory_id' => $this->inventoryId,
                'item_name' => $this->inventoryName,
                'item_desc' => $this->inventoryDesc,
                'item_qty' => 1,
                'item_price' => 650000,
            ])],
            ['name' => 'created_at', 'contents' => date('Y-m-d H:i:s', strtotime('-1 week'))],
            [
                'name' => 'report_image',
                'contents' => fopen($reportImage->getPathname(), 'r'),
                'filename' => 'report_image.jpg',
            ],
        ];
        $response = $this->httpClient->post("", [
            'headers' => [
                'Authorization' => "Bearer ".$this->token
            ],
            'multipart' => $form,
        ]);

        $data = json_decode($response->getBody(), true);

        // Test Parameter
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('success', $data['status']);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('report created', $data['message']);

        // Store all created data
        foreach ($form as $dt) {
            if (array_key_exists('filename', $dt)) continue; 
            TestDataReader::setValue($dt['name'], $dt['contents']);
        }
        TestDataReader::setValue('report_id', $data['data']['id']);

        Audit::auditRecordText("Test - Post Report", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Post Report", "TC-XXX", 'TC-XXX test_post_report', json_encode($data));
    }

    public function test_post_report_item(): void
    {
        // Exec
        $body = [
            "report_item" => json_encode([
                "inventory_id" => $this->inventoryIdB,
                "item_name" => $this->inventoryNameB,
                "item_desc" => $this->inventoryDescB,
                "item_qty" => 1,
                "item_price" => 650000,
            ]),
        ];
        $response = $this->httpClient->post("item/".$this->reportId, [
            'headers' => [
                'Authorization' => "Bearer ".$this->token
            ],
            'json' => $body
        ]);

        $data = json_decode($response->getBody(), true);

        // Test Parameter
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('success', $data['status']);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('report item created', $data['message']);

        Audit::auditRecordText("Test - Post Report Item", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Post Report Item", "TC-XXX", 'TC-XXX test_post_report_item', json_encode($data));
    }

    public function test_get_report_detail_doc_format_by_id(): void
    {
        // Exec
        $response = $this->httpClient->get("detail/item/$this->reportId/doc", [
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

        Audit::auditRecordText("Test - Get Report Detail Doc Format By Id", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Report Detail Doc Format By Id", "TC-XXX", 'TC-XXX test_get_report_detail_doc_format_by_id', json_encode($data));
    }

    public function test_get_report_detail_by_id(): void
    {
        // Exec
        $response = $this->httpClient->get("detail/item/".$this->reportId, [
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

        $this->assertEquals(36,strlen($data['data']['id']));

        $check_object = ['id','report_title','report_desc','report_category','report_image','is_reminder','remind_at','created_at','created_by','updated_at','deleted_at','total_item','total_price'];
        foreach ($check_object as $dt) {
            $this->assertArrayHasKey($dt,$data['data']);
        }

        $check_not_null_str = ['id','report_title','report_category','created_at','created_by'];
        foreach ($check_not_null_str as $dt) {
            $this->assertNotNull($data['data'][$dt]);
            $this->assertIsString($data['data'][$dt]);
        }

        $check_nullable_str = ['report_desc','report_image','remind_at','updated_at','deleted_at'];
        foreach ($check_nullable_str as $dt) {
            if (!is_null($data['data'][$dt])) {
                $this->assertIsString($data['data'][$dt]);
            }
        }

        $check_not_null_int = ['is_reminder','total_item','total_price'];
        foreach ($check_not_null_int as $dt) {
            $this->assertNotNull($data['data'][$dt]);
            $this->assertIsInt($data['data'][$dt]);
        }

        $check_valid_int = ['total_item','total_price'];
        foreach ($check_valid_int as $dt) {
            $this->assertGreaterThanOrEqual(0, $data['data'][$dt]);
        }

        $this->assertIsInt($data['data']['is_reminder']);
        $this->assertContains($data['data']['is_reminder'], [0, 1]);

        $this->assertEquals(36,strlen($data['data']['created_by']));

        foreach ($data['data_item'] as $dt) {
            $check_id = ['id','report_id','created_by'];
            foreach ($check_id as $col) {
                $this->assertEquals(36,strlen($dt[$col]));

            }
            if (!is_null($dt['inventory_id'])) {
                $this->assertEquals(36,strlen($dt['inventory_id']));
            }

            $check_not_null_str = ['id','report_id','item_name','created_at','created_by'];
            foreach ($check_not_null_str as $col) {
                $this->assertNotNull($dt[$col]);
                $this->assertIsString($dt[$col]);
            }

            $check_not_null_int = ['item_qty','item_price'];
            foreach ($check_not_null_int as $col) {
                $this->assertNotNull($dt[$col]);
                $this->assertIsInt($dt[$col]);
            }

            $check_nullable_str = ['inventory_id','item_desc'];
            foreach ($check_nullable_str as $col) {
                if (!is_null($dt[$col])) {
                    $this->assertNotNull($dt[$col]);
                    $this->assertIsString($dt[$col]);
                }
            }

            $check_valid_int = ['item_qty','item_price'];
            foreach ($check_valid_int as $col) {
                $this->assertGreaterThanOrEqual(0, $dt[$col]);
            }
        }

        Audit::auditRecordText("Test - Get Report Detail By Id", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Report Detail By Id", "TC-XXX", 'TC-XXX test_get_report_detail_by_id', json_encode($data));
    }

    public function test_get_all_report(): void
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
            $this->assertEquals(36,strlen($dt['id']));

            $check_object = ['id','report_title','report_desc','report_category','is_reminder','remind_at','created_at','total_variety','total_item','report_items','item_price'];
            foreach ($check_object as $col) {
                $this->assertArrayHasKey($col, $dt);
            }

            $check_not_null_str = ['id','report_title','report_category','created_at'];
            foreach ($check_not_null_str as $col) {
                $this->assertNotNull($dt[$col]);
                $this->assertIsString($dt[$col]);
            }

            $check_not_null_int = ['is_reminder','total_variety'];
            foreach ($check_not_null_int as $col) {
                $this->assertNotNull($dt[$col]);
                $this->assertIsInt($dt[$col]);
            }
            $this->assertContains($dt['is_reminder'],[0,1]);

            $check_nullable_int = ['total_item','item_price'];
            foreach ($check_nullable_int as $col) {
                if (!is_null($dt[$col])) {
                    $this->assertIsInt($dt[$col]);
                    $this->assertGreaterThanOrEqual(0, $dt[$col]);
                }
            }

            $check_nullable_str = ['report_desc','remind_at','report_items'];
            foreach ($check_nullable_str as $col) {
                if (!is_null($dt[$col])) {
                    $this->assertIsString($dt[$col]);
                }
            }
        }

        Audit::auditRecordText("Test - Get All Report", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get All Report", "TC-XXX", 'TC-XXX test_get_all_report', json_encode($data));
    }

    public function test_get_report_by_inventory_name_or_inventory_id(): void
    {
        // Exec
        $search = "Herborist%20Aloe%20Vera%20Gel";
        $id = $this->inventoryId;
        $response = $this->httpClient->get("$search/$id", [
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
            $this->assertEquals(36,strlen($dt['id']));

            $check_object = ['id','report_title','report_desc','report_category','is_reminder','remind_at','created_at','total_variety','total_item','report_items','item_price'];
            foreach ($check_object as $col) {
                $this->assertArrayHasKey($col, $dt);
            }

            $check_not_null_str = ['id','report_title','report_category','created_at'];
            foreach ($check_not_null_str as $col) {
                $this->assertNotNull($dt[$col]);
                $this->assertIsString($dt[$col]);
            }

            $check_not_null_int = ['is_reminder','total_variety'];
            foreach ($check_not_null_int as $col) {
                $this->assertNotNull($dt[$col]);
                $this->assertIsInt($dt[$col]);
            }
            $this->assertContains($dt['is_reminder'],[0,1]);

            $check_nullable_int = ['total_item','item_price'];
            foreach ($check_nullable_int as $col) {
                if (!is_null($dt[$col])) {
                    $this->assertIsInt($dt[$col]);
                    $this->assertGreaterThanOrEqual(0, $dt[$col]);
                }
            }

            $check_nullable_str = ['report_desc','remind_at','report_items'];
            foreach ($check_nullable_str as $col) {
                if (!is_null($dt[$col])) {
                    $this->assertIsString($dt[$col]);
                }
            }
        }

        Audit::auditRecordText("Test - Get My Report By Inventory", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get My Report By Inventory", "TC-XXX", 'TC-XXX test_get_my_report_by_inventory', json_encode($data));
    }

    public function test_put_update_report_by_id(): void
    {
        // Exec
        $body = [
            "report_title" => "Test Update Report",
            "report_desc" => "This is an API Testing",
            "report_category" => "Checkout",
        ];
        $response = $this->httpClient->put("update/report/".$this->reportId, [
            'headers' => [
                'Authorization' => "Bearer ".$this->token
            ],
            'json' => $body
        ]);

        $data = json_decode($response->getBody(), true);

        // Test Parameter
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('success', $data['status']);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('report updated',$data['message']);

        Audit::auditRecordText("Test - Put Update Report By Id", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Put Update Report By Id", "TC-XXX", 'TC-XXX test_put_update_report_by_id', json_encode($data));
    }

    public function test_put_update_report_item_by_id(): void
    {
        // Exec
        $body = [
            "item_name" => 'Product A',
            "item_desc" => 'Test Update Item',
            "item_qty" => 2,
            "item_price" => 19000
        ];
        $response = $this->httpClient->put("update/report_item/".$this->reportItemId, [
            'headers' => [
                'Authorization' => "Bearer ".$this->token
            ],
            'json' => $body
        ]);

        $data = json_decode($response->getBody(), true);

        // Test Parameter
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('success', $data['status']);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('report item updated',$data['message']);

        Audit::auditRecordText("Test - Put Update Report Item By Id", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Put Update Report Item By Id", "TC-XXX", 'TC-XXX test_put_update_report_item_by_id', json_encode($data));
    }

    public function test_put_update_split_report_item_by_id(): void
    {
        // Exec
        $body = [
            "list_id" => "29bfadc5-7b4c-df51-0337-12e966ce2f5d,633eaba9-9175-38f9-3b43-0ccd9267cf02",
            "report_title" => "Test Split Report A",
            "report_desc" => "Test Split Report",
            "report_category" => "Checkout",
            "is_reminder" => 0
        ];
        $response = $this->httpClient->put("update/report_split/".$this->reportId, [
            'headers' => [
                'Authorization' => "Bearer ".$this->token
            ],
            'json' => $body
        ]);

        $data = json_decode($response->getBody(), true);

        // Test Parameter
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('success', $data['status']);
        $this->assertArrayHasKey('message', $data);
        $this->assertStringContainsString('report items updated',$data['message']);

        Audit::auditRecordText("Test - Put Update Report Item By Id", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Put Update Report Item By Id", "TC-XXX", 'TC-XXX test_put_update_split_report_item_by_id', json_encode($data));
    }

    public function test_post_update_report_image_by_report_id(): void
    {
        // Exec
        $id = "9be458e0-48da-d13f-0b41-ef4ce8a4bcad";

        // Create fake images
        $img1 = UploadedFile::fake()->image('image1.jpg');
        $img2 = UploadedFile::fake()->image('image2.jpg');

        $form = [
            [
                'name'     => 'report_image[]',
                'contents' => fopen($img1->getPathname(), 'r'),
                'filename' => 'image1.jpg',
            ],
            [
                'name'     => 'report_image[]',
                'contents' => fopen($img2->getPathname(), 'r'),
                'filename' => 'image2.jpg',
            ],
        ];

        $response = $this->httpClient->post("report_image/$id", [
            'headers' => [
                'Authorization' => "Bearer ".$this->token
            ],
            'multipart' => $form,
        ]);

        $data = json_decode($response->getBody(), true);

        // Test Parameter
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('success', $data['status']);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('report updated', $data['message']);

        Audit::auditRecordText("Test - Post Update Report Image By Report ID", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Post Report Report Image By Report ID", "TC-XXX", 'TC-XXX test_post_update_report_image_by_report_id', json_encode($data));
    }

    public function test_hard_delete_report_image_by_report_id_and_image_id(): void
    {
        // Exec
        $response = $this->httpClient->delete("report_image/destroy/$this->report_id/".$this->reportImageId, [
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
        $this->assertEquals('report image deleted', $data['message']);

        Audit::auditRecordText("Test - Hard Delete Report Image By Report ID And Image ID", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Hard Delete Report Image By Report ID And Image ID", "TC-XXX", 'TC-XXX test_hard_delete_report_image_by_report_id_and_image_id', json_encode($data));
    }
}
