<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First check if the referenced table exists
        if (!Schema::hasTable('users')) {
            throw new \Exception('Required table (users) does not exist. Please run its migration first.');
        }

        // Drop existing foreign keys if table exists
        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                if (Schema::hasColumn('students', 'user_id')) {
                    $table->dropForeign(['user_id']);
                }
            });
        }

        // Drop and recreate the table
        Schema::dropIfExists('students');
        
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('roll_number')->unique()->nullable();
            $table->string('batch')->nullable();
            $table->date('admission_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                if (Schema::hasColumn('students', 'user_id')) {
                    $table->dropForeign(['user_id']);
                }
            });
        }
        Schema::dropIfExists('students');
    }
}; 