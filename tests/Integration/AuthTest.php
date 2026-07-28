<?php

namespace Tests\Integration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use GuzzleHttp\Client;
use Tests\TestCase;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// Helper
use App\Helpers\Audit;
use App\Helpers\TestDataReader;
// Models
use App\Models\AdminModel;
use App\Models\UserModel;
use App\Models\InventoryModel;
use App\Models\ReportModel;
use App\Models\HistoryModel;
use App\Models\ReportItemModel;
use App\Models\ReminderModel;
use App\Models\FAQModel;

class AuthTest extends TestCase
{
    protected $httpClient;
    protected array $testUser;
    protected array $testAdmin;
    protected static bool $dbCleaned = false;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        TestDataReader::clear();
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (!self::$dbCleaned) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            FAQModel::truncate();
            HistoryModel::truncate();
            ReportItemModel::truncate();
            ReportModel::truncate();
            ReminderModel::truncate();
            InventoryModel::truncate();
            UserModel::truncate();
            AdminModel::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            self::$dbCleaned = true;
        }

        $this->httpClient = new Client([
            'base_uri' => 'http://127.0.0.1:8000/',
            'http_errors' => false
        ]);

        if (TestDataReader::getValue('register_token') === null) {
            // Create new user test account
            $this->testUser = UserModel::factory()->apiPayload()->raw();
            TestDataReader::setValue('username', $this->testUser['username']);
            TestDataReader::setValue('email', $this->testUser['email']);
            TestDataReader::setValue('password', 'nopass123');
        } else {
            // Read existing test account
            $this->testUser = [
                'username' => TestDataReader::getValue('username'),
                'email' => TestDataReader::getValue('email'),
                'password' => TestDataReader::getValue('password'),
            ];
        }
    }

    public function test_post_register_validation_token(): void
    {
        $body = [
            'username' => $this->testUser['username'],
            'email' => $this->testUser['email'],
        ];

        // Exec
        $response = $this->httpClient->post("/api/v1/register/token", [
            'json' => $body
        ]);

        $data = json_decode($response->getBody(), true);

        // Test Parameter
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('success', $data['status']);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals("the validation token has been sended to ".$body['email']." email account",$data['message']);

        Audit::auditRecordText("Test - Post Register Validation Token", "TC-XXX", "message : ".json_encode($data));
        Audit::auditRecordSheet("Test - Post Register Validation Token", "TC-XXX", 'TC-001 test_post_register_validation_token', json_encode($data));
    }

    public function test_post_regenerate_register_token(): void
    {
        $body = [
            'username' => $this->testUser['username'],
            'email' => $this->testUser['email'],
        ];

        // Exec
        $response = $this->httpClient->post("/api/v1/register/regen_token", [
            'json' => $body
        ]);

        $data = json_decode($response->getBody(), true);

        // Test Parameter
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('success', $data['status']);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals("the validation token has been sended to ".$body['email']." email account",$data['message']);

        // Get token from email alternative
        $response = $this->httpClient->get('/api/v1/user/validate_request/register/'.$body['username'], [
            'headers' => [
                'X-API-KEY' => env('TESTING_API_KEY'),
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        // Store token
        TestDataReader::setValue('register_token', $data['data']);

        Audit::auditRecordText("Test - Post Regenerate Register Token", "TC-XXX", "message : ".json_encode($data));
        Audit::auditRecordSheet("Test - Post Regenerate Register Token", "TC-XXX", 'TC-001 test_post_regenerate_register_token', json_encode($data));
    }

    public function test_post_validate_register_account(): void
    {
        // Pre-Condition: User already request for a register token
        $token = TestDataReader::getValue('register_token');

        $body = array_merge($this->testUser, [
            'token' => $token,
        ]);

        // Exec
        $response = $this->httpClient->post("/api/v1/register/account", [
            'json' => $body
        ]);

        $data = json_decode($response->getBody(), true);

        // Test Parameter
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('success', $data['status']);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('account is registered',$data['message']);

        Audit::auditRecordText("Test - Post Validate Register Account", "TC-XXX", "message : ".json_encode($data));
        Audit::auditRecordSheet("Test - Post Validate Register Account", "TC-XXX", 'TC-001 test_post_validate_register_account', json_encode($data));
    }

    public function test_post_login()
    {
        $body = [
            'username' => $this->testUser['username'],
            'password' => $this->testUser['password']
        ];

        // Exec
        $response = $this->httpClient->post("/api/v1/login", [
            'json' => $body
        ]);
        $data = json_decode($response->getBody(), true);

        // Test Parameter
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('token', $data);
        $this->assertArrayHasKey('role', $data);
        $this->assertArrayHasKey('message', $data);

        $check_object = ['id','username','email','telegram_user_id','telegram_is_valid','firebase_fcm_token','line_user_id','phone','timezone','created_at','updated_at'];
        foreach ($check_object as $col) {
            $this->assertArrayHasKey($col, $data['message']);
        }

        $check_not_null_str = ['id','username','email','created_at'];
        foreach ($check_not_null_str as $col) {
            $this->assertNotNull($col, $data['message'][$col]);
            $this->assertIsString($col, $data['message'][$col]);
        }

        $check_nullable_str = ['telegram_user_id','firebase_fcm_token','line_user_id','phone','timezone','updated_at'];
        foreach ($check_nullable_str as $col) {
            if (!is_null($data['message'][$col])) {
                $this->assertIsString($col, $data['message'][$col]);
            }
        }

        // Store user id
        TestDataReader::setValue('user_id', $data['message']['id']);

        Audit::auditRecordText("Test - Post Login", "TC-001", "Token : ".$data['token']);
        Audit::auditRecordSheet("Test - Post Login", "TC-001", json_encode($body), $data['token']);
        
        return $data['token'];
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        // Remove this later
        ValidateRequestModel::truncate();
    }
}
