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
        Schema::create('cctv_position_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('layout_id')->constrained('cctv_layout_settings')->onDelete('cascade');
            $table->integer('position_number');
            $table->foreignId('branch_id')->nullable()->constrained('company_branches')->onDelete('set null');
            $table->string('device_id', 50)->nullable();
            $table->string('position_name', 100)->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->boolean('auto_switch')->default(false);
            $table->integer('switch_interval')->default(30);
            $table->string('quality', 20)->default('high');
            $table->string('resolution', 20)->default('1920x1080');
            $table->timestamps();

            // Foreign keys
            $table->foreign('device_id')->references('device_id')->on('device_masters')->onDelete('set null');

            // Unique constraint
            $table->unique(['layout_id', 'position_number']);

            // Indexes
            $table->index('layout_id');
            $table->index('position_number');
            $table->index('branch_id');
            $table->index('device_id');
        });

        // Add CHECK constraint for quality (PostgreSQL)
        DB::statement("ALTER TABLE cctv_position_settings ADD CONSTRAINT cctv_position_settings_quality_check CHECK (quality IN ('low', 'medium', 'high'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('cctv_position_settings');
    }
};
