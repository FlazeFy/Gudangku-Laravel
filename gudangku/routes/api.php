<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthApi\Commands as CommandAuthApi;
use App\Http\Controllers\Api\AuthApi\Queries as QueryAuthApi;
use App\Http\Controllers\Api\InventoryApi\Queries as QueriesInventoryController;
use App\Http\Controllers\Api\InventoryApi\Commands as CommandsInventoryController;
use App\Http\Controllers\Api\HistoryApi\Queries as QueriesHistoryController;
use App\Http\Controllers\Api\HistoryApi\Commands as CommandsHistoryController;
use App\Http\Controllers\Api\StatsApi\Queries as QueriesStatsController;
use App\Http\Controllers\Api\ReportApi\Queries as QueriesReportController;
use App\Http\Controllers\Api\ReportApi\Commands as CommandsReportController;
use App\Http\Controllers\Api\UserApi\Queries as QueriesUserController;
use App\Http\Controllers\Api\UserApi\Commands as CommandsUserController;
use App\Http\Controllers\Api\ReminderApi\Commands as CommandsReminderController;
use App\Http\Controllers\Api\ReminderApi\Queries as QueriesReminderController;
use App\Http\Controllers\Api\DictionaryApi\Queries as QueriesDictionaryController;
use App\Http\Controllers\Api\DictionaryApi\Commands as CommandsDictionaryController;
use App\Http\Controllers\Api\ErrorApi\Queries as QueriesErrorController;

######################### Public Route #########################

Route::post('/v1/login', [CommandAuthApi::class, 'login']);
Route::prefix('/v1/register')->group(function () {
    Route::post('/token', [CommandsUserController::class, 'get_register_validation_token']);
    Route::post('/account', [CommandsUserController::class, 'post_validate_register']);
    Route::post('/regen_token', [CommandsUserController::class, 'regenerate_register_token']);
});

######################### Private Route #########################

Route::get('/v1/logout', [QueryAuthApi::class, 'logout'])->middleware(['auth:sanctum']);

Route::prefix('/v1/inventory')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [QueriesInventoryController::class, 'get_all_inventory']);
    Route::get('/search/by_room_storage/{room}/{storage}', [QueriesInventoryController::class, 'get_inventory_by_storage']);
    Route::get('/list', [QueriesInventoryController::class, 'get_list_inventory']);
    Route::get('/room', [QueriesInventoryController::class, 'get_list_room']);
    Route::get('/calendar', [QueriesInventoryController::class, 'get_list_calendar']);
    Route::get('/analyze/{id}', [QueriesInventoryController::class, 'get_analyze_inventory']);
    Route::prefix('/layout/{room}')->group(function (){
        Route::get('/', [QueriesInventoryController::class, 'get_room_layout']);
        Route::get('/doc', [QueriesInventoryController::class, 'get_room_document']);
    });
    Route::prefix('/detail/{id}')->group(function (){
        Route::get('/', [QueriesInventoryController::class, 'get_inventory_by_id']);
        Route::get('/doc', [QueriesInventoryController::class, 'get_inventory_detail_document']);
    });
    Route::post('/', [CommandsInventoryController::class, 'post_inventory']);
    Route::post('/layout', [CommandsInventoryController::class, 'post_inventory_layout']);
    Route::delete('/delete_layout/{id}/{coor}', [CommandsInventoryController::class, 'hard_del_inventory_layout_by_id_coor']);
    Route::delete('/delete/{id}', [CommandsInventoryController::class, 'soft_delete_inventory_by_id']);
    Route::delete('/destroy/{id}', [CommandsInventoryController::class, 'hard_delete_inventory_by_id']);
    Route::put('/fav_toggle/{id}', [CommandsInventoryController::class, 'fav_toogle_inventory_by_id']);
    Route::put('/recover/{id}', [CommandsInventoryController::class, 'recover_inventory_by_id']);
    Route::put('/edit_image/{id}', [CommandsInventoryController::class, 'edit_image_by_id']);
    Route::put('/edit_layout/{id}', [CommandsInventoryController::class, 'edit_layout_by_id']);
});

Route::prefix('/v1/stats')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/dashboard', [QueriesStatsController::class, 'get_dashboard']);
    Route::prefix('/inventory')->group(function () {
        Route::get('/total_by_category/{type}', [QueriesStatsController::class, 'get_total_inventory_by_category']);
        Route::get('/total_by_favorite/{type}', [QueriesStatsController::class, 'get_total_inventory_by_favorite']);
        Route::get('/total_by_merk/{type}', [QueriesStatsController::class, 'get_total_inventory_by_merk']);
        Route::get('/total_by_room/{type}', [QueriesStatsController::class, 'get_total_inventory_by_room']);
        Route::get('/total_created_per_month/{year}', [QueriesStatsController::class, 'get_total_inventory_created_per_month']);
    });
    Route::prefix('/report')->group(function () {
        Route::get('/total_created_per_month/{year}', [QueriesStatsController::class, 'get_total_report_created_per_month']);
        Route::get('/total_spending_per_month/{year}', [QueriesStatsController::class, 'get_total_report_spending_per_month']);
        Route::get('/total_used_per_month/{year}', [QueriesStatsController::class, 'get_total_report_used_per_month']);
    });
});

Route::prefix('/v1/reminder')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/mark', [QueriesReminderController::class, 'get_reminder_mark']);
    Route::get('/history', [QueriesReminderController::class, 'get_reminder_history']);
    Route::post('/', [CommandsReminderController::class, 'post_reminder']);
    Route::post('/re_remind', [CommandsReminderController::class, 'post_re_remind']);
    Route::delete('/{id}', [CommandsReminderController::class, 'delete_reminder_by_id']);
});

Route::prefix('/v1/analyze')->middleware(['auth:sanctum'])->group(function () {
    Route::post('/report', [CommandsReportController::class, 'post_analyze_report']);
    Route::post('/bill', [CommandsReportController::class, 'post_analyze_bill']);
    Route::post('/report/new', [CommandsReportController::class, 'post_create_analyzed_report']);
});

Route::prefix('/v1/history')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [QueriesHistoryController::class, 'get_all_history']);
    Route::delete('/destroy/{id}', [CommandsHistoryController::class, 'hard_delete_history_by_id']);
});

Route::prefix('/v1/error')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [QueriesErrorController::class, 'get_all_error']);
});

Route::prefix('/v1/dictionary')->group(function () {
    Route::get('/type/{type}', [QueriesDictionaryController::class, 'get_dictionary_by_type']);
    Route::post('/', [CommandsDictionaryController::class, 'post_dictionary'])->middleware(['auth:sanctum']);
    Route::delete('/{id}', [CommandsDictionaryController::class, 'hard_delete_dictionary_by_id'])->middleware(['auth:sanctum']);
});

Route::prefix('/v1/report')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [QueriesReportController::class, 'get_all_report']);
    Route::get('/{search}/{id}', [QueriesReportController::class, 'get_my_report_by_inventory']);
    Route::post('/', [CommandsReportController::class, 'post_report']);
    Route::prefix('/detail/item/{id}')->group(function (){
        Route::get('/', [QueriesReportController::class, 'get_my_report_detail']);
        Route::get('/doc', [QueriesReportController::class, 'get_document']);
    });
    Route::prefix('/update')->group(function (){
        Route::put('/report/{id}', [CommandsReportController::class, 'update_report_by_id']);
        Route::put('/report_item/{id}', [CommandsReportController::class, 'update_report_item_by_id']);
        Route::put('/report_split/{id}', [CommandsReportController::class, 'update_split_report_item_by_id']);
    });
    Route::prefix('/delete')->group(function (){
        Route::delete('/item/{id}', [CommandsReportController::class, 'hard_delete_report_item_by_id']);
        Route::delete('/report/{id}', [CommandsReportController::class, 'hard_delete_report_by_id']);
    });
});

Route::prefix('/v1/user')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [QueriesUserController::class, 'get_all_user']);
    Route::get('/my_profile', [QueriesUserController::class, 'get_my_profile']);
    Route::put('/update_telegram_id', [CommandsUserController::class, 'update_telegram_id']);
    Route::put('/update_profile', [CommandsUserController::class, 'update_profile']);
    Route::put('/validate_telegram_id', [CommandsUserController::class, 'validate_telegram_id']);
    Route::put('/update_timezone_fcm', [CommandsUserController::class, 'update_timezone_fcm']);
});