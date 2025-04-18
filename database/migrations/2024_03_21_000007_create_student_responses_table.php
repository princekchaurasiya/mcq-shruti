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
        Schema::create('student_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_attempt_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('question_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('selected_option_id')->nullable()->constrained('options')->onDelete('set null');
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_responses');
    }
}; 