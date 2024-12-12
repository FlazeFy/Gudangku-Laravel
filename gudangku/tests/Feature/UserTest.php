<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use GuzzleHttp\Client;
use Tests\TestCase;
use App\Helpers\Audit;

class UserTest extends TestCase
{
    protected $httpClient;
    use LoginHelperTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = new Client([
            'base_uri' => 'http://127.0.0.1:8000/api/v1/user/',
            'http_errors' => false
        ]);
    }

    public function test_get_my_profile(): void
    {
        // Exec
        $token = $this->login_trait();
        $response = $this->httpClient->get("my_profile", [
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

        $check_not_null_str = ['id','username','email','created_at','role'];
        foreach ($check_not_null_str as $col) {
            $this->assertNotNull($data['data'][$col]);
            $this->assertIsString($data['data'][$col]);
        }

        $this->assertContains($data['data']['role'], ['user', 'admin']);
        $this->assertContains($data['data']['telegram_is_valid'], [0, 1]);
        if(!is_null($data['data']['telegram_user_id'])){
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
        $token = $this->login_trait();
        $body = [
            "timezone" => "+02:00",
            "firebase_fcm_token" => "ddLEuWR2Q_isCmzHTM8UR4:APA91bEmY8TDmH3ZJtKgXw95wFDKLr53FGA2JArDTiN4jzSWxiGzf9VUECYN2oeqYV__c7Yz9kj8kPqykIP_6N-LaVRUhDXJX3ludLcMSGq36Hn2uh7onMgzDFvaXo3yG37LIWFLdr6f"
        ];
        $response = $this->httpClient->put("update_timezone_fcm", [
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
        $this->assertEquals('timezone and firebase fcm token has been updated',$data['message']);

        Audit::auditRecordText("Test - Put Timezone FCM", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Put Timezone FCM", "TC-XXX", 'TC-XXX test_put_timezone_fcm', json_encode($data));
    }
}
