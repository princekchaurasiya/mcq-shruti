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
        Schema::create('accessibility_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('font_size')->default('medium');
            $table->boolean('high_contrast')->default(false);
            $table->boolean('screen_reader_compatibility')->default(false);
            $table->integer('time_extension')->default(0);
            $table->text('time_extension_reason')->nullable();
            $table->string('time_extension_approval')->default('pending');
            $table->text('denial_reason')->nullable();
            $table->boolean('text_to_speech')->default(false);
            $table->boolean('keyboard_navigation')->default(false);
            $table->string('color_overlay')->nullable();
            $table->boolean('alternative_input')->default(false);
            $table->boolean('voice_input')->default(false);
            $table->boolean('live_assistance_allowed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accessibility_settings');
    }
}; 