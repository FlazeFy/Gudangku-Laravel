<?php

namespace Database\Seeders;

use App\Models\AdminModel;
use App\Models\UserModel;
use App\Models\InventoryModel;
use App\Models\ReportModel;
use App\Models\HistoryModel;
use App\Models\ReportItemModel;
use App\Models\ReminderModel;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Delete All
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        HistoryModel::truncate();
        ReportItemModel::truncate();
        ReportModel::truncate();
        ReminderModel::truncate();
        InventoryModel::truncate();
        UserModel::truncate();
        AdminModel::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        // Factory
        AdminModel::factory(10)->create();
        UserModel::factory(10)->create();
        InventoryModel::factory(10)->create();
        ReportModel::factory(10)->create();
        ReportItemModel::factory(10)->create();
        ReminderModel::factory(10)->create();
        HistoryModel::factory(50)->create();
    }
}
