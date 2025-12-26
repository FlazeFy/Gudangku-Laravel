<?php

namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

// Helper
use App\Helpers\Generator;
// Model
use App\Models\DictionaryModel;
use App\Models\UserModel;

class InventoryModelFactory extends Factory
{
    public function definition(): array
    {
        $ran = mt_rand(0, 1);
        $unit = DictionaryModel::getRandom(0,'inventory_unit');
        $unit_remain = $unit == 'Kilogram' || $unit == 'Pcs' ? null : 'percentage';
        $vol_remain = round(mt_rand(10, 90) / 5) * 5;
        $price = round(mt_rand(5000, 5000000) / 5000) * 5000;

        return [
            'id' => Generator::getUUID(), 
            'inventory_name' => fake()->words(mt_rand(2,3), true), 
            'inventory_category' => DictionaryModel::getRandom(0,'inventory_category'),  
            'inventory_desc' => fake()->paragraph(), 
            'inventory_merk' => fake()->company(), 
            'inventory_color' => fake()->colorName(), 
            'inventory_room' => DictionaryModel::getRandom(0,'inventory_room'), 
            'inventory_storage' => Generator::getRandomStorage(), 
            'inventory_rack' => $ran == 1 ? fake()->unique()->bothify('Rack-?#') : null, 
            'inventory_price' => $price, 
            'inventory_image' => null, 
            'inventory_unit' => $unit, 
            'inventory_vol' => Generator::getRandomVol($unit), 
            'inventory_capacity_unit' => $unit_remain, 
            'inventory_capacity_vol' => $unit_remain == 'percentage' ? $vol_remain : null, 
            'is_favorite' => $ran, 
            'is_reminder' => 0, 
            'created_at' => Generator::getRandomDate(0), 
            'created_by' => UserModel::getRandom(0), 
            'updated_at' => Generator::getRandomDate($ran),
            'deleted_at' => null
        ];
    }
}
