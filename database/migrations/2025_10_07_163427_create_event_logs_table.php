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
        Schema::create('event_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('company_branches')->onDelete('cascade');
            $table->string('device_id', 50);
            $table->string('re_id', 100)->nullable();
            $table->string('event_type', 20)->default('detection');
            $table->integer('detected_count')->default(0);
            $table->string('image_path', 255)->nullable();
            $table->boolean('image_sent')->default(false);
            $table->boolean('message_sent')->default(false);
            $table->boolean('notification_sent')->default(false);
            $table->jsonb('event_data')->nullable();
            $table->timestamp('event_timestamp')->useCurrent();
            $table->timestamps();

            // Foreign keys
            $table->foreign('device_id')->references('device_id')->on('device_masters')->onDelete('cascade');
            // Note: re_id foreign key constraint will be handled by fix migration
            // due to composite unique constraint in re_id_masters table

            // Indexes
            $table->index('branch_id');
            $table->index('device_id');
            $table->index('re_id');
            $table->index('event_type');
            $table->index('event_timestamp');
            $table->index(['branch_id', 'event_timestamp']);
        });

        // GIN index for JSONB event_data (PostgreSQL)
        DB::statement("CREATE INDEX idx_event_logs_event_data ON event_logs USING GIN (event_data)");

        // Add CHECK constraint for event_type (PostgreSQL)
        DB::statement("ALTER TABLE event_logs ADD CONSTRAINT event_logs_event_type_check CHECK (event_type IN ('detection', 'alert', 'motion', 'manual'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('event_logs');
    }
};
