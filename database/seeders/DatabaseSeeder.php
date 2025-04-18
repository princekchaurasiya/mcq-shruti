<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Subject;
use App\Models\Teacher;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SubjectSeeder::class,
            TeacherSeeder::class,
        ]);

        // Create admin user if not exists
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'role' => 'admin'
            ]
        );

        // Create a default subject if not exists
        $subject = Subject::firstOrCreate(
            ['name' => 'General Studies'],
            ['description' => 'General knowledge and studies']
        );

        // Create teacher profile for admin if not exists
        Teacher::firstOrCreate(
            ['user_id' => $admin->id],
            [
                'subject_id' => $subject->id,
                'qualification' => 'PhD',
                'experience_years' => 5
            ]
        );

        // Create a test teacher if not exists
        $teacher = User::firstOrCreate(
            ['email' => 'teacher@example.com'],
            [
                'name' => 'Test Teacher',
                'password' => bcrypt('password'),
                'role' => 'teacher'
            ]
        );

        Teacher::firstOrCreate(
            ['user_id' => $teacher->id],
            [
                'subject_id' => $subject->id,
                'qualification' => 'Masters',
                'experience_years' => 3
            ]
        );

        // Create a test student if not exists
        User::firstOrCreate(
            ['email' => 'student@example.com'],
            [
                'name' => 'Test Student',
                'password' => bcrypt('password'),
                'role' => 'student'
            ]
        );
    }
}
