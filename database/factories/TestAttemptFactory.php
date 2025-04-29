<?php

namespace Database\Factories;

use App\Models\TestAttempt;
use App\Models\MCQTest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TestAttempt>
 */
class TestAttemptFactory extends Factory
{
    protected $model = TestAttempt::class;
    
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = now()->subMinutes($this->faker->numberBetween(30, 120));
        $completedAt = $this->faker->boolean(80) ? $startedAt->copy()->addMinutes($this->faker->numberBetween(10, 60)) : null;
        
        return [
            'user_id' => User::factory()->create(['role' => 'student'])->id,
            'mcq_test_id' => MCQTest::factory(),
            'started_at' => $startedAt,
            'completed_at' => $completedAt,
            'score' => $completedAt ? $this->faker->numberBetween(0, 100) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
    
    /**
     * Indicate that the test attempt is completed.
     */
    public function completed(): Factory
    {
        return $this->state(function (array $attributes) {
            $startedAt = $attributes['started_at'] ?? now()->subMinutes($this->faker->numberBetween(30, 120));
            $completedAt = $startedAt->copy()->addMinutes($this->faker->numberBetween(10, 60));
            
            return [
                'completed_at' => $completedAt,
                'score' => $this->faker->numberBetween(0, 100),
            ];
        });
    }
    
    /**
     * Indicate that the test attempt is in progress.
     */
    public function inProgress(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'completed_at' => null,
                'score' => null,
            ];
        });
    }
} 