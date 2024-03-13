<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AddController;

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
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/login', [LoginController::class, 'index']);
    Route::post('/login/validate', [LoginController::class, 'login_auth']);

    Route::post('/deleteInventory/{id}', [HomeController::class, 'soft_delete']);
    Route::post('/destroyInventory/{id}', [HomeController::class, 'hard_delete']);
    Route::post('/recoverInventory/{id}', [HomeController::class, 'recover']);
});

Route::prefix('/add')->group(function () {
    Route::get('/', [AddController::class, 'index']);

    Route::post('/addInventory', [AddController::class, 'create']);
});
