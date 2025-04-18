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
        Schema::table('student_responses', function (Blueprint $table) {
            // First drop the foreign key if it exists
            if (Schema::hasColumn('student_responses', 'selected_option_id')) {
                $table->dropForeign(['selected_option_id']);
                $table->dropColumn('selected_option_id');
            }
            
            // Add the new string column
            $table->string('selected_option')->nullable()->after('question_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_responses', function (Blueprint $table) {
            if (Schema::hasColumn('student_responses', 'selected_option')) {
                $table->dropColumn('selected_option');
            }
            
            $table->foreignId('selected_option_id')->nullable()->constrained('options')->onDelete('set null');
        });
    }
};
