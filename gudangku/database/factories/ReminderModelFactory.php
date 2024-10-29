<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Helpers\Generator;
use App\Models\DictionaryModel;
use App\Models\UserModel;
use App\Models\InventoryModel;

class ReminderModelFactory extends Factory
{
    public function definition(): array
    {
        $ran = mt_rand(0, 1);
        $user_id = UserModel::getRandom(0);
        $inventory = InventoryModel::getRandom(0,$user_id);

        return [
            'id' => Generator::getUUID(), 
            'inventory_id' => $inventory->id, 
            'reminder_desc' => fake()->paragraph(),  
            'reminder_type' => DictionaryModel::getRandom(0,'reminder_type'), 
            'reminder_context' => DictionaryModel::getRandom(0,'reminder_context'), 
            'created_at' => Generator::getRandomDate(0), 
            'created_by' => $user_id, 
            'updated_at' => Generator::getRandomDate($ran)
        ];
    }
}
