<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\MCQTest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    protected $model = Question::class;
    
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $options = [
            $this->faker->sentence(3),
            $this->faker->sentence(3),
            $this->faker->sentence(3),
            $this->faker->sentence(3),
        ];
        
        return [
            'mcq_test_id' => MCQTest::factory(),
            'question_text' => $this->faker->sentence . '?',
            'options' => json_encode($options),
            'correct_option' => json_encode([rand(0, 3)]),
            'explanation' => $this->faker->paragraph,
            'marks' => $this->faker->numberBetween(1, 5),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
} 