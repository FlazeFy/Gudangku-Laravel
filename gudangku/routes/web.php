<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\AddController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CalendarController;

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
    Route::get('/login', [LoginController::class, 'index']);
    Route::post('/login/validate', [LoginController::class, 'login_auth']);
});

Route::prefix('/inventory')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/by/{view}/{context}', [HomeController::class, 'catalog_index']);
    
    Route::post('/deleteInventory/{id}', [HomeController::class, 'soft_delete']);
    Route::post('/destroyInventory/{id}', [HomeController::class, 'hard_delete']);
    Route::post('/recoverInventory/{id}', [HomeController::class, 'recover']);
    Route::post('/favToggleInventory/{id}', [HomeController::class, 'fav_toogle']);
    Route::post('/toogleView', [HomeController::class, 'toogle_view']);
});

Route::prefix('/inventory/add')->group(function () {
    Route::get('/', [AddController::class, 'index']);

    Route::post('/addInventory', [AddController::class, 'create']);
});

Route::prefix('/stats')->group(function () {
    Route::get('/', [StatsController::class, 'index']);
    Route::post('/toogleTotal', [StatsController::class, 'toogle_total']);
});

Route::prefix('/history')->group(function () {
    Route::get('/', [HistoryController::class, 'index']);

    Route::post('/delete/{id}', [HistoryController::class, 'hard_delete']);
});

Route::prefix('/profile')->group(function () {
    Route::get('/', [ProfileController::class, 'index']);

    Route::post('/sign_out', [ProfileController::class, 'sign_out']);
});

Route::prefix('/calendar')->group(function () {
    Route::get('/', [CalendarController::class, 'index']);
});