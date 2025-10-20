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
        // Create materialized views for EXTREME performance

        // 1. Materialized view for daily statistics (refreshed every hour)
        DB::statement("
            CREATE MATERIALIZED VIEW IF NOT EXISTS mv_daily_detection_stats AS
            SELECT
                DATE(detection_timestamp) as detection_date,
                branch_id,
                COUNT(*) as total_detections,
                COUNT(DISTINCT re_id) as unique_persons,
                COUNT(DISTINCT device_id) as unique_devices
            FROM re_id_branch_detections
            WHERE detection_timestamp >= CURRENT_DATE - INTERVAL '90 days'
            GROUP BY DATE(detection_timestamp), branch_id
        ");

        // 2. Materialized view for branch statistics (refreshed every hour)
        DB::statement("
            CREATE MATERIALIZED VIEW IF NOT EXISTS mv_branch_detection_stats AS
            SELECT
                branch_id,
                COUNT(*) as total_detections,
                COUNT(DISTINCT re_id) as unique_persons,
                COUNT(DISTINCT device_id) as unique_devices,
                MAX(detection_timestamp) as last_detection
            FROM re_id_branch_detections
            WHERE detection_timestamp >= CURRENT_DATE - INTERVAL '30 days'
            GROUP BY branch_id
        ");

        // 3. Materialized view for recent detections (last 7 days)
        DB::statement("
            CREATE MATERIALIZED VIEW IF NOT EXISTS mv_recent_detections AS
            SELECT
                detection_timestamp,
                re_id,
                branch_id,
                device_id,
                DATE(detection_timestamp) as detection_date
            FROM re_id_branch_detections
            WHERE detection_timestamp >= CURRENT_DATE - INTERVAL '7 days'
        ");

        // 4. Create indexes on materialized views for maximum performance
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_mv_daily_stats_date_branch
            ON mv_daily_detection_stats(detection_date, branch_id)
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_mv_branch_stats_branch_id
            ON mv_branch_detection_stats(branch_id)
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_mv_recent_detections_timestamp
            ON mv_recent_detections(detection_timestamp, branch_id)
        ");

        // 5. Create function to refresh materialized views
        DB::statement("
            CREATE OR REPLACE FUNCTION refresh_dashboard_materialized_views()
            RETURNS void AS $$
            BEGIN
                REFRESH MATERIALIZED VIEW mv_daily_detection_stats;
                REFRESH MATERIALIZED VIEW mv_branch_detection_stats;
                REFRESH MATERIALIZED VIEW mv_recent_detections;
            END;
            $$ LANGUAGE plpgsql;
        ");

        // 6. Create trigger to auto-refresh materialized views
        DB::statement("
            CREATE OR REPLACE FUNCTION trigger_refresh_dashboard_views()
            RETURNS trigger AS $$
            BEGIN
                -- Refresh views asynchronously (non-blocking)
                PERFORM pg_notify('refresh_dashboard_views', '');
                RETURN NULL;
            END;
            $$ LANGUAGE plpgsql;
        ");

        // 7. Create trigger on re_id_branch_detections table
        DB::statement("
            CREATE TRIGGER trigger_refresh_dashboard_views_after_insert
            AFTER INSERT ON re_id_branch_detections
            FOR EACH STATEMENT
            EXECUTE FUNCTION trigger_refresh_dashboard_views();
        ");

        DB::statement("
            CREATE TRIGGER trigger_refresh_dashboard_views_after_update
            AFTER UPDATE ON re_id_branch_detections
            FOR EACH STATEMENT
            EXECUTE FUNCTION trigger_refresh_dashboard_views();
        ");

        DB::statement("
            CREATE TRIGGER trigger_refresh_dashboard_views_after_delete
            AFTER DELETE ON re_id_branch_detections
            FOR EACH STATEMENT
            EXECUTE FUNCTION trigger_refresh_dashboard_views();
        ");

        // 8. Initial refresh of materialized views
        DB::statement("SELECT refresh_dashboard_materialized_views();");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        // Drop triggers
        DB::statement("DROP TRIGGER IF EXISTS trigger_refresh_dashboard_views_after_insert ON re_id_branch_detections");
        DB::statement("DROP TRIGGER IF EXISTS trigger_refresh_dashboard_views_after_update ON re_id_branch_detections");
        DB::statement("DROP TRIGGER IF EXISTS trigger_refresh_dashboard_views_after_delete ON re_id_branch_detections");

        // Drop functions
        DB::statement("DROP FUNCTION IF EXISTS trigger_refresh_dashboard_views()");
        DB::statement("DROP FUNCTION IF EXISTS refresh_dashboard_materialized_views()");

        // Drop materialized views
        DB::statement("DROP MATERIALIZED VIEW IF EXISTS mv_daily_detection_stats");
        DB::statement("DROP MATERIALIZED VIEW IF EXISTS mv_branch_detection_stats");
        DB::statement("DROP MATERIALIZED VIEW IF EXISTS mv_recent_detections");
    }
};
