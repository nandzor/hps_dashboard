<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove CHECK constraints that conflict with encrypted data
        DB::statement("ALTER TABLE cctv_streams DROP CONSTRAINT IF EXISTS cctv_streams_stream_protocol_check");
        DB::statement("ALTER TABLE cctv_streams DROP CONSTRAINT IF EXISTS cctv_streams_status_check");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add the constraints (only if data is not encrypted)
        DB::statement("ALTER TABLE cctv_streams ADD CONSTRAINT cctv_streams_stream_protocol_check CHECK (stream_protocol IN ('rtsp', 'rtmp', 'hls', 'http', 'webrtc'))");
        DB::statement("ALTER TABLE cctv_streams ADD CONSTRAINT cctv_streams_status_check CHECK (status IN ('online', 'offline', 'error', 'maintenance'))");
    }
};