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

use App\Http\Controllers\Api\UserApi\Queries as QueriesUserController;
use App\Http\Controllers\Api\UserApi\Commands as CommandsUserController;

######################### Public Route #########################

Route::post('/v1/login', [CommandAuthApi::class, 'login']);
Route::post('/v1/register/token', [CommandsUserController::class, 'get_register_validation_token']);
Route::post('/v1/register/account', [CommandsUserController::class, 'post_validate_register']);
Route::post('/v1/register/regen_token', [CommandsUserController::class, 'regenerate_register_token']);

######################### Private Route #########################

Route::get('/v1/logout', [QueryAuthApi::class, 'logout'])->middleware(['auth:sanctum']);

Route::prefix('/v1/inventory')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [QueriesInventoryController::class, 'get_all_inventory']);
    Route::get('/list', [QueriesInventoryController::class, 'get_list_inventory']);
    Route::get('/calendar', [QueriesInventoryController::class, 'get_list_calendar']);

    Route::delete('/delete/{id}', [CommandsInventoryController::class, 'soft_delete_inventory_by_id']);
    Route::delete('/destroy/{id}', [CommandsInventoryController::class, 'hard_delete_inventory_by_id']);
    Route::put('/fav_toggle/{id}', [CommandsInventoryController::class, 'fav_toogle_inventory_by_id']);
    Route::put('/recover/{id}', [CommandsInventoryController::class, 'recover_inventory_by_id']);
    Route::put('/edit_image/{id}', [CommandsInventoryController::class, 'edit_image_by_id']);
});

Route::prefix('/v1/stats')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/total_inventory_by_category', [QueriesStatsController::class, 'get_total_inventory_by_category']);
    Route::get('/total_inventory_by_favorite', [QueriesStatsController::class, 'get_total_inventory_by_favorite']);
    Route::get('/total_inventory_by_room', [QueriesStatsController::class, 'get_total_inventory_by_room']);
});

Route::prefix('/v1/history')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [QueriesHistoryController::class, 'get_all_history']);
    
    Route::delete('/destroy/{id}', [CommandsHistoryController::class, 'hard_delete_history_by_id']);
});

Route::prefix('/v1/report')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [QueriesReportController::class, 'get_my_report']);
});

Route::prefix('/v1/user')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/my_profile', [QueriesUserController::class, 'get_my_profile']);
    Route::put('/update_telegram_id', [CommandsUserController::class, 'update_telegram_id']);
    Route::put('/update_timezone_fcm', [CommandsUserController::class, 'update_timezone_fcm']);
});