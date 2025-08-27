<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\FeaturesController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\AddController;
use App\Http\Controllers\EditController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AnalyzeController;
use App\Http\Controllers\ReportDetailController;
use App\Http\Controllers\Room3DController;
use App\Http\Controllers\Room2DController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ErrorController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\LendController;
use App\Http\Controllers\EmbedController;

######################### Public Route #########################

Route::prefix('/')->group(function () {
    Route::get('/', [LandingController::class, 'index'])->name('landing');
    Route::get('/login', [LoginController::class, 'index'])->name('login');
    Route::get('/register', [RegisterController::class, 'index'])->name('register');
    Route::get('/features', [FeaturesController::class, 'index']);
    Route::get('/help', [HelpController::class, 'index']);

    Route::post('/login/validate', [LoginController::class, 'login_auth']);
});

Route::prefix('/auth')->group(function (){
    Route::get('/google', [LoginController::class, 'redirect_to_google']);
    Route::get('/google/callback', [LoginController::class, 'login_google_callback']);
});

######################### Private Route #########################

Route::prefix('/inventory')->middleware(['auth_v2:sanctum'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/by/{view}/{context}', [HomeController::class, 'catalog_index']); 
    Route::post('/toogleView', [HomeController::class, 'toogle_view']);
    Route::post('/save_as_csv', [HomeController::class, 'save_as_csv']);

    Route::prefix('/add')->group(function (){
        Route::get('/', [AddController::class, 'index']);
    });
    Route::prefix('/edit/{id}')->group(function (){
        Route::get('/', [EditController::class, 'index']);
    });
});

Route::prefix('/stats')->middleware(['auth_v2:sanctum'])->group(function () {
    Route::get('/', [StatsController::class, 'index']);
    Route::post('/toogleTotal', [StatsController::class, 'toogle_total']);
    Route::post('/toogleView', [StatsController::class, 'toogle_view']);
    Route::post('/toogleYear', [StatsController::class, 'toogle_year']);
});

Route::prefix('/embed')->group(function () {
    Route::get('/distribution_inventory_category', [EmbedController::class, 'distribution_inventory_category']);
    Route::get('/distribution_inventory_room', [EmbedController::class, 'distribution_inventory_room']);
    Route::get('/distribution_inventory_favorite', [EmbedController::class, 'distribution_inventory_favorite']);
    Route::get('/distribution_inventory_merk', [EmbedController::class, 'distribution_inventory_merk']);
});

Route::prefix('/history')->middleware(['auth_v2:sanctum'])->group(function () {
    Route::get('/', [HistoryController::class, 'index']);
    Route::post('/save_as_csv', [HistoryController::class, 'save_as_csv']);
});

Route::prefix('/profile')->middleware(['auth_v2:sanctum'])->group(function () {
    Route::get('/', [ProfileController::class, 'index']);
    Route::post('/sign_out', [ProfileController::class, 'sign_out']);
    Route::post('/validate_telegram', [ProfileController::class, 'validate_telegram_id']);
    Route::post('/submit_telegram_validation', [ProfileController::class, 'submit_telegram_validation']);
});

Route::prefix('/user')->middleware(['auth_v2:sanctum'])->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::post('/save_as_csv', [UserController::class, 'save_as_csv']);
});

Route::prefix('/error')->middleware(['auth_v2:sanctum'])->group(function () {
    Route::get('/', [ErrorController::class, 'index']);

    Route::post('/save_as_csv', [ErrorController::class, 'save_as_csv']);
});

Route::prefix('/reminder')->middleware(['auth_v2:sanctum'])->group(function () {
    Route::get('/', [ReminderController::class, 'index']);

    Route::post('/save_as_csv', [ReminderController::class, 'save_as_csv']);
});

Route::prefix('/calendar')->middleware(['auth_v2:sanctum'])->group(function () {
    Route::get('/', [CalendarController::class, 'index']);
});

Route::prefix('/lend')->group(function () {
    Route::get('/{id}', [LendController::class, 'index']);
});

Route::prefix('/report')->middleware(['auth_v2:sanctum'])->group(function () {
    Route::get('/', [ReportController::class, 'index']);

    Route::prefix('/detail/{id}')->group(function (){
        Route::get('/', [ReportDetailController::class, 'index']);
        Route::post('/toogle_edit', [ReportDetailController::class, 'toogle_edit']);  
        Route::post('/save_as_csv', [ReportDetailController::class, 'save_as_csv']);  
    });
});

Route::prefix('/room')->middleware(['auth_v2:sanctum'])->group(function () {
    Route::get('/3d', [Room3DController::class, 'index']);
    Route::get('/2d', [Room2DController::class, 'index']);
    Route::post('/select_room', [Room2DController::class, 'select_room']);
});

Route::prefix('/doc')->group(function () {
    Route::prefix('/report/{id}')->group(function (){
        Route::get('/', [DocumentController::class, 'index_report']);
        Route::get('/custom', [DocumentController::class, 'custom_report']);
    });
    Route::prefix('/layout/{room}')->middleware(['auth_v2:sanctum'])->group(function (){
        Route::get('/', [DocumentController::class, 'index_layout']);
        Route::get('/custom', [DocumentController::class, 'custom_layout']);
    });
    Route::prefix('/inventory/{id}')->middleware(['auth_v2:sanctum'])->group(function (){
        Route::get('/', [DocumentController::class, 'index_inventory']);
        Route::get('/custom', [DocumentController::class, 'custom_inventory']);
    });
});

Route::prefix('/analyze')->group(function () {
    Route::prefix('/report/{id}')->group(function (){
        Route::get('/', [AnalyzeController::class, 'index_report']);
    });
    Route::prefix('/layout/{room}')->middleware(['auth_v2:sanctum'])->group(function (){
        Route::get('/', [AnalyzeController::class, 'index_layout']);
    });
    Route::prefix('/inventory/{id}')->middleware(['auth_v2:sanctum'])->group(function (){
        Route::get('/', [AnalyzeController::class, 'index_inventory']);
    });
});