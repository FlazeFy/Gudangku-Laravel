<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Controller API
use App\Http\Controllers\Api\AuthApi\Commands as CommandAuthApi;
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
use App\Http\Controllers\Api\ErrorApi\Commands as CommandsErrorController;
use App\Http\Controllers\Api\LendApi\Commands as CommandsLendController;
use App\Http\Controllers\Api\LendApi\Queries as QueriesLendController;

######################### Public Route #########################

Route::post('/v1/login', [CommandAuthApi::class, 'postLogin']);

Route::prefix('/v1/register')->group(function () {
    Route::post('/token', [CommandsUserController::class, 'postRegisterValidationToken']);
    Route::post('/account', [CommandsUserController::class, 'postValidateRegister']);
    Route::post('/regen_token', [CommandsUserController::class, 'postRegenerateRegisterToken']);
});

Route::prefix('/v1/lend')->group(function () {
    Route::prefix('/inventory/{lend_id}')->group(function () {
        Route::get('/', [QueriesLendController::class, 'getLendInventory']);
        Route::post('/', [CommandsLendController::class, 'postBorrowInventory']);
    });
});

Route::prefix('/v1/dictionary')->group(function () {
    Route::get('/type/{type}', [QueriesDictionaryController::class, 'getDictionaryByType']);
});

Route::prefix('/v1/stats')->group(function () {
    Route::prefix('/inventory')->group(function () {
        Route::get('/total_created_per_month/{year}', [QueriesStatsController::class, 'getTotalInventoryCreatedPerMonth']);
        Route::get('/total_by_category/{type}', [QueriesStatsController::class, 'getTotalInventoryByCategory']);
        Route::get('/total_by_room/{type}', [QueriesStatsController::class, 'getTotalInventoryByRoom']);
        Route::get('/total_by_favorite/{type}', [QueriesStatsController::class, 'getTotalInventoryByFavorite']);
        Route::get('/total_by_merk/{type}', [QueriesStatsController::class, 'getTotalInventoryByMerk']);
        Route::get('/favorite_inventory_comparison', [QueriesStatsController::class, 'getTotalFavoriteInventoryComparison']);
        Route::get('/low_capacity_inventory_comparison', [QueriesStatsController::class, 'getTotalLowCapacityInventoryComparison']);
    });
    Route::prefix('/report')->group(function () {
        Route::get('/total_created_per_month/{year}', [QueriesStatsController::class, 'getTotalReportCreatedPerMonth']);
    });
    Route::prefix('/history')->group(function () {
        Route::get('/total_activity_per_month/{year}', [QueriesStatsController::class, 'getTotalActivityPerMonth']);
    });
});

######################### Private Route #########################

Route::post('/v1/logout', [CommandAuthApi::class, 'postLogout'])->middleware(['auth:sanctum']);

Route::prefix('/v1/inventory')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [QueriesInventoryController::class, 'getAllInventory']);
    Route::prefix('/catalog')->group(function () {
        Route::get('/', [QueriesInventoryController::class, 'getInventoryCatalog']);
        Route::get('/{view}/{catalog}', [QueriesInventoryController::class, 'getInventoryByCatalog']);
    });
    Route::get('/search/by_room_storage/{room}/{storage}', [QueriesInventoryController::class, 'getInventoryByStorage']);
    Route::get('/list', [QueriesInventoryController::class, 'getListInventory']);
    Route::get('/room', [QueriesInventoryController::class, 'getListRoom']);
    Route::get('/calendar', [QueriesInventoryController::class, 'getListCalendar']);
    Route::get('/analyze/{id}', [QueriesInventoryController::class, 'getAnalyzeInventory']);
    Route::prefix('/layout/{room}')->group(function (){
        Route::get('/', [QueriesInventoryController::class, 'getRoomLayout']);
        Route::get('/doc', [QueriesInventoryController::class, 'getRoomDocument']);
    });
    Route::prefix('/detail/{id}')->group(function (){
        Route::get('/', [QueriesInventoryController::class, 'getInventoryByID']);
        Route::get('/doc', [QueriesInventoryController::class, 'getInventoryDetailDocument']);
    });
    Route::post('/', [CommandsInventoryController::class, 'postInventory']);
    Route::post('/layout', [CommandsInventoryController::class, 'postInventoryLayout']);
    Route::delete('/delete_layout/{id}/{coor}', [CommandsInventoryController::class, 'hardDeleteInventoryLayoutByIDCoor']);
    Route::delete('/delete/{id}', [CommandsInventoryController::class, 'softDeleteInventoryByID']);
    Route::delete('/destroy/{id}', [CommandsInventoryController::class, 'hardDeleteInventoryByID']);
    Route::put('/fav_toggle/{id}', [CommandsInventoryController::class, 'putFavToogleInventoryByID']);
    Route::put('/recover/{id}', [CommandsInventoryController::class, 'putRecoverInventoryByID']);
    Route::put('/edit/{id}', [CommandsInventoryController::class, 'putEditInventoryByID']);
    Route::post('/edit_image/{id}', [CommandsInventoryController::class, 'putEditImageByID']);
    Route::put('/edit_layout/{id}', [CommandsInventoryController::class, 'putEditLayoutByID']);
});

Route::prefix('/v1/stats')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/dashboard', [QueriesStatsController::class, 'getDashboard']);
    Route::prefix('/inventory')->group(function () {
        Route::get('/most_expensive/{context}', [QueriesStatsController::class, 'getMostExpensiveInventoryPerContext']);
        Route::get('/tree_map', [QueriesStatsController::class, 'getInventoryTreeMap']);
    });
    Route::prefix('/report')->group(function () {
        Route::get('/total_spending_per_month/{year}', [QueriesStatsController::class, 'getTotalReportSpendingPerMonth']);
        Route::get('/total_used_per_month/{year}', [QueriesStatsController::class, 'getTotalReportUsedPerMonth']);
    });
    Route::prefix('/user')->group(function () {
        Route::get('/last_login', [QueriesStatsController::class, 'getLastLoginUser']);
        Route::get('/leaderboard', [QueriesStatsController::class, 'getLeaderboard']);
    });
});

Route::prefix('/v1/reminder')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/mark', [QueriesReminderController::class, 'getReminderMark']);
    Route::get('/history', [QueriesReminderController::class, 'getReminderHistory']);
    Route::post('/', [CommandsReminderController::class, 'postReminder']);
    Route::post('/copy', [CommandsReminderController::class, 'postCopyReminder']);
    Route::post('/re_remind', [CommandsReminderController::class, 'postReRemind']);
    Route::delete('/{id}', [CommandsReminderController::class, 'hardDeleteReminderByID']);
    Route::put('/{id}', [CommandsReminderController::class, 'putReminderByID']);
});

Route::prefix('/v1/analyze')->middleware(['auth:sanctum'])->group(function () {
    Route::post('/report', [CommandsReportController::class, 'postAnalyzeReport']);
    Route::post('/bill', [CommandsReportController::class, 'postAnalyzeBill']);
    Route::post('/report/new', [CommandsReportController::class, 'postCreateAnalyzedReport']);
});

Route::prefix('/v1/lend')->middleware(['auth:sanctum'])->group(function () {
    Route::post('/qr', [CommandsLendController::class, 'postLendQR']);
    Route::put('/update_status/{lend_id}', [CommandsLendController::class, 'putConfirmationReturned']);
    Route::get('/qr', [QueriesLendController::class, 'getLendActive']);
    Route::get('/qr/history', [QueriesLendController::class, 'getLendHistory']);
});

Route::prefix('/v1/history')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [QueriesHistoryController::class, 'getAllHistory']);
    Route::delete('/destroy/{id}', [CommandsHistoryController::class, 'hardDeleteHistoryByID']);
});

Route::prefix('/v1/error')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [QueriesErrorController::class, 'getAllError']);
    Route::delete('/destroy/{id}', [CommandsErrorController::class, 'hardDeleteErrorByID']);
});

Route::prefix('/v1/dictionary')->middleware(['auth:sanctum'])->group(function () {
    Route::post('/', [CommandsDictionaryController::class, 'postDictionary']);
    Route::delete('/{id}', [CommandsDictionaryController::class, 'hardDeleteDictionaryByID']);
});

Route::prefix('/v1/report')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [QueriesReportController::class, 'getAllReport']);
    Route::get('/{search}/{id}', [QueriesReportController::class, 'getReportByInventoryNameOrInventoryID']);
    Route::post('/', [CommandsReportController::class, 'postReport']);
    Route::post('/item/{id}', [CommandsReportController::class, 'postReportItem']);
    Route::prefix('/report_image')->group(function () {
        Route::post('/{id}', [CommandsReportController::class, 'postUpdateReportImageByReportID']);
        Route::delete('/destroy/{report_id}/{image_id}', [CommandsReportController::class, 'hardDeleteReportImageByReportIDAndImageID']);
    });
    Route::prefix('/detail/item/{id}')->group(function (){
        Route::get('/', [QueriesReportController::class, 'getReportDetailByID']);
        Route::get('/doc', [QueriesReportController::class, 'getReportDetailDocFormatByID']);
    });
    Route::prefix('/update')->group(function (){
        Route::put('/report/{id}', [CommandsReportController::class, 'putUpdateReportByID']);
        Route::put('/report_item/{id}', [CommandsReportController::class, 'putUpdateReportItemByID']);
        Route::put('/report_split/{id}', [CommandsReportController::class, 'putUpdateSplitReportItemByID']);
    });
    Route::prefix('/delete')->group(function (){
        Route::delete('/item/{id}', [CommandsReportController::class, 'hardDeleteReportItemByID']);
        Route::delete('/report/{id}', [CommandsReportController::class, 'hardDeleteReportByID']);
    });
});

Route::prefix('/v1/user')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [QueriesUserController::class, 'getAllUser']);
    Route::get('/my_year', [QueriesUserController::class, 'getContentYear']);
    Route::get('/my_profile', [QueriesUserController::class, 'getMyProfile']);
    Route::put('/update_telegram_id', [CommandsUserController::class, 'putUpdateTelegramID']);
    Route::put('/update_profile', [CommandsUserController::class, 'putUpdateProfile']);
    Route::put('/validate_telegram_id', [CommandsUserController::class, 'putValidateTelegramID']);
    Route::put('/update_timezone_fcm', [CommandsUserController::class, 'updateTimezoneFCM']);
    Route::delete('/{id}', [CommandsUserController::class, 'hardDeleteUserById']);
});