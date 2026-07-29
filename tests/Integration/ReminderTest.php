<?php

namespace Tests\Integration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use GuzzleHttp\Client;
use Tests\TestCase;

// Helper
use App\Helpers\Audit;
use App\Helpers\TestDataReader;

class ReminderTest extends TestCase
{
    protected $httpClient;
    protected $token;
    protected $inventoryId;
    protected $reminderId;
    use LoginHelperTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = new Client([
            'base_uri' => 'http://127.0.0.1:8000/api/v1/reminder/',
            'http_errors' => false
        ]);

        // Pre-Condition: User already sign in
        $this->token = $this->login_trait("user");
        // Pre-Condition: At least an inventory exists
        $this->inventoryId = TestDataReader::getValue('inventory_id') ?? "";
        // Pre-Condition: At least a reminder exists
        $this->reminderId = TestDataReader::getValue('reminder_id') ?? "";
    }

    public function test_post_reminder(): void
    {
        // Exec
        $body = [
            "inventory_id" => $this->inventoryId,
            "reminder_desc" => "Restock at https://tokopedia.link/rBfBm3vVDIbBeli 2 boleh",
            "reminder_type" => "Every Month",
            "reminder_context" => "Every 3",
            "send_demo" => true
        ];
        $response = $this->httpClient->post("", [
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
        $this->assertEquals('reminder created', $data['message']);

        // Store all created data
        foreach ($body as $key => $val) {
            TestDataReader::setValue($key, $val);
        }
        TestDataReader::setValue('reminder_id', $data['data']['id']);

        Audit::auditRecordText("Test - Post Reminder", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Post Reminder", "TC-XXX", 'TC-XXX test_post_reminder', json_encode($data));
    }

    public function test_post_copy_reminder(): void
    {
        // Exec
        $body = [
            "reminder_desc" => "Restock at https://tokopedia.link/rBfBm3vVDIbBeli 2 boleh",
            "reminder_type" => "Every Month",
            "reminder_context" => "Every 28",
            "list_inventory_id" => $this->inventoryId,
        ];
        $response = $this->httpClient->post("copy", [
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
        $this->assertStringContainsString('reminder created for inventory :', $data['message']);

        Audit::auditRecordText("Test - Post Copy Reminder", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Post Copy Reminder", "TC-XXX", 'TC-XXX test_post_copy_reminder', json_encode($data));
    }

    public function test_put_reminder_by_id(): void
    {
        // Exec
        $body = [
            "inventory_id" => $this->inventoryId,
            "reminder_desc" => "Restock at https://tokopedia.link/rBfBm3vVDIbBeli 2 boleh",
            "reminder_type" => "Every Week",
            "reminder_context" => "Every Sunday"
        ];
        $response = $this->httpClient->put($this->reminderId, [
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
        $this->assertEquals('reminder updated', $data['message']);

        Audit::auditRecordText("Test - PUT Reminder By ID", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - PUT Reminder By ID", "TC-XXX", 'TC-XXX test_put_reminder_by_id', json_encode($data));
    }

    public function test_get_reminder_history(): void
    {
        // Exec
        $response = $this->httpClient->get("history", [
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
            $check_object = ['id','inventory_name','reminder_desc','reminder_type','reminder_context','last_execute'];
            foreach ($check_object as $col) {
                $this->assertArrayHasKey($col, $dt);
            }

            $check_not_null_str = ['id','inventory_name','reminder_desc','reminder_type','reminder_context','last_execute'];
            foreach ($check_not_null_str as $col) {
                $this->assertNotNull($dt[$col]);
                $this->assertIsString($dt[$col]);
            }

            $this->assertEquals(36,strlen($dt['id']));
        }

        Audit::auditRecordText("Test - Get Reminder History", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Reminder History", "TC-XXX", 'TC-XXX test_get_reminder_history', json_encode($data));
    }
}
