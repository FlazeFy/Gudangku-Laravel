<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Helpers\Generator;
use App\Models\DictionaryModel;
use App\Models\UserModel;

class ReportModelFactory extends Factory
{
    public function definition(): array
    {
        $ran = mt_rand(0, 1);
        
        return [
            'id' => Generator::getUUID(), 
            'report_title' => fake()->words(mt_rand(2,4), true), 
            'report_desc' => fake()->paragraph(), 
            'report_category' => DictionaryModel::getRandom(0,'report_category'), 
            'is_reminder' => $ran, 
            'remind_at' => $ran == 1 ? fake()->dateTime() : null, 
            'created_at' => Generator::getRandomDate(0), 
            'created_by' => UserModel::getRandomWithInventory(0), 
            'updated_at' => Generator::getRandomDate($ran),
            'deleted_at' => null
        ];
    }
}
