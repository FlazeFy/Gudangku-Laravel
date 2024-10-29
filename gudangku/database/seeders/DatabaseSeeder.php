<?php

namespace Database\Seeders;

use App\Models\AdminModel;
use App\Models\UserModel;
use App\Models\InventoryModel;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        AdminModel::factory(10)->create();
        UserModel::factory(10)->create();
        InventoryModel::factory(10)->create();
    }
}
