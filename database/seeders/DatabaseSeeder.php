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
        // Seed subjects
        $this->call([
            SubjectSeeder::class,
        ]);

        // Create a default subject if not exists
        $subject = Subject::firstOrCreate(
            ['name' => 'General Studies'],
            ['description' => 'General knowledge and studies']
        );

        // Create users with roles
        $this->call([
            UserSeeder::class,
        ]);
        
        // Create sample tests
        $this->call([
            TestSeeder::class,
        ]);
        
        // Create sample questions
        $this->call([
            QuestionSeeder::class,
        ]);
        
        // Create sample test attempts
        $this->call([
            TestAttemptSeeder::class,
        ]);
    }
}
