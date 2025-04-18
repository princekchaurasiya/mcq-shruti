<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Student;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all users with 'student' role
        $studentUsers = DB::table('users')
            ->where('role', 'student')
            ->get();
            
        foreach ($studentUsers as $studentUser) {
            // Check if a student record already exists
            $exists = DB::table('students')
                ->where('user_id', $studentUser->id)
                ->exists();
                
            // If no student record exists, create one
            if (!$exists) {
                DB::table('students')->insert([
                    'user_id' => $studentUser->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                echo "Created student record for user ID: {$studentUser->id}\n";
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a data fix, so we don't need to do anything in the down method
    }
};
