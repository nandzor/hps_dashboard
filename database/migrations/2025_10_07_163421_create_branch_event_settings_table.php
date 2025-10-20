<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('branch_event_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('company_branches')->onDelete('cascade');
            $table->string('device_id', 50);
            $table->boolean('is_active')->default(true);
            $table->boolean('send_image')->default(true);
            $table->boolean('send_message')->default(true);
            $table->boolean('whatsapp_enabled')->default(false);
            $table->jsonb('whatsapp_numbers')->nullable();
            $table->text('message_template')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('device_id')->references('device_id')->on('device_masters')->onDelete('cascade');

            // Unique constraint
            $table->unique(['branch_id', 'device_id']);

            // Indexes
            $table->index('branch_id');
            $table->index('device_id');
            $table->index('is_active');
        });

        // GIN index for JSONB whatsapp_numbers (PostgreSQL)
        DB::statement("CREATE INDEX idx_branch_event_settings_whatsapp_numbers ON branch_event_settings USING GIN (whatsapp_numbers)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('branch_event_settings');
    }
};
