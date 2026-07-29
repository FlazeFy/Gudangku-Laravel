<?php

namespace Tests\Integration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use GuzzleHttp\Client;
use Tests\TestCase;

// Helper
use App\Helpers\Audit;
use App\Helpers\TestDataReader;

class UserAdminTest extends TestCase
{
    protected $httpClient;
    protected string $token;
    use LoginHelperTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = new Client([
            'base_uri' => 'http://127.0.0.1:8000/api/v1/',
            'http_errors' => false
        ]);

        // Pre-Condition: User already sign in
        $this->token = $this->login_trait("admin");
    }

    public function test_post_re_remind(): void
    {
        // Exec
        $body = [
            "reminder_id" => $this->reminderId
        ];
        $response = $this->httpClient->post("reminder/re_remind", [
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
        $this->assertEquals('reminder re-executed', $data['message']);

        Audit::auditRecordText("Test - Post Re-Remind", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Post Re-Remind", "TC-XXX", 'TC-XXX test_post_re_remind', json_encode($data));
    }

    public function test_get_reminder_mark(): void
    {
        // Exec
        $token = $this->login_trait("admin");
        $response = $this->httpClient->get("reminder/mark", [
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

    public function test_get_leaderboard(): void
    {
        // Exec
        $token = $this->login_trait("admin");
        $response = $this->httpClient->get("user/leaderboard", [
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

    public function test_get_last_login_user(): void
    {
        // Exec
        $token = $this->login_trait("admin");
        $response = $this->httpClient->get("user/last_login", [
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

}
