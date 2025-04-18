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
        if (!Schema::hasTable('mcq_tests')) {
            throw new \Exception('Required table (mcq_tests) does not exist. Please run its migration first.');
        }

        // Drop any existing foreign keys that reference this table
        if (Schema::hasTable('options')) {
            Schema::table('options', function (Blueprint $table) {
                if (Schema::hasColumn('options', 'question_id')) {
                    $table->dropForeign(['question_id']);
                }
            });
        }
        if (Schema::hasTable('student_responses')) {
            Schema::table('student_responses', function (Blueprint $table) {
                if (Schema::hasColumn('student_responses', 'question_id')) {
                    $table->dropForeign(['question_id']);
                }
            });
        }

        // Drop existing foreign keys if table exists
        if (Schema::hasTable('questions')) {
            Schema::table('questions', function (Blueprint $table) {
                if (Schema::hasColumn('questions', 'mcq_test_id')) {
                    $table->dropForeign(['mcq_test_id']);
                }
            });
        }

        // Drop and recreate the table
        Schema::dropIfExists('questions');
        
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mcq_test_id')->nullable()->constrained()->onDelete('set null');
            $table->text('question_text');
            $table->integer('marks')->default(1);
            $table->timestamps();
        });

        // Restore foreign keys in dependent tables
        if (Schema::hasTable('options')) {
            Schema::table('options', function (Blueprint $table) {
                if (Schema::hasColumn('options', 'question_id')) {
                    $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
                }
            });
        }
        if (Schema::hasTable('student_responses')) {
            Schema::table('student_responses', function (Blueprint $table) {
                if (Schema::hasColumn('student_responses', 'question_id')) {
                    $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
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
        if (Schema::hasTable('options')) {
            Schema::table('options', function (Blueprint $table) {
                if (Schema::hasColumn('options', 'question_id')) {
                    $table->dropForeign(['question_id']);
                }
            });
        }
        if (Schema::hasTable('student_responses')) {
            Schema::table('student_responses', function (Blueprint $table) {
                if (Schema::hasColumn('student_responses', 'question_id')) {
                    $table->dropForeign(['question_id']);
                }
            });
        }

        // Then drop foreign keys from this table
        if (Schema::hasTable('questions')) {
            Schema::table('questions', function (Blueprint $table) {
                if (Schema::hasColumn('questions', 'mcq_test_id')) {
                    $table->dropForeign(['mcq_test_id']);
                }
            });
        }

        Schema::dropIfExists('questions');
    }
}; 