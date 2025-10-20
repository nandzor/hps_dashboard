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
        Schema::create('re_id_branch_detections', function (Blueprint $table) {
            $table->id();
            $table->string('re_id', 100);
            $table->foreignId('branch_id')->constrained('company_branches')->onDelete('cascade');
            $table->string('device_id', 50);
            $table->timestamp('detection_timestamp');
            $table->integer('detected_count')->default(1);
            $table->jsonb('detection_data')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('device_id')->references('device_id')->on('device_masters')->onDelete('cascade');
            // Note: re_id foreign key constraint will be handled by fix migration
            // due to composite unique constraint in re_id_masters table

            // Indexes
            $table->index('re_id');
            $table->index('branch_id');
            $table->index('device_id');
            $table->index('detection_timestamp');
            $table->index(['re_id', 'branch_id', 'detection_timestamp']);
        });

        // GIN index for JSONB detection_data (PostgreSQL)
        DB::statement("CREATE INDEX idx_re_id_branch_detections_detection_data ON re_id_branch_detections USING GIN (detection_data)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('re_id_branch_detections');
    }
};
