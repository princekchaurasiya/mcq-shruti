<?php

namespace Database\Factories;

use App\Models\MCQTest;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MCQTest>
 */
class MCQTestFactory extends Factory
{
    protected $model = MCQTest::class;
    
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subject = Subject::factory()->create();
        
        return [
            'teacher_id' => User::factory()->create(['role' => 'teacher'])->id,
            'subject_id' => $subject->id,
            'title' => $this->faker->sentence,
            'duration_minutes' => $this->faker->numberBetween(15, 120),
            'passing_percentage' => $this->faker->numberBetween(40, 80),
            'is_active' => $this->faker->boolean,
            'start_time' => now(),
            'end_time' => now()->addDays(7),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
} 