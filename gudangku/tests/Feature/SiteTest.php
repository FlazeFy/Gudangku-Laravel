<?php

namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

// Helper
use App\Helpers\Audit;
use App\Models\UserModel;

class SiteTest extends TestCase
{
    public function test_all_web_routes() {
        $summary = '';
        $user = UserModel::factory()->create();

        $routes = [
            // Public Route
            ['uri' => '/', 'status' => 200, 'auth' => false],
            ['uri' => '/login', 'status' => 200, 'auth' => false],
            ['uri' => '/register', 'status' => 200, 'auth' => false],
            ['uri' => '/features', 'status' => 200, 'auth' => false],
            ['uri' => '/help', 'status' => 200, 'auth' => false],
            ['uri' => '/lend/1', 'status' => 200, 'auth' => false],
            ['uri' => '/embed/distribution_inventory_category', 'status' => 200, 'auth' => false],
            ['uri' => '/embed/distribution_inventory_room', 'status' => 200, 'auth' => false],
            ['uri' => '/embed/distribution_inventory_favorite', 'status' => 200, 'auth' => false],
            ['uri' => '/embed/distribution_inventory_merk', 'status' => 200, 'auth' => false],
            ['uri' => '/embed/inventory_created_per_month/2024', 'status' => 200, 'auth' => false],
            ['uri' => '/embed/report_created_per_month/2024', 'status' => 200, 'auth' => false],
            ['uri' => '/embed/activity_per_month/2024', 'status' => 200, 'auth' => false],
            ['uri' => '/doc/report/1', 'status' => 200, 'auth' => false],
            ['uri' => '/doc/report/1/custom', 'status' => 200, 'auth' => false],
            ['uri' => '/analyze/report/1', 'status' => 200, 'auth' => false],

            // Private Route
            ['uri' => '/chat', 'status' => 200, 'auth' => false],
            ['uri' => '/inventory', 'status' => 200, 'auth' => false],
            ['uri' => '/stats', 'status' => 200, 'auth' => false],
            ['uri' => '/history', 'status' => 200, 'auth' => false],
            ['uri' => '/profile', 'status' => 200, 'auth' => false],
            ['uri' => '/user', 'status' => 200, 'auth' => false],
            ['uri' => '/error', 'status' => 200, 'auth' => false],
            ['uri' => '/reminder', 'status' => 200, 'auth' => false],
            ['uri' => '/calendar', 'status' => 200, 'auth' => false],
            ['uri' => '/report', 'status' => 200, 'auth' => false],
            ['uri' => '/room/3d', 'status' => 200, 'auth' => false],
            ['uri' => '/room/2d', 'status' => 200, 'auth' => false],

            ['uri' => '/chat', 'status' => 200, 'auth' => true],
            ['uri' => '/inventory', 'status' => 200, 'auth' => true],
            ['uri' => '/inventory/by/list/all', 'status' => 200, 'auth' => true],
            ['uri' => '/inventory/add', 'status' => 200, 'auth' => true],
            ['uri' => '/inventory/edit/1', 'status' => 200, 'auth' => true],
            ['uri' => '/stats', 'status' => 200, 'auth' => true],
            ['uri' => '/history', 'status' => 200, 'auth' => true],
            ['uri' => '/profile', 'status' => 200, 'auth' => true],
            ['uri' => '/user', 'status' => 200, 'auth' => true],
            ['uri' => '/error', 'status' => 200, 'auth' => true],
            ['uri' => '/reminder', 'status' => 200, 'auth' => true],
            ['uri' => '/calendar', 'status' => 200, 'auth' => true],
            ['uri' => '/report', 'status' => 200, 'auth' => true],
            ['uri' => '/report/add', 'status' => 200, 'auth' => true],
            ['uri' => '/report/detail/1', 'status' => 200, 'auth' => true],
            ['uri' => '/room/3d', 'status' => 200, 'auth' => true],
            ['uri' => '/room/2d', 'status' => 200, 'auth' => true],
            ['uri' => '/doc/layout/room1', 'status' => 200, 'auth' => true],
            ['uri' => '/doc/layout/room1/custom', 'status' => 200, 'auth' => true],
            ['uri' => '/doc/inventory/1', 'status' => 200, 'auth' => true],
            ['uri' => '/doc/inventory/1/custom', 'status' => 200, 'auth' => true],
            ['uri' => '/analyze/layout/room1', 'status' => 200, 'auth' => true],
            ['uri' => '/analyze/inventory/1', 'status' => 200, 'auth' => true],
        ];

        foreach ($routes as $route) {
            // Reset auth state each loop
            auth()->logout();

            if ($route['auth']) $this->actingAs($user);

            $start = microtime(true);
            $response = $this->followingRedirects(false)->get($route['uri']);
            $duration = microtime(true) - $start;

            // Status check
            $response->assertStatus($route['status']);

            // Check redirect for private route (guest)
            if (!$route['auth'] && $route['status'] === 200) $response->assertRedirect('/login');

            // Prevent silent 500
            $this->assertNotEquals(500, $response->status(), "Route crashed: {$route['uri']}");

            // Performance guard
            $this->assertTrue($duration < 1.5, "Slow route: {$route['uri']} ({$duration}s)");

            $line = round($duration, 4) . "s | {$route['status']} | {$route['uri']}";
            $summary .= $line . "\n";
        }

        Audit::auditRecordText("Test - Site Test", "All Web Routes", $summary);
        Audit::auditRecordSheet("Test - Site Test", "All Web Routes", 'ALL', $summary);

        $this->assertNotEmpty($summary);
    }
}
