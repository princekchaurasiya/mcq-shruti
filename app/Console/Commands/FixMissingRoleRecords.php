<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;

class FixMissingRoleRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:missing-role-records';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix missing student and teacher records for users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for users with missing role records...');
        
        // Fix student records
        $studentUsers = User::where('role', 'student')
            ->whereDoesntHave('student')
            ->get();
            
        $this->info("Found {$studentUsers->count()} student users without student records.");
        
        foreach ($studentUsers as $user) {
            DB::table('students')->insert([
                'user_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $this->info("Created student record for user ID: {$user->id} ({$user->name})");
        }
        
        // Fix teacher records
        $teacherUsers = User::where('role', 'teacher')
            ->whereDoesntHave('teacher')
            ->get();
            
        $this->info("Found {$teacherUsers->count()} teacher users without teacher records.");
        
        foreach ($teacherUsers as $user) {
            DB::table('teachers')->insert([
                'user_id' => $user->id,
                'qualification' => '',
                'experience_years' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $this->info("Created teacher record for user ID: {$user->id} ({$user->name})");
        }
        
        $this->info('All missing role records have been fixed!');
    }
}
