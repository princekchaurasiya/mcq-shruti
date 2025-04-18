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
            $table->boolean('is_marked_for_review')->default(false)->after('is_correct');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_responses', function (Blueprint $table) {
            $table->dropColumn('is_marked_for_review');
        });
    }
};
