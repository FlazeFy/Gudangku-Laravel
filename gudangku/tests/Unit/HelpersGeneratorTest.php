<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Tests\TestCase;

// Helpers
use App\Helpers\Audit;
use App\Helpers\Generator;

class HelpersGeneratorTest extends TestCase
{ 
    protected $httpClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = new Client([
            'base_uri' => 'http://127.0.0.1:8000/',
            'http_errors' => false
        ]);
    }

    public function test_generator_get_uuid(): void
    {   
        $check_length = 36;

        // Exec
        $check = Generator::getUUID();
        $check2 = Generator::getUUID();

        // Test Parameter
        $this->assertIsString($check);
        $this->assertEquals($check_length, strlen($check));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/',$check);
        // Check if UUID is random
        $this->assertNotEquals($check,$check2);

        Audit::auditRecordText("Test - Generator Helper", "Generator-getUUID", "Result : ".$check);
        Audit::auditRecordSheet("Test - Generator Helper", "Generator-getUUID",'',$check);
    }

    public function test_generator_get_user_email(): void
    {   
        $check_length = 36;

        // Exec
        $response = $this->httpClient->post("/api/v1/login", [
            'json' => [
                'username' => 'flazefy',
                'password' => 'nopass123',
            ]
        ]);

        $data = json_decode($response->getBody(), true);

        // Test Parameter
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('id', $data['result']);

        $user_id = $data['result']['id'];

        // Exec
        $check = Generator::getUserEmail($user_id);

        // Test Parameter
        $this->assertIsString($check);

        Audit::auditRecordText("Test - Generator Helper", "Generator-getUserEmail", "Request : $user_id, Result: $check");
        Audit::auditRecordSheet("Test - Generator Helper", "Generator-getUserEmail", $user_id,$check);
    }

    public function test_generator_getToken(){
        // Sample
        $len = 6;
        $token = Generator::getTokenValidation($len);
        $token2 = Generator::getTokenValidation($len);

        // Test Parameter
        $this->assertIsString($token);
        $this->assertEquals(6,strlen($token));
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{6}$/', $token);
        // Check if Token is random
        $this->assertNotEquals($token,$token2);

        Audit::auditRecordText("Test - Generator Helper", "Generator-getTokenValidation", "Result : ".$token);
        Audit::auditRecordSheet("Test - Generator Helper", "Generator-getTokenValidation",'',$token);
    }

    public function test_generator_getRandomDate(){
        // Sample
        $start = '2023-01-01 00:00:00';
        $date = Generator::getRandomDate(0);
        $date2 = Generator::getRandomDate(0);
        $date_null = Generator::getRandomDate(1);

        // Test Parameter
        $this->assertIsString($date);
        $this->assertNull($date_null);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $date);
        $this->assertGreaterThanOrEqual(strtotime($start), strtotime($date));
        // Check if Token is random
        $this->assertNotEquals($date,$date2);

        Audit::auditRecordText("Test - Generator Helper", "Generator-getRandomDate", "Result : ".$date);
        Audit::auditRecordSheet("Test - Generator Helper", "Generator-getRandomDate",'',$date);
    }

    public function test_generator_getDocTemplate(){
        // Sample
        $type = "footer";
        $html_doc = Generator::getDocTemplate($type);

        // Test Parameter
        // Extract datetime
        preg_match('/Generated at ([0-9]{2} [A-Za-z]{3} [0-9]{4} [0-9]{2}:[0-9]{2})/', $html_doc, $matches);
        $this->assertNotEmpty($matches);
        // Check format
        $datetime = $matches[1];
        $dt = \DateTime::createFromFormat('d M Y H:i', $datetime);
        $this->assertEquals($datetime, $dt->format('d M Y H:i'));

        Audit::auditRecordText("Test - Generator Helper", "Generator-getDocTemplate", "Result : ".$html_doc);
        Audit::auditRecordSheet("Test - Generator Helper", "Generator-getDocTemplate",'',$html_doc);
    }
}
