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
        if (!Schema::hasTable('teachers') || !Schema::hasTable('subjects')) {
            throw new \Exception('Required tables (teachers, subjects) do not exist. Please run their migrations first.');
        }

        // Drop any existing foreign keys that reference this table
        if (Schema::hasTable('questions')) {
            Schema::table('questions', function (Blueprint $table) {
                if (Schema::hasColumn('questions', 'mcq_test_id')) {
                    $table->dropForeign(['mcq_test_id']);
                }
            });
        }
        if (Schema::hasTable('test_attempts')) {
            Schema::table('test_attempts', function (Blueprint $table) {
                if (Schema::hasColumn('test_attempts', 'mcq_test_id')) {
                    $table->dropForeign(['mcq_test_id']);
                }
            });
        }

        // Drop existing foreign keys if table exists
        if (Schema::hasTable('mcq_tests')) {
            Schema::table('mcq_tests', function (Blueprint $table) {
                if (Schema::hasColumn('mcq_tests', 'teacher_id')) {
                    $table->dropForeign(['teacher_id']);
                }
                if (Schema::hasColumn('mcq_tests', 'subject_id')) {
                    $table->dropForeign(['subject_id']);
                }
            });
        }

        // Drop and recreate the table
        Schema::dropIfExists('mcq_tests');
        
        Schema::create('mcq_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('subject_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('duration')->comment('Duration in minutes')->default(60);
            $table->integer('total_marks')->default(0);
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Restore foreign keys in dependent tables
        if (Schema::hasTable('questions')) {
            Schema::table('questions', function (Blueprint $table) {
                if (Schema::hasColumn('questions', 'mcq_test_id')) {
                    $table->foreign('mcq_test_id')->references('id')->on('mcq_tests')->onDelete('cascade');
                }
            });
        }
        if (Schema::hasTable('test_attempts')) {
            Schema::table('test_attempts', function (Blueprint $table) {
                if (Schema::hasColumn('test_attempts', 'mcq_test_id')) {
                    $table->foreign('mcq_test_id')->references('id')->on('mcq_tests')->onDelete('cascade');
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
        if (Schema::hasTable('questions')) {
            Schema::table('questions', function (Blueprint $table) {
                if (Schema::hasColumn('questions', 'mcq_test_id')) {
                    $table->dropForeign(['mcq_test_id']);
                }
            });
        }
        if (Schema::hasTable('test_attempts')) {
            Schema::table('test_attempts', function (Blueprint $table) {
                if (Schema::hasColumn('test_attempts', 'mcq_test_id')) {
                    $table->dropForeign(['mcq_test_id']);
                }
            });
        }

        // Then drop foreign keys from this table
        if (Schema::hasTable('mcq_tests')) {
            Schema::table('mcq_tests', function (Blueprint $table) {
                if (Schema::hasColumn('mcq_tests', 'teacher_id')) {
                    $table->dropForeign(['teacher_id']);
                }
                if (Schema::hasColumn('mcq_tests', 'subject_id')) {
                    $table->dropForeign(['subject_id']);
                }
            });
        }

        Schema::dropIfExists('mcq_tests');
    }
}; 