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
        // Add composite indexes for dashboard queries optimization

        // 1. Covering index for dashboard statistics queries
        // This index covers the most common query pattern: date range + branch filter
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_re_id_branch_detections_dashboard_stats
            ON re_id_branch_detections(detection_timestamp, branch_id, re_id, device_id)
        ");

        // 2. Partial index for recent detections only (PostgreSQL specific)
        // This reduces index size and improves performance for recent records
        // Note: Using a fixed date instead of CURRENT_DATE for partial index
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_re_id_branch_detections_recent_dashboard
            ON re_id_branch_detections(detection_timestamp, branch_id, re_id, device_id)
            WHERE detection_timestamp >= '2024-01-01'::date
        ");

        // 3. Index for daily trend queries (date grouping)
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_re_id_branch_detections_daily_trend
            ON re_id_branch_detections(DATE(detection_timestamp), branch_id)
        ");

        // 4. Index for top branches queries (branch grouping with count)
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_re_id_branch_detections_top_branches
            ON re_id_branch_detections(branch_id, detection_timestamp)
        ");

        // 5. BRIN index for very large tables (PostgreSQL specific)
        // BRIN is efficient for large tables with natural ordering
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_re_id_branch_detections_brin_timestamp
            ON re_id_branch_detections USING BRIN (detection_timestamp)
        ");

        // 6. Composite index for export queries (includes all needed columns)
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_re_id_branch_detections_export_covering
            ON re_id_branch_detections(detection_timestamp, branch_id, re_id, device_id, detected_count)
        ");

        // 7. Index for branch relationship queries
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_re_id_branch_detections_branch_relationship
            ON re_id_branch_detections(branch_id, detection_timestamp DESC)
        ");

        // 8. Index for counting reports optimization
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_counting_reports_monthly_optimization
            ON counting_reports(report_type, report_date, branch_id)
        ");

        // 9. Index for company branches active status
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_company_branches_active
            ON company_branches(status, id, branch_name)
            WHERE status = 'active'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        // Drop all the indexes we created
        DB::statement("DROP INDEX IF EXISTS idx_re_id_branch_detections_dashboard_stats");
        DB::statement("DROP INDEX IF EXISTS idx_re_id_branch_detections_recent_dashboard");
        DB::statement("DROP INDEX IF EXISTS idx_re_id_branch_detections_daily_trend");
        DB::statement("DROP INDEX IF EXISTS idx_re_id_branch_detections_top_branches");
        DB::statement("DROP INDEX IF EXISTS idx_re_id_branch_detections_brin_timestamp");
        DB::statement("DROP INDEX IF EXISTS idx_re_id_branch_detections_export_covering");
        DB::statement("DROP INDEX IF EXISTS idx_re_id_branch_detections_branch_relationship");
        DB::statement("DROP INDEX IF EXISTS idx_counting_reports_monthly_optimization");
        DB::statement("DROP INDEX IF EXISTS idx_company_branches_active");
    }
};
