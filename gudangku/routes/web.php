<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\AddController;
use App\Http\Controllers\StatsController;

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
    
    Route::post('/deleteInventory/{id}', [HomeController::class, 'soft_delete']);
    Route::post('/destroyInventory/{id}', [HomeController::class, 'hard_delete']);
    Route::post('/recoverInventory/{id}', [HomeController::class, 'recover']);
    Route::post('/favToggleInventory/{id}', [HomeController::class, 'fav_toogle']);
});

Route::prefix('/inventory/add')->group(function () {
    Route::get('/', [AddController::class, 'index']);

    Route::post('/addInventory', [AddController::class, 'create']);
});

Route::prefix('/stats')->group(function () {
    Route::get('/', [StatsController::class, 'index']);
    Route::post('/toogleTotal', [StatsController::class, 'toogle_total']);
});
