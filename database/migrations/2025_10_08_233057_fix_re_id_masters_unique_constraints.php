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
        // Drop the incorrect unique constraint on re_id alone (if it exists)
        // This allows same re_id to exist on different dates
        try {
            Schema::table('re_id_masters', function (Blueprint $table) {
                $table->dropUnique(['re_id']);
            });
        } catch (\Exception $e) {
            // Constraint might not exist, continue
        }

        // Ensure the correct unique constraint exists (re_id + detection_date)
        // This was already created in the original migration but let's make sure
        try {
            Schema::table('re_id_masters', function (Blueprint $table) {
                $table->unique(['re_id', 'detection_date'], 're_id_masters_re_id_detection_date_unique');
            });
        } catch (\Exception $e) {
            // Constraint might already exist, continue
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        // Restore the incorrect unique constraint (for rollback)
        Schema::table('re_id_masters', function (Blueprint $table) {
            $table->dropUnique(['re_id', 'detection_date']);
            $table->unique('re_id');
        });
    }
};
