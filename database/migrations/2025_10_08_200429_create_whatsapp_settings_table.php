<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('whatsapp_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., 'default', 'emergency', 'admin'
            $table->string('description')->nullable();
            $table->json('phone_numbers'); // Array of phone numbers
            $table->text('message_template');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false); // Only one can be default
            $table->timestamps();

            $table->index(['is_active', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('whatsapp_settings');
    }
};
