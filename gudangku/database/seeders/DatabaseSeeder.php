<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// Model
use App\Models\AdminModel;
use App\Models\UserModel;
use App\Models\InventoryModel;
use App\Models\ReportModel;
use App\Models\HistoryModel;
use App\Models\ReportItemModel;
use App\Models\ReminderModel;
use App\Models\FAQModel;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Delete All
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        FAQModel::truncate();
        HistoryModel::truncate();
        ReportItemModel::truncate();
        ReportModel::truncate();
        ReminderModel::truncate();
        InventoryModel::truncate();
        UserModel::truncate();
        AdminModel::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        // Factory
        AdminModel::factory(5)->create();
        UserModel::factory(30)->create();
        FAQModel::factory(60)->create();
        InventoryModel::factory(200)->create();
        ReportModel::factory(250)->create();
        ReportItemModel::factory(300)->create();
        ReminderModel::factory(50)->create();
        HistoryModel::factory(200)->create();
    }
}
