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
            ['uri' => '/', 'status' => 200, 'auth' => false, 'type' => 'public'],
            ['uri' => '/login', 'status' => 200, 'auth' => false, 'type' => 'public'],
            ['uri' => '/register', 'status' => 200, 'auth' => false, 'type' => 'public'],
            ['uri' => '/features', 'status' => 200, 'auth' => false, 'type' => 'public'],
            ['uri' => '/help', 'status' => 200, 'auth' => false, 'type' => 'public'],
            ['uri' => '/lend/1', 'status' => 200, 'auth' => false, 'type' => 'public'],
            ['uri' => '/embed/distribution_inventory_category', 'status' => 200, 'auth' => false, 'type' => 'public'],
            ['uri' => '/embed/distribution_inventory_room', 'status' => 200, 'auth' => false, 'type' => 'public'],
            ['uri' => '/embed/distribution_inventory_favorite', 'status' => 200, 'auth' => false, 'type' => 'public'],
            ['uri' => '/embed/distribution_inventory_merk', 'status' => 200, 'auth' => false, 'type' => 'public'],
            ['uri' => '/embed/inventory_created_per_month/2024', 'status' => 200, 'auth' => false, 'type' => 'public'],
            ['uri' => '/embed/report_created_per_month/2024', 'status' => 200, 'auth' => false, 'type' => 'public'],
            ['uri' => '/embed/activity_per_month/2024', 'status' => 200, 'auth' => false, 'type' => 'public'],
            ['uri' => '/doc/report/1', 'status' => 200, 'auth' => false, 'type' => 'public'],
            ['uri' => '/doc/report/1/custom', 'status' => 200, 'auth' => false, 'type' => 'public'],
            ['uri' => '/analyze/report/1', 'status' => 200, 'auth' => false, 'type' => 'public'],
    
            // Private Route - Guest (should redirect)
            ['uri' => '/chat', 'status' => 200, 'auth' => false, 'type' => 'private'],
            ['uri' => '/inventory', 'status' => 200, 'auth' => false, 'type' => 'private'],
            ['uri' => '/stats', 'status' => 200, 'auth' => false, 'type' => 'private'],
            ['uri' => '/history', 'status' => 200, 'auth' => false, 'type' => 'private'],
            ['uri' => '/profile', 'status' => 200, 'auth' => false, 'type' => 'private'],
            ['uri' => '/user', 'status' => 200, 'auth' => false, 'type' => 'private'],
            ['uri' => '/error', 'status' => 200, 'auth' => false, 'type' => 'private'],
            ['uri' => '/reminder', 'status' => 200, 'auth' => false, 'type' => 'private'],
            ['uri' => '/calendar', 'status' => 200, 'auth' => false, 'type' => 'private'],
            ['uri' => '/report', 'status' => 200, 'auth' => false, 'type' => 'private'],
            ['uri' => '/room/3d', 'status' => 200, 'auth' => false, 'type' => 'private'],
            ['uri' => '/room/2d', 'status' => 200, 'auth' => false, 'type' => 'private'],
    
            // Private Route - Authenticated
            ['uri' => '/chat', 'status' => 200, 'auth' => true, 'type' => 'private'],
            ['uri' => '/inventory', 'status' => 200, 'auth' => true, 'type' => 'private'],
            ['uri' => '/inventory/by/list/all', 'status' => 200, 'auth' => true, 'type' => 'private'],
            ['uri' => '/inventory/add', 'status' => 200, 'auth' => true, 'type' => 'private'],
            ['uri' => '/inventory/edit/1', 'status' => 200, 'auth' => true, 'type' => 'private'],
            ['uri' => '/stats', 'status' => 200, 'auth' => true, 'type' => 'private'],
            ['uri' => '/history', 'status' => 200, 'auth' => true, 'type' => 'private'],
            ['uri' => '/profile', 'status' => 200, 'auth' => true, 'type' => 'private'],
            ['uri' => '/user', 'status' => 200, 'auth' => true, 'type' => 'private'],
            ['uri' => '/error', 'status' => 200, 'auth' => true, 'type' => 'private'],
            ['uri' => '/reminder', 'status' => 200, 'auth' => true, 'type' => 'private'],
            ['uri' => '/calendar', 'status' => 200, 'auth' => true, 'type' => 'private'],
            ['uri' => '/report', 'status' => 200, 'auth' => true, 'type' => 'private'],
            ['uri' => '/report/add', 'status' => 200, 'auth' => true, 'type' => 'private'],
            ['uri' => '/report/detail/1', 'status' => 200, 'auth' => true, 'type' => 'private'],
            ['uri' => '/room/3d', 'status' => 200, 'auth' => true, 'type' => 'private'],
            ['uri' => '/room/2d', 'status' => 200, 'auth' => true, 'type' => 'private'],
            ['uri' => '/doc/layout/room1', 'status' => 200, 'auth' => true, 'type' => 'private'],
            ['uri' => '/doc/layout/room1/custom', 'status' => 200, 'auth' => true, 'type' => 'private'],
            ['uri' => '/doc/inventory/1', 'status' => 200, 'auth' => true, 'type' => 'private'],
            ['uri' => '/doc/inventory/1/custom', 'status' => 200, 'auth' => true, 'type' => 'private'],
            ['uri' => '/analyze/layout/room1', 'status' => 200, 'auth' => true, 'type' => 'private'],
            ['uri' => '/analyze/inventory/1', 'status' => 200, 'auth' => true, 'type' => 'private'],
        ];
    
        foreach ($routes as $route) {
            auth()->logout();
    
            if ($route['auth']) $this->actingAs($user);
    
            $start = microtime(true);
            $response = $this->followingRedirects(false)->get($route['uri']);
            $duration = microtime(true) - $start;

            // Check redirect to /login for unauthenticated private routes
            if ($route['type'] === 'private' && !$route['auth']) {
                $response = $this->followingRedirects(true)->get($route['uri']);
                $response->assertStatus(200);
                $this->assertEquals(url('/login'), url()->current());
            } else {
                // Status check
                $response = $this->followingRedirects(false)->get($route['uri']);
                $response->assertStatus($route['status']);
            }
    
            // Prevent silent 500
            $this->assertNotEquals(500, $response->status(), "Route crashed: {$route['uri']}");
    
            // Performance guard
            $this->assertTrue($duration < 1.5, "Slow route: {$route['uri']} ({$duration}s)");
    
            $authLabel = $route['auth'] ? 'auth' : 'guest';
            $ms = round($duration * 1000, 4);
            $line = "{$ms}ms | {$route['status']} | [{$authLabel}] {$route['uri']}";
            $summary .= $line . "\n";
        }
    
        Audit::auditRecordText("Test - Site Test", "All Web Routes", $summary);
        Audit::auditRecordSheet("Test - Site Test", "All Web Routes", 'ALL', $summary);
    
        $this->assertNotEmpty($summary);
    }

    public function test_all_api_routes() {
        $summary = '';
    
        $routes = [
            // Public Route
            ['uri' => '/api/v1/login', 'type' => 'public', 'method' => 'POST'],
            ['uri' => '/api/v1/register/token', 'type' => 'public', 'method' => 'POST'],
            ['uri' => '/api/v1/register/account', 'type' => 'public', 'method' => 'POST'],
            ['uri' => '/api/v1/register/regen_token', 'type' => 'public', 'method' => 'POST'],
            ['uri' => '/api/v1/lend/inventory/1', 'type' => 'public', 'method' => 'GET'],
            ['uri' => '/api/v1/lend/inventory/1', 'type' => 'public', 'method' => 'POST'],
            ['uri' => '/api/v1/dictionary/type/inventory_category', 'type' => 'public', 'method' => 'GET'],
            ['uri' => '/api/v1/stats/inventory/total_created_per_month/2024', 'type' => 'public', 'method' => 'GET'],
            ['uri' => '/api/v1/stats/inventory/total_by_category/price', 'type' => 'public', 'method' => 'GET'],
            ['uri' => '/api/v1/stats/inventory/total_by_room/price', 'type' => 'public', 'method' => 'GET'],
            ['uri' => '/api/v1/stats/inventory/total_by_favorite/price', 'type' => 'public', 'method' => 'GET'],
            ['uri' => '/api/v1/stats/inventory/total_by_merk/price', 'type' => 'public', 'method' => 'GET'],
            ['uri' => '/api/v1/stats/inventory/favorite_inventory_comparison', 'type' => 'public', 'method' => 'GET'],
            ['uri' => '/api/v1/stats/inventory/low_capacity_inventory_comparison', 'type' => 'public', 'method' => 'GET'],
            ['uri' => '/api/v1/stats/report/total_created_per_month/2024', 'type' => 'public', 'method' => 'GET'],
            ['uri' => '/api/v1/stats/history/total_activity_per_month/2024', 'type' => 'public', 'method' => 'GET'],

            // Private Route
            ['uri' => '/api/v1/logout', 'type' => 'private', 'method' => 'POST'],
            ['uri' => '/api/v1/inventory', 'type' => 'private', 'method' => 'GET'],
            ['uri' => '/api/v1/inventory', 'type' => 'private', 'method' => 'POST'],
            ['uri' => '/api/v1/inventory/catalog', 'type' => 'private', 'method' => 'GET'],
            ['uri' => '/api/v1/inventory/catalog/room/all', 'type' => 'private', 'method' => 'GET'],
            ['uri' => '/api/v1/inventory/search/by_room_storage/room1/storage1', 'type' => 'private', 'method' => 'GET'],
            ['uri' => '/api/v1/inventory/list', 'type' => 'private', 'method' => 'GET'],
            ['uri' => '/api/v1/inventory/room', 'type' => 'private', 'method' => 'GET'],
            ['uri' => '/api/v1/inventory/calendar', 'type' => 'private', 'method' => 'GET'],
            ['uri' => '/api/v1/inventory/analyze/1', 'type' => 'private', 'method' => 'GET'],
            ['uri' => '/api/v1/inventory/layout/room1', 'type' => 'private', 'method' => 'GET'],
            ['uri' => '/api/v1/inventory/layout/room1/doc', 'type' => 'private', 'method' => 'GET'],
            ['uri' => '/api/v1/inventory/layout', 'type' => 'private', 'method' => 'POST'],
            ['uri' => '/api/v1/inventory/delete_layout/1/1', 'type' => 'private', 'method' => 'DELETE'],
            ['uri' => '/api/v1/inventory/detail/1', 'type' => 'private', 'method' => 'GET'],
            ['uri' => '/api/v1/inventory/detail/1/doc', 'type' => 'private', 'method' => 'GET'],
            ['uri' => '/api/v1/inventory/delete/1', 'type' => 'private', 'method' => 'DELETE'],
            ['uri' => '/api/v1/inventory/destroy/1', 'type' => 'private', 'method' => 'DELETE'],
            ['uri' => '/api/v1/inventory/fav_toggle/1', 'type' => 'private', 'method' => 'PUT'],
            ['uri' => '/api/v1/inventory/recover/1', 'type' => 'private', 'method' => 'PUT'],
            ['uri' => '/api/v1/inventory/edit/1', 'type' => 'private', 'method' => 'PUT'],
            ['uri' => '/api/v1/inventory/edit_image/1', 'type' => 'private', 'method' => 'POST'],
            ['uri' => '/api/v1/inventory/edit_layout/1', 'type' => 'private', 'method' => 'PUT'],
            ['uri' => '/api/v1/stats/dashboard', 'type' => 'private', 'method' => 'GET'],
            ['uri' => '/api/v1/stats/inventory/most_expensive/all', 'type' => 'private', 'method' => 'GET'],
            ['uri' => '/api/v1/stats/inventory/tree_map', 'type' => 'private', 'method' => 'GET'],
            ['uri' => '/api/v1/stats/report/total_spending_per_month/2024', 'type' => 'private', 'method' => 'GET'],
            ['uri' => '/api/v1/stats/report/total_used_per_month/2024', 'type' => 'private', 'method' => 'GET'],
            ['uri' => '/api/v1/stats/user/last_login', 'type' => 'private', 'method' => 'GET'],
            ['uri' => '/api/v1/stats/user/leaderboard', 'type' => 'private', 'method' => 'GET'],
            ['uri' => '/api/v1/reminder/mark', 'type' => 'private', 'method' => 'GET'],
            ['uri' => '/api/v1/reminder/history', 'type' => 'private', 'method' => 'GET'],
            ['uri' => '/api/v1/reminder', 'type' => 'private', 'method' => 'POST'],
            ['uri' => '/api/v1/reminder/copy', 'type' => 'private', 'method' => 'POST'],
            ['uri' => '/api/v1/reminder/re_remind', 'type' => 'private', 'method' => 'POST'],
            ['uri' => '/api/v1/reminder/destroy/1', 'type' => 'private', 'method' => 'DELETE'],
            ['uri' => '/api/v1/reminder/1', 'type' => 'private', 'method' => 'PUT'],
            ['uri' => '/api/v1/analyze/report', 'type' => 'private', 'method' => 'POST'],
            ['uri' => '/api/v1/analyze/bill', 'type' => 'private', 'method' => 'POST'],
            ['uri' => '/api/v1/analyze/report/new', 'type' => 'private', 'method' => 'POST'],
            ['uri' => '/api/v1/lend/qr', 'type' => 'private', 'method' => 'POST'],
            ['uri' => '/api/v1/lend/qr', 'type' => 'private', 'method' => 'GET'],
            ['uri' => '/api/v1/lend/qr/history', 'type' => 'private', 'method' => 'GET'],
            ['uri' => '/api/v1/lend/update_status/1', 'type' => 'private', 'method' => 'PUT'],
            ['uri' => '/api/v1/history', 'type' => 'private', 'method' => 'GET'],
            ['uri' => '/api/v1/history/destroy/1', 'type' => 'private', 'method' => 'DELETE'],
            ['uri' => '/api/v1/error', 'type' => 'private', 'method' => 'GET'],
            ['uri' => '/api/v1/error/destroy/1', 'type' => 'private', 'method' => 'DELETE'],
            ['uri' => '/api/v1/dictionary', 'type' => 'private', 'method' => 'POST'],
            ['uri' => '/api/v1/dictionary/1', 'type' => 'private', 'method' => 'DELETE'],
            ['uri' => '/api/v1/report', 'type' => 'private', 'method' => 'GET'],
            ['uri' => '/api/v1/report/inventory/1', 'type' => 'private', 'method' => 'GET'],
            ['uri' => '/api/v1/report', 'type' => 'private', 'method' => 'POST'],
            ['uri' => '/api/v1/report/item/1', 'type' => 'private', 'method' => 'POST'],
            ['uri' => '/api/v1/report/report_image/1', 'type' => 'private', 'method' => 'POST'],
            ['uri' => '/api/v1/report/report_image/destroy/1/1', 'type' => 'private', 'method' => 'DELETE'],
            ['uri' => '/api/v1/report/detail/item/1', 'type' => 'private', 'method' => 'GET'],
            ['uri' => '/api/v1/report/detail/item/1/doc', 'type' => 'private', 'method' => 'GET'],
            ['uri' => '/api/v1/report/update/report/1', 'type' => 'private', 'method' => 'PUT'],
            ['uri' => '/api/v1/report/update/report_item/1', 'type' => 'private', 'method' => 'PUT'],
            ['uri' => '/api/v1/report/update/report_split/1', 'type' => 'private', 'method' => 'PUT'],
            ['uri' => '/api/v1/report/destroy/item/1', 'type' => 'private', 'method' => 'DELETE'],
            ['uri' => '/api/v1/report/destroy/report/1', 'type' => 'private', 'method' => 'DELETE'],
            ['uri' => '/api/v1/user', 'type' => 'private', 'method' => 'GET'],
            ['uri' => '/api/v1/user/my_year', 'type' => 'private', 'method' => 'GET'],
            ['uri' => '/api/v1/user/my_profile', 'type' => 'private', 'method' => 'GET'],
            ['uri' => '/api/v1/user/update_telegram_id', 'type' => 'private', 'method' => 'PUT'],
            ['uri' => '/api/v1/user/update_profile', 'type' => 'private', 'method' => 'PUT'],
            ['uri' => '/api/v1/user/validate_telegram_id', 'type' => 'private', 'method' => 'PUT'],
            ['uri' => '/api/v1/user/update_timezone_fcm', 'type' => 'private', 'method' => 'PUT'],
            ['uri' => '/api/v1/user/destroy/1', 'type' => 'private', 'method' => 'DELETE'],
        ];
    
        foreach ($routes as $route) {
            auth()->logout();
    
            $start = microtime(true);
            $method = $route['method'] ?? 'GET';
            $response = $this->followingRedirects(false)->$method($route['uri']);
            $duration = microtime(true) - $start;
    
            // Check if return 401 status code if access protected route without auth header
            if ($route['type'] === 'public') {
                $this->assertNotEquals(401, $response->status(), "Public route returned 401: {$route['uri']}");
            } else {
                $response->assertStatus(401);
                $response->assertJson([
                    'status' => 'failed',
                    'message' => 'you need to include the authorization token from login',
                ]);
            }
    
            // Prevent silent 500
            $this->assertNotEquals(500, $response->status(), "Route crashed: {$route['uri']}");

            // Performance guard
            $this->assertTrue($duration < 1.5, "Slow route: {$route['uri']} ({$duration}s)");
    
            $ms = round($duration * 1000, 4);
            $line = "{$ms}ms | {$response->status()} | [{$route['type']}] {$route['uri']}";
            $summary .= $line . "\n";
        }
    
        // Audit Test
        Audit::auditRecordText("Test - Smoke Site Test", "All API Routes", $summary);
        Audit::auditRecordSheet("Test - Smoke Site Test", "All API Routes", 'ALL', $summary);
    
        $this->assertNotEmpty($summary);
    }
}
