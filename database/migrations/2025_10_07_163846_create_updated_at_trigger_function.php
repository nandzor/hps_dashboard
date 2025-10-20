<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        // Create trigger function for auto-updating updated_at (PostgreSQL)
        DB::unprepared("
            CREATE OR REPLACE FUNCTION update_updated_at_column()
            RETURNS TRIGGER AS $$
            BEGIN
                NEW.updated_at = CURRENT_TIMESTAMP;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        ");

        // Create triggers for all tables with updated_at column
        $tables = [
            'company_groups',
            'company_branches',
            'device_masters',
            're_id_masters',
            're_id_branch_detections',
            'branch_event_settings',
            'event_logs',
            'api_credentials',
            'api_usage_summary',
            'whatsapp_delivery_summary',
            'cctv_streams',
            'counting_reports',
            'cctv_layout_settings',
            'cctv_position_settings',
            'storage_files',
            'users',
        ];

        foreach ($tables as $table) {
            DB::unprepared("
                CREATE TRIGGER update_{$table}_updated_at
                BEFORE UPDATE ON {$table}
                FOR EACH ROW
                EXECUTE FUNCTION update_updated_at_column();
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        // Drop all triggers
        $tables = [
            'company_groups',
            'company_branches',
            'device_masters',
            're_id_masters',
            're_id_branch_detections',
            'branch_event_settings',
            'event_logs',
            'api_credentials',
            'api_usage_summary',
            'whatsapp_delivery_summary',
            'cctv_streams',
            'counting_reports',
            'cctv_layout_settings',
            'cctv_position_settings',
            'storage_files',
            'users',
        ];

        foreach ($tables as $table) {
            DB::unprepared("DROP TRIGGER IF EXISTS update_{$table}_updated_at ON {$table};");
        }

        // Drop trigger function
        DB::unprepared("DROP FUNCTION IF EXISTS update_updated_at_column();");
    }
};
