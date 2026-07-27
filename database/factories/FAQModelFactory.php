<?php

namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

// Helper
use App\Helpers\Generator;

class FAQModelFactory extends Factory
{
    public function definition(): array
    {        
        return [
            'id' => Generator::getUUID(), 
            'faq_question' => fake()->sentence(), 
            'faq_answer' => fake()->paragraph(1),
            'created_at' => Generator::getRandomDate(0), 
        ];
    }
}
