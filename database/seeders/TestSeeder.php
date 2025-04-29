<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MCQTest;
use App\Models\Teacher;
use App\Models\Subject;
use Carbon\Carbon;

class TestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first teacher
        $teacher = Teacher::first();
        if (!$teacher) {
            $this->command->error('Please run UserSeeder first to create a teacher');
            return;
        }

        // Get subject
        $subject = Subject::first();
        if (!$subject) {
            $this->command->error('Please run SubjectSeeder first to create subjects');
            return;
        }

        // Create active test
        MCQTest::firstOrCreate(
            ['title' => 'General Knowledge Test'],
            [
                'teacher_id' => $teacher->id,
                'subject_id' => $subject->id,
                'description' => 'A test of general knowledge covering various topics',
                'duration_minutes' => 60,
                'passing_percentage' => 70,
                'start_time' => Carbon::now()->subDay(),
                'end_time' => Carbon::now()->addDays(2),
                'is_active' => true
            ]
        );

        // Create completed test
        MCQTest::firstOrCreate(
            ['title' => 'Science Quiz'],
            [
                'teacher_id' => $teacher->id,
                'subject_id' => $subject->id,
                'description' => 'A quiz on basic science concepts',
                'duration_minutes' => 30,
                'passing_percentage' => 60,
                'start_time' => Carbon::now()->subDays(10),
                'end_time' => Carbon::now()->subDays(8),
                'is_active' => true
            ]
        );

        // Create upcoming test
        MCQTest::firstOrCreate(
            ['title' => 'Advanced Mathematics'],
            [
                'teacher_id' => $teacher->id,
                'subject_id' => $subject->id,
                'description' => 'Advanced mathematics problems for senior students',
                'duration_minutes' => 90,
                'passing_percentage' => 75,
                'start_time' => Carbon::now()->addDays(5),
                'end_time' => Carbon::now()->addDays(7),
                'is_active' => true
            ]
        );
    }
} 