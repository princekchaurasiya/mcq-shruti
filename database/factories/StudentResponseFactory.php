<?php

namespace Database\Factories;

use App\Models\StudentResponse;
use App\Models\TestAttempt;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StudentResponse>
 */
class StudentResponseFactory extends Factory
{
    protected $model = StudentResponse::class;
    
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isCorrect = $this->faker->boolean(70);
        $selectedOption = null;
        
        if ($isCorrect) {
            // Will create a correct response when the question is created
            $selectedOption = json_encode([0]); // Will be overridden later
        } else {
            // Will create an incorrect response
            $selectedOption = json_encode([1]); // Will be overridden later
        }
        
        return [
            'test_attempt_id' => TestAttempt::factory(),
            'question_id' => Question::factory(),
            'selected_option' => $selectedOption,
            'is_correct' => $isCorrect,
            'is_marked_for_review' => $this->faker->boolean(20),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
    
    /**
     * Indicate that the response is correct.
     */
    public function correct(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'is_correct' => true,
            ];
        });
    }
    
    /**
     * Indicate that the response is incorrect.
     */
    public function incorrect(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'is_correct' => false,
            ];
        });
    }
} 