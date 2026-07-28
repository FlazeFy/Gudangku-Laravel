<?php

namespace Tests\Integration;
use GuzzleHttp\Client;

// Helper
use App\Helpers\TestDataReader;

trait LoginHelperTrait
{
    public function login_trait($role): string
    {
        // Read existing test account
        $testUser = [
            'username' => TestDataReader::getValue($role !== "admin" ? 'username' : 'admin_username'),
            'password' => TestDataReader::getValue($role !== "admin" ? 'password' : 'admin_password'),
        ];

        $httpClient = new Client([
            'base_uri' => 'http://127.0.0.1:8000/',
            'http_errors' => false
        ]);

        $param = [
            'username' => $testUser['username'],
            'password' => $testUser['password']
        ];
        $response = $httpClient->post("/api/v1/login", [
            'json' => $param
        ]);

        $data = json_decode($response->getBody(), true);

        if (!isset($data['token'])) {
            throw new \Exception("Login failed: Token not found");
        }

        return $data['token'];
    }
}
