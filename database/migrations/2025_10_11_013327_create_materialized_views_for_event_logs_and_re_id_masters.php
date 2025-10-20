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
        // Create materialized views for event_logs and re_id_masters tables

        // 1. Materialized view for event_logs daily statistics
        DB::statement("
            CREATE MATERIALIZED VIEW IF NOT EXISTS mv_event_logs_daily_stats AS
            SELECT
                DATE(event_timestamp) as event_date,
                branch_id,
                event_type,
                COUNT(*) as total_events,
                COUNT(CASE WHEN notification_sent = true THEN 1 END) as notifications_sent,
                COUNT(CASE WHEN notification_sent = false THEN 1 END) as notifications_failed
            FROM event_logs
            WHERE event_timestamp >= CURRENT_DATE - INTERVAL '90 days'
            GROUP BY DATE(event_timestamp), branch_id, event_type
        ");

        // 2. Materialized view for event_logs branch statistics
        DB::statement("
            CREATE MATERIALIZED VIEW IF NOT EXISTS mv_event_logs_branch_stats AS
            SELECT
                branch_id,
                event_type,
                COUNT(*) as total_events,
                COUNT(CASE WHEN notification_sent = true THEN 1 END) as notifications_sent,
                COUNT(CASE WHEN notification_sent = false THEN 1 END) as notifications_failed,
                MAX(event_timestamp) as last_event
            FROM event_logs
            WHERE event_timestamp >= CURRENT_DATE - INTERVAL '30 days'
            GROUP BY branch_id, event_type
        ");

        // 3. Materialized view for event_logs hourly statistics
        DB::statement("
            CREATE MATERIALIZED VIEW IF NOT EXISTS mv_event_logs_hourly_stats AS
            SELECT
                DATE_TRUNC('hour', event_timestamp) as event_hour,
                branch_id,
                event_type,
                COUNT(*) as events_per_hour
            FROM event_logs
            WHERE event_timestamp >= CURRENT_DATE - INTERVAL '7 days'
            GROUP BY DATE_TRUNC('hour', event_timestamp), branch_id, event_type
        ");

        // 4. Materialized view for re_id_masters daily statistics
        DB::statement("
            CREATE MATERIALIZED VIEW IF NOT EXISTS mv_re_id_masters_daily_stats AS
            SELECT
                detection_date,
                COUNT(*) as total_persons,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active_persons,
                COUNT(CASE WHEN status = 'inactive' THEN 1 END) as inactive_persons,
                AVG(total_actual_count) as avg_detection_count,
                MAX(total_actual_count) as max_detection_count
            FROM re_id_masters
            WHERE detection_date >= CURRENT_DATE - INTERVAL '90 days'
            GROUP BY detection_date
        ");

        // 5. Materialized view for re_id_masters branch statistics
        DB::statement("
            CREATE MATERIALIZED VIEW IF NOT EXISTS mv_re_id_masters_branch_stats AS
            SELECT
                detection_date,
                COUNT(*) as total_persons,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active_persons,
                SUM(total_actual_count) as total_detections,
                AVG(total_actual_count) as avg_detections_per_person
            FROM re_id_masters
            WHERE detection_date >= CURRENT_DATE - INTERVAL '30 days'
            GROUP BY detection_date
        ");

        // 6. Materialized view for re_id_masters appearance features
        DB::statement("
            CREATE MATERIALIZED VIEW IF NOT EXISTS mv_re_id_masters_appearance_stats AS
            SELECT
                detection_date,
                appearance_features->>'height' as height_category,
                appearance_features->>'clothing_colors' as clothing_colors,
                COUNT(*) as person_count
            FROM re_id_masters
            WHERE detection_date >= CURRENT_DATE - INTERVAL '30 days'
            AND appearance_features IS NOT NULL
            GROUP BY detection_date, appearance_features->>'height', appearance_features->>'clothing_colors'
        ");

        // 7. Create indexes on materialized views for maximum performance
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_mv_event_logs_daily_date_branch
            ON mv_event_logs_daily_stats(event_date, branch_id, event_type)
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_mv_event_logs_branch_branch_type
            ON mv_event_logs_branch_stats(branch_id, event_type)
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_mv_event_logs_hourly_hour_branch
            ON mv_event_logs_hourly_stats(event_hour, branch_id, event_type)
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_mv_re_id_masters_daily_date
            ON mv_re_id_masters_daily_stats(detection_date)
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_mv_re_id_masters_branch_date
            ON mv_re_id_masters_branch_stats(detection_date)
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_mv_re_id_masters_appearance_date
            ON mv_re_id_masters_appearance_stats(detection_date, height_category)
        ");

        // 8. Create function to refresh all materialized views
        DB::statement("
            CREATE OR REPLACE FUNCTION refresh_all_materialized_views()
            RETURNS void AS $$
            BEGIN
                -- Refresh re_id_branch_detections views
                REFRESH MATERIALIZED VIEW mv_daily_detection_stats;
                REFRESH MATERIALIZED VIEW mv_branch_detection_stats;
                REFRESH MATERIALIZED VIEW mv_recent_detections;

                -- Refresh event_logs views
                REFRESH MATERIALIZED VIEW mv_event_logs_daily_stats;
                REFRESH MATERIALIZED VIEW mv_event_logs_branch_stats;
                REFRESH MATERIALIZED VIEW mv_event_logs_hourly_stats;

                -- Refresh re_id_masters views
                REFRESH MATERIALIZED VIEW mv_re_id_masters_daily_stats;
                REFRESH MATERIALIZED VIEW mv_re_id_masters_branch_stats;
                REFRESH MATERIALIZED VIEW mv_re_id_masters_appearance_stats;
            END;
            $$ LANGUAGE plpgsql;
        ");

        // 9. Create triggers for auto-refresh on event_logs
        DB::statement("
            CREATE OR REPLACE FUNCTION trigger_refresh_event_logs_views()
            RETURNS trigger AS $$
            BEGIN
                PERFORM pg_notify('refresh_event_logs_views', '');
                RETURN NULL;
            END;
            $$ LANGUAGE plpgsql;
        ");

        DB::statement("
            CREATE TRIGGER trigger_refresh_event_logs_views_after_insert
            AFTER INSERT ON event_logs
            FOR EACH STATEMENT
            EXECUTE FUNCTION trigger_refresh_event_logs_views();
        ");

        DB::statement("
            CREATE TRIGGER trigger_refresh_event_logs_views_after_update
            AFTER UPDATE ON event_logs
            FOR EACH STATEMENT
            EXECUTE FUNCTION trigger_refresh_event_logs_views();
        ");

        // 10. Create triggers for auto-refresh on re_id_masters
        DB::statement("
            CREATE OR REPLACE FUNCTION trigger_refresh_re_id_masters_views()
            RETURNS trigger AS $$
            BEGIN
                PERFORM pg_notify('refresh_re_id_masters_views', '');
                RETURN NULL;
            END;
            $$ LANGUAGE plpgsql;
        ");

        DB::statement("
            CREATE TRIGGER trigger_refresh_re_id_masters_views_after_insert
            AFTER INSERT ON re_id_masters
            FOR EACH STATEMENT
            EXECUTE FUNCTION trigger_refresh_re_id_masters_views();
        ");

        DB::statement("
            CREATE TRIGGER trigger_refresh_re_id_masters_views_after_update
            AFTER UPDATE ON re_id_masters
            FOR EACH STATEMENT
            EXECUTE FUNCTION trigger_refresh_re_id_masters_views();
        ");

        // 11. Initial refresh of all materialized views
        DB::statement("SELECT refresh_all_materialized_views();");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        // Drop triggers
        DB::statement("DROP TRIGGER IF EXISTS trigger_refresh_event_logs_views_after_insert ON event_logs");
        DB::statement("DROP TRIGGER IF EXISTS trigger_refresh_event_logs_views_after_update ON event_logs");
        DB::statement("DROP TRIGGER IF EXISTS trigger_refresh_re_id_masters_views_after_insert ON re_id_masters");
        DB::statement("DROP TRIGGER IF EXISTS trigger_refresh_re_id_masters_views_after_update ON re_id_masters");

        // Drop functions
        DB::statement("DROP FUNCTION IF EXISTS trigger_refresh_event_logs_views()");
        DB::statement("DROP FUNCTION IF EXISTS trigger_refresh_re_id_masters_views()");
        DB::statement("DROP FUNCTION IF EXISTS refresh_all_materialized_views()");

        // Drop materialized views
        DB::statement("DROP MATERIALIZED VIEW IF EXISTS mv_event_logs_daily_stats");
        DB::statement("DROP MATERIALIZED VIEW IF EXISTS mv_event_logs_branch_stats");
        DB::statement("DROP MATERIALIZED VIEW IF EXISTS mv_event_logs_hourly_stats");
        DB::statement("DROP MATERIALIZED VIEW IF EXISTS mv_re_id_masters_daily_stats");
        DB::statement("DROP MATERIALIZED VIEW IF EXISTS mv_re_id_masters_branch_stats");
        DB::statement("DROP MATERIALIZED VIEW IF EXISTS mv_re_id_masters_appearance_stats");
    }
};
