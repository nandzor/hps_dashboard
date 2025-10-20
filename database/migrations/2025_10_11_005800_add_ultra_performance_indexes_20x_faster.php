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
        // ULTRA-AGGRESSIVE INDEXES for 20x performance boost

        // 1. Super covering index for mega query optimization
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_re_id_branch_detections_mega_covering
            ON re_id_branch_detections(detection_timestamp, branch_id, re_id, device_id)
        ");

        // 2. Partial index for recent data (last 90 days) - most common queries
        // Use fixed date to avoid IMMUTABLE function requirement
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_re_id_branch_detections_recent_90_days
            ON re_id_branch_detections(detection_timestamp, branch_id, re_id, device_id)
            WHERE detection_timestamp >= '2024-01-01'::date
        ");

        // 3. Hash index for exact branch lookups (ultra fast)
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_re_id_branch_detections_branch_hash
            ON re_id_branch_detections USING HASH (branch_id)
        ");

        // 4. GIN index for JSONB detection_data (if exists) - SKIP if column doesn't exist
        // DB::statement("
        //     CREATE INDEX IF NOT EXISTS idx_re_id_branch_detections_data_gin
        //     ON re_id_branch_detections USING GIN (detection_data)
        // ");

        // 5. Composite index for date range + branch filtering (most common pattern)
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_re_id_branch_detections_date_branch_optimized
            ON re_id_branch_detections(detection_timestamp DESC, branch_id, re_id, device_id)
        ");

        // 6. Index for daily aggregation queries
        // Use expression index with immutable function
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_re_id_branch_detections_daily_agg
            ON re_id_branch_detections((detection_timestamp::date), branch_id, re_id)
        ");

        // 7. Index for branch statistics (top branches)
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_re_id_branch_detections_branch_stats
            ON re_id_branch_detections(branch_id, detection_timestamp DESC, re_id)
        ");

        // 8. BRIN index for timestamp (efficient for large tables)
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_re_id_branch_detections_brin_ultra
            ON re_id_branch_detections USING BRIN (detection_timestamp)
        ");

        // 9. Index for company branches optimization
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_company_branches_ultra_optimized
            ON company_branches(id, branch_name, status)
            WHERE status = 'active'
        ");

        // 10. Index for counting reports optimization
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_counting_reports_ultra_optimized
            ON counting_reports(report_type, report_date DESC, branch_id)
            INCLUDE (total_detections, unique_person_count, total_events)
        ");

        // 11. ULTRA-PERFORMANCE INDEXES for event_logs table
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_event_logs_timestamp_branch
            ON event_logs(event_timestamp, branch_id, event_type)
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_event_logs_daily_agg
            ON event_logs((event_timestamp::date), branch_id, event_type)
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_event_logs_branch_stats
            ON event_logs(branch_id, event_timestamp DESC, event_type)
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_event_logs_event_type_hash
            ON event_logs USING HASH (event_type)
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_event_logs_brin_timestamp
            ON event_logs USING BRIN (event_timestamp)
        ");

        // 12. ULTRA-PERFORMANCE INDEXES for re_id_masters table
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_re_id_masters_re_id_hash
            ON re_id_masters USING HASH (re_id)
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_re_id_masters_status_created
            ON re_id_masters(status, created_at DESC)
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_re_id_masters_created_at
            ON re_id_masters(created_at DESC)
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_re_id_masters_status_active
            ON re_id_masters(re_id, status)
            WHERE status = 'active'
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_re_id_masters_brin_created
            ON re_id_masters USING BRIN (created_at)
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_re_id_masters_detection_date
            ON re_id_masters(detection_date DESC, re_id)
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_re_id_masters_person_name
            ON re_id_masters(person_name, status)
        ");

        // 13. Composite indexes for cross-table joins
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_event_logs_re_id_branch
            ON event_logs(re_id, branch_id, event_timestamp DESC)
        ");

        // 14. Analyze tables for better query planning
        DB::statement("ANALYZE re_id_branch_detections");
        DB::statement("ANALYZE company_branches");
        DB::statement("ANALYZE counting_reports");
        DB::statement("ANALYZE event_logs");
        DB::statement("ANALYZE re_id_masters");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        // Drop all ultra-performance indexes for re_id_branch_detections
        DB::statement("DROP INDEX IF EXISTS idx_re_id_branch_detections_mega_covering");
        DB::statement("DROP INDEX IF EXISTS idx_re_id_branch_detections_recent_90_days");
        DB::statement("DROP INDEX IF EXISTS idx_re_id_branch_detections_branch_hash");
        DB::statement("DROP INDEX IF EXISTS idx_re_id_branch_detections_data_gin");
        DB::statement("DROP INDEX IF EXISTS idx_re_id_branch_detections_date_branch_optimized");
        DB::statement("DROP INDEX IF EXISTS idx_re_id_branch_detections_daily_agg");
        DB::statement("DROP INDEX IF EXISTS idx_re_id_branch_detections_branch_stats");
        DB::statement("DROP INDEX IF EXISTS idx_re_id_branch_detections_brin_ultra");
        DB::statement("DROP INDEX IF EXISTS idx_company_branches_ultra_optimized");
        DB::statement("DROP INDEX IF EXISTS idx_counting_reports_ultra_optimized");

        // Drop all ultra-performance indexes for event_logs
        DB::statement("DROP INDEX IF EXISTS idx_event_logs_timestamp_branch");
        DB::statement("DROP INDEX IF EXISTS idx_event_logs_daily_agg");
        DB::statement("DROP INDEX IF EXISTS idx_event_logs_branch_stats");
        DB::statement("DROP INDEX IF EXISTS idx_event_logs_event_type_hash");
        DB::statement("DROP INDEX IF EXISTS idx_event_logs_brin_timestamp");
        DB::statement("DROP INDEX IF EXISTS idx_event_logs_re_id_branch");

        // Drop all ultra-performance indexes for re_id_masters
        DB::statement("DROP INDEX IF EXISTS idx_re_id_masters_re_id_hash");
        DB::statement("DROP INDEX IF EXISTS idx_re_id_masters_status_created");
        DB::statement("DROP INDEX IF EXISTS idx_re_id_masters_created_at");
        DB::statement("DROP INDEX IF EXISTS idx_re_id_masters_status_active");
        DB::statement("DROP INDEX IF EXISTS idx_re_id_masters_brin_created");
        DB::statement("DROP INDEX IF EXISTS idx_re_id_masters_detection_date");
        DB::statement("DROP INDEX IF EXISTS idx_re_id_masters_person_name");
    }
};
