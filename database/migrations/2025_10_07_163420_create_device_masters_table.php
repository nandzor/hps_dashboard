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
        Schema::create('device_masters', function (Blueprint $table) {
            $table->id();
            $table->string('device_id', 50)->unique();
            $table->string('device_name', 150);
            $table->string('device_type', 20)->default('camera');
            $table->foreignId('branch_id')->constrained('company_branches')->onDelete('cascade');
            $table->string('url', 255)->nullable();
            $table->string('username', 100)->nullable();
            $table->string('password', 255)->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();

            // Indexes
            $table->index('device_id');
            $table->index('device_type');
            $table->index('branch_id');
            $table->index('status');
        });

        // Add CHECK constraint for device_type (PostgreSQL)
        DB::statement("ALTER TABLE device_masters ADD CONSTRAINT device_masters_device_type_check CHECK (device_type IN ('camera', 'node_ai', 'mikrotik', 'cctv'))");

        // Add CHECK constraint for status (PostgreSQL)
        DB::statement("ALTER TABLE device_masters ADD CONSTRAINT device_masters_status_check CHECK (status IN ('active', 'inactive'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('device_masters');
    }
};
