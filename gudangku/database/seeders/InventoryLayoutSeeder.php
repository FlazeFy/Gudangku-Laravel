<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

// Helper
use App\Helpers\Generator;
// Model
use App\Models\UserModel;
use App\Models\InventoryModel;
use App\Models\InventoryLayoutModel;

class InventoryLayoutSeeder extends Seeder
{
    public function run(): void
    {
        // Delete All 
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        InventoryLayoutModel::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $users = UserModel::getAllWithInventory();
        foreach($users as $us) {
            $ran = mt_rand(0, 1);
            $inventories = InventoryModel::getInventoryStorageRoom($us->id);

            foreach($inventories as $in) {
                $layout = Generator::getRandomLayout();
                // Factory
                InventoryLayoutModel::createInventoryLayout($in->inventory_room, $in->inventory_storage, $ran === 1 ? fake()->paragraph() : null, $layout, $us->id);
            }
        }
    }
}
