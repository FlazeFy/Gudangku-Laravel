<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthApi\Commands as CommandAuthApi;
use App\Http\Controllers\Api\AuthApi\Queries as QueryAuthApi;

use App\Http\Controllers\Api\InventoryApi\Queries as QueriesInventoryController;
use App\Http\Controllers\Api\InventoryApi\Commands as CommandsInventoryController;


######################### Public Route #########################

Route::post('/v1/login', [CommandAuthApi::class, 'login']);

######################### Private Route #########################

Route::get('/v1/logout', [QueryAuthApi::class, 'logout'])->middleware(['auth:sanctum']);

Route::prefix('/v1/inventory')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [QueriesInventoryController::class, 'get_all_inventory']);

    Route::delete('/delete/{id}', [CommandsInventoryController::class, 'soft_delete_inventory_by_id']);
    Route::delete('/destroy/{id}', [CommandsInventoryController::class, 'hard_delete_inventory_by_id']);
});