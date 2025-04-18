<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            ['name' => 'Mathematics', 'description' => 'Study of numbers, quantities, and shapes'],
            ['name' => 'Physics', 'description' => 'Study of matter, energy, and their interactions'],
            ['name' => 'Chemistry', 'description' => 'Study of composition, structure, and properties of matter'],
            ['name' => 'Biology', 'description' => 'Study of living organisms and their vital processes'],
            ['name' => 'Computer Science', 'description' => 'Study of computers and computational systems'],
            ['name' => 'English', 'description' => 'Study of English language and literature'],
            ['name' => 'History', 'description' => 'Study of past events and human civilization'],
            ['name' => 'Geography', 'description' => 'Study of Earth and its features'],
        ];

        foreach ($subjects as $subject) {
            Subject::create($subject);
        }
    }
} 