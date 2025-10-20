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
        Schema::create('cctv_streams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('company_branches')->onDelete('cascade');
            $table->string('device_id', 50);
            $table->string('stream_name', 150);
            $table->string('stream_url', 500);
            $table->string('stream_username', 100)->nullable();
            $table->string('stream_password', 255)->nullable();
            $table->string('stream_protocol', 20)->default('rtsp');
            $table->integer('position')->default(1);
            $table->string('resolution', 20)->default('1920x1080');
            $table->integer('fps')->default(30);
            $table->string('status', 20)->default('active');
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('device_id')->references('device_id')->on('device_masters')->onDelete('cascade');

            // Indexes
            $table->index('branch_id');
            $table->index('device_id');
            $table->index('position');
            $table->index('status');
        });

        // Add CHECK constraint for stream_protocol (PostgreSQL)
        DB::statement("ALTER TABLE cctv_streams ADD CONSTRAINT cctv_streams_stream_protocol_check CHECK (stream_protocol IN ('rtsp', 'rtmp', 'hls', 'http', 'webrtc'))");

        // Add CHECK constraint for status (PostgreSQL)
        DB::statement("ALTER TABLE cctv_streams ADD CONSTRAINT cctv_streams_status_check CHECK (status IN ('online', 'offline', 'error', 'maintenance'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('cctv_streams');
    }
};
