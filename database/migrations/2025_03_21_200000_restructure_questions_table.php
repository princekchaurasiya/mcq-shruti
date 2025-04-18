<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up()
    {
        try {
            Log::info('Starting questions table restructuring...');

            // Step 1: Drop foreign key if it exists
            try {
                Schema::table('questions', function (Blueprint $table) {
                    $table->dropForeign(['mcq_test_id']);
                });
                Log::info('Successfully dropped foreign key on mcq_test_id');
            } catch (\Exception $e) {
                Log::info('Foreign key constraint does not exist or already dropped: ' . $e->getMessage());
            }

            // Step 2: Drop existing columns if they exist
            Schema::table('questions', function (Blueprint $table) {
                $columnsToCheck = [
                    'image_path',
                    'options',
                    'correct_option',
                    'explanation'
                ];

                foreach ($columnsToCheck as $column) {
                    try {
                        if (Schema::hasColumn('questions', $column)) {
                            $table->dropColumn($column);
                            Log::info("Successfully dropped column: {$column}");
                        } else {
                            Log::info("Column {$column} does not exist, skipping...");
                        }
                    } catch (\Exception $e) {
                        Log::warning("Error dropping column {$column}: " . $e->getMessage());
                    }
                }
            });

            // Step 3: Add all required columns with correct types
            Schema::table('questions', function (Blueprint $table) {
                try {
                    if (!Schema::hasColumn('questions', 'options')) {
                        $table->json('options')->after('question_text');
                        Log::info('Added options column');
                    }
                    if (!Schema::hasColumn('questions', 'correct_option')) {
                        $table->json('correct_option')->after('options');
                        Log::info('Added correct_option column');
                    }
                    if (!Schema::hasColumn('questions', 'image_path')) {
                        $table->string('image_path')->nullable()->after('correct_option');
                        Log::info('Added image_path column');
                    }
                    if (!Schema::hasColumn('questions', 'explanation')) {
                        $table->text('explanation')->nullable()->after('image_path');
                        Log::info('Added explanation column');
                    }
                } catch (\Exception $e) {
                    Log::error('Error adding columns: ' . $e->getMessage());
                    throw $e;
                }
            });

            // Step 4: Add back the foreign key constraint
            try {
                Schema::table('questions', function (Blueprint $table) {
                    $table->foreign('mcq_test_id')
                        ->references('id')
                        ->on('mcq_tests')
                        ->onDelete('cascade');
                });
                Log::info('Successfully added foreign key constraint');
            } catch (\Exception $e) {
                Log::warning('Error adding foreign key constraint: ' . $e->getMessage());
            }

            Log::info('Successfully completed questions table restructuring');

        } catch (\Exception $e) {
            Log::error('Fatal error during migration: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            throw $e;
        }
    }

    public function down()
    {
        try {
            Log::info('Starting questions table rollback...');

            // Remove foreign key if it exists
            try {
                Schema::table('questions', function (Blueprint $table) {
                    $table->dropForeign(['mcq_test_id']);
                });
                Log::info('Successfully dropped foreign key during rollback');
            } catch (\Exception $e) {
                Log::info('Foreign key already dropped or does not exist during rollback: ' . $e->getMessage());
            }

            // Drop the added columns
            try {
                Schema::table('questions', function (Blueprint $table) {
                    $columnsToCheck = [
                        'options',
                        'correct_option',
                        'image_path',
                        'explanation'
                    ];

                    foreach ($columnsToCheck as $column) {
                        if (Schema::hasColumn('questions', $column)) {
                            $table->dropColumn($column);
                            Log::info("Successfully dropped column during rollback: {$column}");
                        }
                    }
                });
            } catch (\Exception $e) {
                Log::error('Error dropping columns during rollback: ' . $e->getMessage());
                throw $e;
            }

            Log::info('Successfully completed questions table rollback');

        } catch (\Exception $e) {
            Log::error('Fatal error during rollback: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            throw $e;
        }
    }
}; 