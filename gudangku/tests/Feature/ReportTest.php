<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use GuzzleHttp\Client;
use Tests\TestCase;
use App\Helpers\Audit;

class ReportTest extends TestCase
{
    protected $httpClient;
    protected $room;
    protected $storage;
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
    }

    public function test_get_report_detail_doc_format_by_id(): void
    {
        // Exec
        $token = $this->login_trait();
        $id = "9f8b26ed-750a-ba92-3100-bb0b4b3ffb4b";
        $response = $this->httpClient->get("detail/item/$id/doc", [
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

        Audit::auditRecordText("Test - Get Report Detail Doc Format By Id", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Report Detail Doc Format By Id", "TC-XXX", 'TC-XXX test_get_report_detail_doc_format_by_id', json_encode($data));
    }

    public function test_get_report_detail_by_id(): void
    {
        // Exec
        $token = $this->login_trait();
        $id = "9f8b26ed-750a-ba92-3100-bb0b4b3ffb4b";
        $response = $this->httpClient->get("detail/item/$id", [
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
            if(!is_null($data['data'][$dt])){
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
            if(!is_null($dt['inventory_id'])){
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
                if(!is_null($dt[$col])){
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
                if(!is_null($dt[$col])){
                    $this->assertIsInt($dt[$col]);
                    $this->assertGreaterThanOrEqual(0, $dt[$col]);
                }
            }

            $check_nullable_str = ['report_desc','remind_at','report_items'];
            foreach ($check_nullable_str as $col) {
                if(!is_null($dt[$col])){
                    $this->assertIsString($dt[$col]);
                }
            }
        }

        Audit::auditRecordText("Test - Get All Report", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get All Report", "TC-XXX", 'TC-XXX test_get_all_report', json_encode($data));
    }
}
