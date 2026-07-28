<?php

namespace Tests\Integration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use GuzzleHttp\Client;
use Tests\TestCase;

// Helper
use App\Helpers\Audit;
use App\Helpers\TestDataReader;

class UserTest extends TestCase
{
    protected $httpClient;
    protected $token;
    use LoginHelperTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = new Client([
            'base_uri' => 'http://127.0.0.1:8000/api/v1/user/',
            'http_errors' => false
        ]);

        // Pre-Condition: User already sign in
        $this->token = $this->login_trait("user");
    }

    public function test_get_my_profile(): void
    {
        // Exec
        $response = $this->httpClient->get("my_profile", [
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

        $check_not_null_str = ['id','username','email','created_at','role'];
        foreach ($check_not_null_str as $col) {
            $this->assertNotNull($data['data'][$col]);
            $this->assertIsString($data['data'][$col]);
        }

        $this->assertContains($data['data']['role'], ['user', 'admin']);
        $this->assertContains($data['data']['telegram_is_valid'], [0, 1]);
        if (!is_null($data['data']['telegram_user_id'])) {
            $this->assertIsString($data['data']['telegram_user_id']);
        } else {
            $this->assertEquals(0,$data['data']['telegram_user_id']);
        }

        Audit::auditRecordText("Test - Get My Profile", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get My Profile", "TC-XXX", 'TC-XXX test_get_my_profile', json_encode($data));
    }

    public function test_put_timezone_fcm(): void
    {
        // Exec
        $body = [
            "timezone" => "+02:00",
            "firebase_fcm_token" => "ddLEuWR2Q_isCmzHTM8UR4:APA91bEmY8TDmH3ZJtKgXw95wFDKLr53FGA2JArDTiN4jzSWxiGzf9VUECYN2oeqYV__c7Yz9kj8kPqykIP_6N-LaVRUhDXJX3ludLcMSGq36Hn2uh7onMgzDFvaXo3yG37LIWFLdr6f"
        ];
        $response = $this->httpClient->put("update_timezone_fcm", [
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
        $this->assertEquals('timezone and firebase fcm token has been updated',$data['message']);

        Audit::auditRecordText("Test - Put Timezone FCM", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Put Timezone FCM", "TC-XXX", 'TC-XXX test_put_timezone_fcm', json_encode($data));
    }

    public function test_put_telegram_id(): void
    {
        // Exec
        $body = [
            "telegram_user_id" => "1317625977",
        ];
        $response = $this->httpClient->put("update_telegram_id", [
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
        $this->assertEquals('telegram id updated! and validation has been sended to you',$data['message']);

        // Get token from email alternative
        $userId = TestDataReader::getValue("user_id");
        $response = $this->httpClient->get('/api/v1/user/validate_request/telegram_id_validation/'.$userId, [
            'headers' => [
                'X-API-KEY' => env('TESTING_API_KEY'),
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        // Store token
        TestDataReader::setValue('telegram_id_validation_token', $data['data']);

        Audit::auditRecordText("Test - Put Telegram ID", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Put Telegram ID", "TC-XXX", 'TC-XXX test_put_telegram_id', json_encode($data));
    }

    public function test_put_validate_telegram_id(): void
    {
        // Pre-Condition: User already request a token
        $telegramIdValidationToken = TestDataReader::getValue("telegram_id_validation_token");

        $body = [
            "request_context" => $telegramIdValidationToken,
        ];

        // Exec
        $response = $this->httpClient->put("validate_telegram_id", [
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
        $this->assertEquals('telegram id has been validated',$data['message']);

        Audit::auditRecordText("Test - Put Validate Telegram ID", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Put Validate Telegram ID", "TC-XXX", 'TC-XXX test_put_validate_telegram_id', json_encode($data));
    }

    public function test_put_update_profile(): void
    {
        // Exec
        $body = [
            "email" => "flazen.edu@gmail.com",
            "username" => "flazefy"
        ];
        $response = $this->httpClient->put("update_profile", [
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
        $this->assertEquals('profile updated',$data['message']);

        // Store updated data
        TestDataReader::setValue('username', $body['username']);
        TestDataReader::setValue('email', $body['email']);

        Audit::auditRecordText("Test - Put Update Profile", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Put Update Profile", "TC-XXX", 'TC-XXX test_put_update_profile', json_encode($data));
    }

    public function test_get_all_user(): void
    {
        // Exec
        $token = $this->login_trait("admin");
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
            $check_object = ['id','username','email','telegram_user_id','telegram_is_valid','firebase_fcm_token','line_user_id','phone','timezone','created_at','updated_at'];
            foreach ($check_object as $col) {
                $this->assertArrayHasKey($col, $dt);
            }

            $check_not_null_str = ['id','username','email','created_at'];
            foreach ($check_not_null_str as $col) {
                $this->assertNotNull($dt[$col]);
                $this->assertIsString($dt[$col]);
            }

            $check_nullable_str = ['telegram_user_id','firebase_fcm_token','line_user_id','phone','timezone','updated_at'];
            foreach ($check_nullable_str as $col) {
                if (!is_null($dt[$col])) {
                    $this->assertNotNull($dt[$col]);
                    $this->assertIsString($dt[$col]);
                }
            }

            $this->assertContains($dt['telegram_is_valid'], [0, 1]);
        }

        Audit::auditRecordText("Test - Get All User", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get All User", "TC-XXX", 'TC-XXX test_get_all_user', json_encode($data));
    }

    public function test_get_content_year(): void
    {
        // Exec
        $response = $this->httpClient->get("my_year", [
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
            $this->assertArrayHasKey('year', $dt);
            $this->assertNotNull($dt['year']);
            $this->assertIsInt($dt['year']);
            $this->assertGreaterThan(0, $dt['year']);
        }

        Audit::auditRecordText("Test - Get Content Year", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Content Year", "TC-XXX", 'TC-XXX test_get_content_year', json_encode($data));
    }
}
