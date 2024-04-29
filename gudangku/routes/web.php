<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\AddController;
use App\Http\Controllers\EditController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::prefix('/')->group(function () {
    Route::get('/', [LandingController::class, 'index'])->name('landing');
    Route::get('/login', [LoginController::class, 'index'])->name('login');
    Route::post('/login/validate', [LoginController::class, 'login_auth']);
});

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
});

Route::prefix('/inventory/add')->middleware(['auth_v2:sanctum'])->group(function () {
    Route::get('/', [AddController::class, 'index']);

    Route::post('/addInventory', [AddController::class, 'create']);
});

Route::prefix('/inventory/edit/{id}')->middleware(['auth_v2:sanctum'])->group(function () {
    Route::get('/', [EditController::class, 'index']);

    Route::post('/editInventory', [EditController::class, 'update']);
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
});

Route::prefix('/calendar')->middleware(['auth_v2:sanctum'])->group(function () {
    Route::get('/', [CalendarController::class, 'index']);
});

Route::prefix('/report')->middleware(['auth_v2:sanctum'])->group(function () {
    Route::get('/', [ReportController::class, 'index']);
    Route::post('/', [ReportController::class, 'create_report']);
});