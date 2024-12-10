<?php

namespace Tests\Feature;

use GuzzleHttp\Client;

trait LoginHelperTrait
{
    public function login_trait(): string
    {
        $httpClient = new Client([
            'base_uri' => 'http://127.0.0.1:8000/',
            'http_errors' => false
        ]);

        $param = [
            'username' => 'flazefy',
            'password' => 'nopass123'
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
