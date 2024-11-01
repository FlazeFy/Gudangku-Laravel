<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\AddController;
use App\Http\Controllers\EditController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportDetailController;
use App\Http\Controllers\Room3DController;
use App\Http\Controllers\Room2DController;

######################### Public Route #########################

Route::prefix('/')->group(function () {
    Route::get('/', [LandingController::class, 'index'])->name('landing');
    Route::get('/login', [LoginController::class, 'index'])->name('login');
    Route::get('/register', [RegisterController::class, 'index'])->name('register');
    Route::post('/login/validate', [LoginController::class, 'login_auth']);
});

######################### Private Route #########################

Route::prefix('/inventory')->middleware(['auth_v2:sanctum'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/by/{view}/{context}', [HomeController::class, 'catalog_index']); 
    Route::post('/deleteInventory/{id}', [HomeController::class, 'soft_delete']);
    Route::post('/destroyInventory/{id}', [HomeController::class, 'hard_delete']);
    Route::post('/destroyReminder/{id}', [HomeController::class, 'hard_delete_reminder']);
    Route::post('/copyReminder/{id}', [HomeController::class, 'copy_reminder']);
    Route::post('/editReminder/{id}', [HomeController::class, 'edit_reminder']);
    Route::post('/recoverInventory/{id}', [HomeController::class, 'recover']);
    Route::post('/favToggleInventory/{id}', [HomeController::class, 'fav_toogle']);
    Route::post('/toogleView', [HomeController::class, 'toogle_view']);
    Route::post('/saveAsCsv', [HomeController::class, 'save_as_csv']);
    Route::post('/auditWABot', [HomeController::class, 'get_all_inventory_wa_bot']);

    Route::prefix('/add')->group(function (){
        Route::get('/', [AddController::class, 'index']);
        Route::post('/addInventory', [AddController::class, 'create']);
    });
    Route::prefix('/edit/{id}')->group(function (){
        Route::get('/', [EditController::class, 'index']);
        Route::post('/editInventory', [EditController::class, 'update']);
        Route::post('/editInventory/addReport', [EditController::class, 'create_report']);
    });
});

Route::prefix('/stats')->middleware(['auth_v2:sanctum'])->group(function () {
    Route::get('/', [StatsController::class, 'index']);
    Route::post('/toogleTotal', [StatsController::class, 'toogle_total']);
});

Route::prefix('/history')->middleware(['auth_v2:sanctum'])->group(function () {
    Route::get('/', [HistoryController::class, 'index']);
    Route::post('/delete/{id}', [HistoryController::class, 'hard_delete']);
    Route::post('/saveAsCsv', [HistoryController::class, 'save_as_csv']);
});

Route::prefix('/profile')->middleware(['auth_v2:sanctum'])->group(function () {
    Route::get('/', [ProfileController::class, 'index']);
    Route::post('/sign_out', [ProfileController::class, 'sign_out']);
    Route::post('/validate_telegram', [ProfileController::class, 'validate_telegram_id']);
    Route::post('/submit_telegram_validation', [ProfileController::class, 'submit_telegram_validation']);
});

Route::prefix('/calendar')->middleware(['auth_v2:sanctum'])->group(function () {
    Route::get('/', [CalendarController::class, 'index']);
});

Route::prefix('/report')->middleware(['auth_v2:sanctum'])->group(function () {
    Route::get('/', [ReportController::class, 'index']);
    Route::post('/', [ReportController::class, 'create_report']);

    Route::prefix('/detail/{id}')->group(function (){
        Route::get('/', [ReportDetailController::class, 'index']);
        Route::post('/toogleEdit', [ReportDetailController::class, 'toogle_edit']);    
    });
});

Route::prefix('/room')->middleware(['auth_v2:sanctum'])->group(function () {
    Route::get('/3d', [Room3DController::class, 'index']);
    Route::get('/2d', [Room2DController::class, 'index']);
    Route::post('/selectRoom', [Room2DController::class, 'select_room']);
});

Route::prefix('/doc')->group(function () {
    Route::get('/report/{id}', [DocumentController::class, 'index_report']);
    Route::get('/layout/{room}', [DocumentController::class, 'index_layout'])->middleware(['auth_v2:sanctum']);
});