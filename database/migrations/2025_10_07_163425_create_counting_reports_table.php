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
        Schema::create('counting_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_type', 20);
            $table->date('report_date');
            $table->foreignId('branch_id')->nullable()->constrained('company_branches')->onDelete('cascade');
            $table->integer('total_devices')->default(0);
            $table->integer('total_detections')->default(0);
            $table->integer('total_events')->default(0);
            $table->integer('unique_device_count')->default(0);
            $table->integer('unique_person_count')->default(0);
            $table->jsonb('report_data')->nullable();
            $table->timestamp('generated_at')->useCurrent();
            $table->timestamps();

            // Unique constraint
            $table->unique(['report_type', 'report_date', 'branch_id'], 'counting_reports_unique');

            // Indexes
            $table->index('report_type');
            $table->index('report_date');
            $table->index('branch_id');
            $table->index(['report_date', 'branch_id']);
        });

        // GIN index for JSONB report_data (PostgreSQL)
        DB::statement("CREATE INDEX idx_counting_reports_report_data ON counting_reports USING GIN (report_data)");

        // Add CHECK constraint for report_type (PostgreSQL)
        DB::statement("ALTER TABLE counting_reports ADD CONSTRAINT counting_reports_report_type_check CHECK (report_type IN ('daily', 'weekly', 'monthly', 'yearly'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('counting_reports');
    }
};
