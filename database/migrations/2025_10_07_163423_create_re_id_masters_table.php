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
        Schema::create('re_id_masters', function (Blueprint $table) {
            $table->id();
            $table->string('re_id', 100);
            $table->date('detection_date');
            $table->timestamp('detection_time')->nullable();
            $table->string('person_name', 150)->nullable();
            $table->jsonb('appearance_features')->nullable();
            $table->timestamp('first_detected_at')->nullable();
            $table->timestamp('last_detected_at')->nullable();
            $table->integer('total_detection_branch_count')->default(0);
            $table->integer('total_actual_count')->default(0);
            $table->string('status', 20)->default('active');
            $table->timestamps();

            // Unique constraints
            $table->unique(['re_id', 'detection_date']); // Unique by re_id + date (allows same re_id on different dates)

            // Indexes
            $table->index('re_id');
            $table->index('detection_date');
            $table->index('status');
            $table->index('first_detected_at');
            $table->index('last_detected_at');
        });

        // GIN index for JSONB appearance_features (PostgreSQL)
        DB::statement("CREATE INDEX idx_re_id_masters_appearance_features ON re_id_masters USING GIN (appearance_features)");

        // Add CHECK constraint for status (PostgreSQL)
        DB::statement("ALTER TABLE re_id_masters ADD CONSTRAINT re_id_masters_status_check CHECK (status IN ('active', 'inactive'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('re_id_masters');
    }
};
