<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('student_responses', function (Blueprint $table) {
            if (!$this->foreignKeyExists('student_responses', 'student_responses_test_attempt_id_foreign')) {
                $table->foreign('test_attempt_id')
                    ->references('id')
                    ->on('test_attempts')
                    ->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_responses', function (Blueprint $table) {
            if ($this->foreignKeyExists('student_responses', 'student_responses_test_attempt_id_foreign')) {
                $table->dropForeign(['test_attempt_id']);
            }
        });
    }

    /**
     * Check if a foreign key exists in the table.
     */
    private function foreignKeyExists(string $table, string $foreignKey): bool
    {
        return DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                           WHERE TABLE_NAME = ? AND CONSTRAINT_NAME = ?", [$table, $foreignKey]) ? true : false;
    }
};
