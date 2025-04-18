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
        if (!Schema::hasTable('questions')) {
            throw new \Exception('Required table (questions) does not exist. Please run its migration first.');
        }

        // Drop any existing foreign keys that reference this table
        if (Schema::hasTable('student_responses')) {
            Schema::table('student_responses', function (Blueprint $table) {
                if (Schema::hasColumn('student_responses', 'selected_option_id')) {
                    $table->dropForeign(['selected_option_id']);
                }
            });
        }

        // Drop existing foreign keys if table exists
        if (Schema::hasTable('options')) {
            Schema::table('options', function (Blueprint $table) {
                if (Schema::hasColumn('options', 'question_id')) {
                    $table->dropForeign(['question_id']);
                }
            });
        }

        // Drop and recreate the table
        Schema::dropIfExists('options');
        
        Schema::create('options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->nullable()->constrained()->onDelete('set null');
            $table->text('option_text');
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });

        // Restore foreign keys in dependent tables
        if (Schema::hasTable('student_responses')) {
            Schema::table('student_responses', function (Blueprint $table) {
                if (Schema::hasColumn('student_responses', 'selected_option_id')) {
                    $table->foreign('selected_option_id')->references('id')->on('options')->onDelete('cascade');
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
        if (Schema::hasTable('student_responses')) {
            Schema::table('student_responses', function (Blueprint $table) {
                if (Schema::hasColumn('student_responses', 'selected_option_id')) {
                    $table->dropForeign(['selected_option_id']);
                }
            });
        }

        // Then drop foreign keys from this table
        if (Schema::hasTable('options')) {
            Schema::table('options', function (Blueprint $table) {
                if (Schema::hasColumn('options', 'question_id')) {
                    $table->dropForeign(['question_id']);
                }
            });
        }

        Schema::dropIfExists('options');
    }
}; 