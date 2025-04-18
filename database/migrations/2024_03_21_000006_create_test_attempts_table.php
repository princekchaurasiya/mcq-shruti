<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            Log::info('Starting creation of test_attempts table');

            Schema::create('test_attempts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('mcq_test_id')->constrained('mcq_tests')->onDelete('cascade');
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->decimal('score', 5, 2)->default(0);
                $table->timestamps();

                // Add indexes for better performance
                $table->index(['user_id', 'mcq_test_id']);
                $table->index(['started_at', 'completed_at']);
            });

            Log::info('Successfully created test_attempts table');

        } catch (\Exception $e) {
            Log::error('Failed to create test_attempts table', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_attempts');
    }
}; 