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
        // Since we removed the unique constraint on re_id alone,
        // we need to handle foreign key relationships differently

        // Option 1: Remove foreign key constraints temporarily
        // (Foreign keys to re_id_masters.re_id are no longer valid)

        // Drop foreign key constraints that reference re_id_masters.re_id (if they exist)
        try {
            Schema::table('re_id_branch_detections', function (Blueprint $table) {
                $table->dropForeign(['re_id']);
            });
        } catch (\Exception $e) {
            // Foreign key might not exist, continue
        }

        try {
            Schema::table('event_logs', function (Blueprint $table) {
                $table->dropForeign(['re_id']);
            });
        } catch (\Exception $e) {
            // Foreign key might not exist, continue
        }

        // Note: We're not recreating these foreign keys because:
        // 1. re_id is no longer unique in re_id_masters
        // 2. We need composite foreign keys (re_id + detection_date) which is complex
        // 3. Application logic handles the relationships correctly
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        // Restore foreign key constraints (for rollback)
        Schema::table('re_id_branch_detections', function (Blueprint $table) {
            $table->foreign('re_id')->references('re_id')->on('re_id_masters');
        });

        Schema::table('event_logs', function (Blueprint $table) {
            $table->foreign('re_id')->references('re_id')->on('re_id_masters');
        });
    }
};
