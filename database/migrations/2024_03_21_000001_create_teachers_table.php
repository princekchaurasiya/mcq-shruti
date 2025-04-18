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
        // First check if the referenced tables exist
        if (!Schema::hasTable('users') || !Schema::hasTable('subjects')) {
            throw new \Exception('Required tables (users, subjects) do not exist. Please run their migrations first.');
        }

        // Drop any existing foreign keys that reference this table
        if (Schema::hasTable('mcq_tests')) {
            Schema::table('mcq_tests', function (Blueprint $table) {
                if (Schema::hasColumn('mcq_tests', 'teacher_id')) {
                    $table->dropForeign(['teacher_id']);
                }
            });
        }

        // Drop existing foreign keys if table exists
        if (Schema::hasTable('teachers')) {
            Schema::table('teachers', function (Blueprint $table) {
                if (Schema::hasColumn('teachers', 'user_id')) {
                    $table->dropForeign(['user_id']);
                }
                if (Schema::hasColumn('teachers', 'subject_id')) {
                    $table->dropForeign(['subject_id']);
                }
            });
        }

        // Drop and recreate the table
        Schema::dropIfExists('teachers');
        
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('subject_id')->nullable()->constrained()->onDelete('set null');
            $table->string('qualification')->nullable()->default('Not Specified');
            $table->integer('experience_years')->default(0);
            $table->timestamps();
        });

        // Restore the foreign key in mcq_tests if it exists
        if (Schema::hasTable('mcq_tests')) {
            Schema::table('mcq_tests', function (Blueprint $table) {
                if (Schema::hasColumn('mcq_tests', 'teacher_id')) {
                    $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First drop foreign keys from dependent tables
        if (Schema::hasTable('mcq_tests')) {
            Schema::table('mcq_tests', function (Blueprint $table) {
                if (Schema::hasColumn('mcq_tests', 'teacher_id')) {
                    $table->dropForeign(['teacher_id']);
                }
            });
        }

        // Then drop foreign keys from this table
        if (Schema::hasTable('teachers')) {
            Schema::table('teachers', function (Blueprint $table) {
                if (Schema::hasColumn('teachers', 'user_id')) {
                    $table->dropForeign(['user_id']);
                }
                if (Schema::hasColumn('teachers', 'subject_id')) {
                    $table->dropForeign(['subject_id']);
                }
            });
        }

        Schema::dropIfExists('teachers');
    }
};
