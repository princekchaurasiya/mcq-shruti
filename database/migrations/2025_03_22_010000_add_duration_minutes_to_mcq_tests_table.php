<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up()
    {
        try {
            Log::info('Starting migration to add duration_minutes column to mcq_tests table');
            
            Schema::table('mcq_tests', function (Blueprint $table) {
                if (!Schema::hasColumn('mcq_tests', 'duration_minutes')) {
                    $table->integer('duration_minutes')->after('description');
                    Log::info('Successfully added duration_minutes column');
                } else {
                    Log::info('duration_minutes column already exists');
                }
            });
            
        } catch (\Exception $e) {
            Log::error('Failed to add duration_minutes column', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function down()
    {
        try {
            Log::info('Starting rollback of duration_minutes column from mcq_tests table');
            
            Schema::table('mcq_tests', function (Blueprint $table) {
                if (Schema::hasColumn('mcq_tests', 'duration_minutes')) {
                    $table->dropColumn('duration_minutes');
                    Log::info('Successfully removed duration_minutes column');
                } else {
                    Log::info('duration_minutes column does not exist');
                }
            });
            
        } catch (\Exception $e) {
            Log::error('Failed to remove duration_minutes column', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}; 