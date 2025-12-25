<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Helpers\Generator;
use App\Models\DictionaryModel;
use App\Models\UserModel;
use App\Models\InventoryModel;
use App\Models\ReportModel;

class ReportItemModelFactory extends Factory
{
    public function definition(): array
    {
        $ran = mt_rand(0, 1);
        $user_id = UserModel::getRandomWithInventoryAndReport(0);
        $inventory = InventoryModel::getRandom(0,$user_id);
        $report = ReportModel::getRandom(0,$user_id);

        return [
            'id' => Generator::getUUID(), 
            'inventory_id' => $inventory->id, 
            'report_id' => $report->id, 
            'item_name' => $inventory->inventory_name, 
            'item_desc' => $ran == 1 ? $inventory->inventory_name : fake()->paragraph(), 
            'item_qty' => $report->report_category === 'Checkout' ? ($inventory->inventory_unit === 'Pcs' ? mt_rand(1, $inventory->inventory_vol - 1) : 1) 
                : (in_array($report->report_category, ['Wishlist', 'Wash List']) ? 1 : mt_rand(1, 3)),            
            'item_price' => $inventory->inventory_price, 
            'created_at' => Generator::getRandomDate(0), 
            'created_by' => $user_id, 
        ];
    }
}
