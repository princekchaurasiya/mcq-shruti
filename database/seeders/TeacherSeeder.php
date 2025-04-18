<?php

namespace Database\Seeders;

use App\Models\Teacher;
use App\Models\User;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        // Get the first user with teacher role
        $user = User::where('role', 'teacher')->first();

        if (!$user) {
            // If no teacher user exists, create one
            $user = User::create([
                'name' => 'Teacher User',
                'email' => 'teacher@example.com',
                'password' => bcrypt('password'),
                'role' => 'teacher'
            ]);
        }

        // Get a random subject
        $subject = Subject::inRandomOrder()->first();

        if (!$subject) {
            // If no subjects exist, run the subject seeder first
            $this->call(SubjectSeeder::class);
            $subject = Subject::inRandomOrder()->first();
        }

        // Create teacher record if it doesn't exist
        if (!$user->teacher()->exists()) {
            Teacher::create([
                'user_id' => $user->id,
                'subject_id' => $subject->id,
                'qualification' => 'Ph.D. in Education',
                'experience_years' => 5
            ]);
        }
    }
} 