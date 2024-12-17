<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use GuzzleHttp\Client;
use Tests\TestCase;
use App\Helpers\Audit;

class ReminderTest extends TestCase
{
    protected $httpClient;
    use LoginHelperTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = new Client([
            'base_uri' => 'http://127.0.0.1:8000/api/v1/reminder/',
            'http_errors' => false
        ]);
    }

    public function test_post_reminder(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $body = [
            "inventory_id" => "83ce75db-4016-d87c-2c3c-db1e222d0001",
            "reminder_desc" => "Restock at https://tokopedia.link/rBfBm3vVDIbBeli 2 boleh",
            "reminder_type" => "Every Month",
            "reminder_context" => "Every 3",
            "send_demo" => true
        ];
        $response = $this->httpClient->post("", [
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
        $this->assertEquals('reminder created', $data['message']);

        Audit::auditRecordText("Test - Post Reminder", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Post Reminder", "TC-XXX", 'TC-XXX test_post_reminder', json_encode($data));
    }
    
    public function test_delete_reminder_by_id(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $id = "935265fc-29d7-fe3f-3b04-061c674ea69c";
        $response = $this->httpClient->post("/$id", [
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
        $this->assertEquals('reminder deleted', $data['message']);
        
        Audit::auditRecordText("Test - Delete Reminder By Id", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Delete Reminder By Id", "TC-XXX", 'TC-XXX test_delete_reminder_by_id', json_encode($data));
    }

    public function test_get_reminder_mark(): void
    {
        // Exec
        $token = $this->login_trait("admin");
        $response = $this->httpClient->get("mark", [
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
            $check_object = ['inventory_name','inventory_category','reminder_desc','reminder_type','reminder_context','last_execute','created_at','username'];
            foreach ($check_object as $col) {
                $this->assertArrayHasKey($col, $dt);
            }

            $check_not_null_str = ['inventory_name','inventory_category','reminder_desc','reminder_type','reminder_context','last_execute','created_at','username'];
            foreach ($check_not_null_str as $col) {
                $this->assertNotNull($dt[$col]);
                $this->assertIsString($dt[$col]);
            }
        }

        Audit::auditRecordText("Test - Get Reminder Mark", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Reminder Mark", "TC-XXX", 'TC-XXX test_get_reminder_mark', json_encode($data));
    }

    public function test_post_re_remind(): void
    {
        // Exec
        $token = $this->login_trait("admin");
        $body = [
            "reminder_id" => "d6260a32-f612-11ee-b7be-3216422910e7"
        ];
        $response = $this->httpClient->post("re_remind", [
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
        $this->assertEquals('reminder re-executed', $data['message']);

        Audit::auditRecordText("Test - Post Re-Remind", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Post Re-Remind", "TC-XXX", 'TC-XXX test_post_re_remind', json_encode($data));
    }
}
