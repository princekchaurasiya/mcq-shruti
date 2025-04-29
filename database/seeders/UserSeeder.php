<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Admin;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Subject;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get a subject id
        $subject = Subject::first();
        if (!$subject) {
            $subject = Subject::create([
                'name' => 'General Studies',
                'description' => 'General knowledge and studies'
            ]);
        }

        // Create Admin User
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );
        
        // Create Admin record
        Admin::firstOrCreate(
            ['user_id' => $adminUser->id],
            [
                'name' => $adminUser->name,
                'email' => $adminUser->email,
                'role' => 'admin',
            ]
        );

        // Create Teacher User
        $teacherUser = User::firstOrCreate(
            ['email' => 'prince@example.com'],
            [
                'name' => 'Prince',
                'password' => Hash::make('password'),
                'role' => 'teacher',
            ]
        );
        
        // Create Teacher record
        Teacher::firstOrCreate(
            ['user_id' => $teacherUser->id],
            [
                'subject_id' => $subject->id,
                'qualification' => 'Masters',
                'experience_years' => 3,
            ]
        );

        // Create Student User
        $studentUser = User::firstOrCreate(
            ['email' => 'shruti@example.com'],
            [
                'name' => 'Shruti',
                'password' => Hash::make('password'),
                'role' => 'student',
            ]
        );
        
        // Create Student record
        Student::firstOrCreate(
            ['user_id' => $studentUser->id],
            [
                'roll_number' => 'STD-1001',
                'batch' => '2025',
                'admission_date' => now(),
            ]
        );
    }
} 