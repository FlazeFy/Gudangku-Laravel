<?php

namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

// Helper
use App\Helpers\Generator;
// Model
use App\Models\UserModel;

class HistoryModelFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => Generator::getUUID(), 
            'history_type' => fake()->word(), 
            'history_context' => fake()->sentence(), 
            'created_at' => Generator::getRandomDate(0), 
            'created_by' => UserModel::getRandom(0) 
        ];
    }
}
