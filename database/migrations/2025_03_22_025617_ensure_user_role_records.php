<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all users
        $users = DB::table('users')->get();
        
        foreach ($users as $user) {
            echo "Processing user ID: {$user->id}, Role: {$user->role}\n";
            
            if ($user->role === 'student') {
                // Check if student record exists
                $exists = DB::table('students')->where('user_id', $user->id)->exists();
                
                if (!$exists) {
                    // Create student record
                    DB::table('students')->insert([
                        'user_id' => $user->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    echo "Created student record for user ID: {$user->id}\n";
                } else {
                    echo "Student record already exists for user ID: {$user->id}\n";
                }
            } elseif ($user->role === 'teacher') {
                // Check if teacher record exists
                $exists = DB::table('teachers')->where('user_id', $user->id)->exists();
                
                if (!$exists) {
                    // Create teacher record
                    DB::table('teachers')->insert([
                        'user_id' => $user->id,
                        'qualification' => '',
                        'experience_years' => 0,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    echo "Created teacher record for user ID: {$user->id}\n";
                } else {
                    echo "Teacher record already exists for user ID: {$user->id}\n";
                }
            } elseif ($user->role === 'admin') {
                // Check if admin record exists
                $exists = DB::table('admins')->where('user_id', $user->id)->exists();
                
                if (!$exists) {
                    // Create admin record
                    DB::table('admins')->insert([
                        'user_id' => $user->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    echo "Created admin record for user ID: {$user->id}\n";
                } else {
                    echo "Admin record already exists for user ID: {$user->id}\n";
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this migration as it's just inserting records
    }
};
