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

use App\Http\Controllers\Api\DictionaryApi\Queries as QueriesDictionaryController;

######################### Public Route #########################

Route::post('/v1/login', [CommandAuthApi::class, 'login']);
Route::post('/v1/register/token', [CommandsUserController::class, 'get_register_validation_token']);
Route::post('/v1/register/account', [CommandsUserController::class, 'post_validate_register']);
Route::post('/v1/register/regen_token', [CommandsUserController::class, 'regenerate_register_token']);

######################### Private Route #########################

Route::get('/v1/logout', [QueryAuthApi::class, 'logout'])->middleware(['auth:sanctum']);

Route::prefix('/v1/inventory')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [QueriesInventoryController::class, 'get_all_inventory']);
    Route::get('/search/by_room_storage/{room}/{storage}', [QueriesInventoryController::class, 'get_inventory_by_storage']);
    Route::get('/list', [QueriesInventoryController::class, 'get_list_inventory']);
    Route::get('/room', [QueriesInventoryController::class, 'get_list_room']);
    Route::get('/layout/{room}', [QueriesInventoryController::class, 'get_room_layout']);
    Route::get('/calendar', [QueriesInventoryController::class, 'get_list_calendar']);

    Route::post('/', [CommandsInventoryController::class, 'post_inventory']);
    Route::post('/layout', [CommandsInventoryController::class, 'post_inventory_layout']);
    Route::delete('/delete/{id}', [CommandsInventoryController::class, 'soft_delete_inventory_by_id']);
    Route::delete('/destroy/{id}', [CommandsInventoryController::class, 'hard_delete_inventory_by_id']);
    Route::put('/fav_toggle/{id}', [CommandsInventoryController::class, 'fav_toogle_inventory_by_id']);
    Route::put('/recover/{id}', [CommandsInventoryController::class, 'recover_inventory_by_id']);
    Route::put('/edit_image/{id}', [CommandsInventoryController::class, 'edit_image_by_id']);
    Route::put('/edit_layout/{id}', [CommandsInventoryController::class, 'edit_layout_by_id']);
});

Route::prefix('/v1/stats')->middleware(['auth:sanctum'])->group(function () {
    Route::prefix('/inventory')->group(function () {
        Route::get('/total_by_category/{type}', [QueriesStatsController::class, 'get_total_inventory_by_category']);
        Route::get('/total_by_favorite/{type}', [QueriesStatsController::class, 'get_total_inventory_by_favorite']);
        Route::get('/total_by_room/{type}', [QueriesStatsController::class, 'get_total_inventory_by_room']);
    });
    Route::prefix('/report')->group(function () {
        Route::get('/total_created_per_month/{year}', [QueriesStatsController::class, 'get_total_report_created_per_month']);
        Route::get('/total_spending_per_month/{year}', [QueriesStatsController::class, 'get_total_report_spending_per_month']);
    });
});


Route::prefix('/v1/history')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [QueriesHistoryController::class, 'get_all_history']);
    
    Route::delete('/destroy/{id}', [CommandsHistoryController::class, 'hard_delete_history_by_id']);
});

Route::prefix('/v1/dictionary')->group(function () {
    Route::get('/type/{type}', [QueriesDictionaryController::class, 'get_dictionary_by_type']);
});

Route::prefix('/v1/report')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [QueriesReportController::class, 'get_my_report']);
    Route::get('/{search}/{id}', [QueriesReportController::class, 'get_my_report_by_inventory']);
    Route::get('/detail/item/{id}', [QueriesReportController::class, 'get_my_report_detail']);
    Route::delete('/delete/item/{id}', [CommandsReportController::class, 'hard_delete_report_item_by_id']);
    Route::delete('/delete/report/{id}', [CommandsReportController::class, 'hard_delete_report_by_id']);
    Route::put('/update/report/{id}', [CommandsReportController::class, 'update_report_by_id']);
});

Route::prefix('/v1/user')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/my_profile', [QueriesUserController::class, 'get_my_profile']);
    Route::put('/update_telegram_id', [CommandsUserController::class, 'update_telegram_id']);
    Route::put('/update_timezone_fcm', [CommandsUserController::class, 'update_timezone_fcm']);
});