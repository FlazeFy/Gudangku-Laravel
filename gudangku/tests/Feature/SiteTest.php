<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Helpers\Audit;

class SiteTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_get_page(): void
    {
        $summary = '';
        $routes = [
            '/', 
            '/login', 
            '/add'
        ];
        
        foreach ($routes as $route) {
            $start = microtime(true);
            $response = $this->get($route);

            // Test Parameter
            $response->assertStatus(200);
            $res = Audit::countTime($start) . " on load $route\n";
            $summary .= $res;
            echo $res;
        }

        Audit::auditRecordText("Test - Site Test", "Get Page", $summary);
        Audit::auditRecordSheet("Test - Site Test", "Get Page", implode(",", $routes), $summary);
    }
}
