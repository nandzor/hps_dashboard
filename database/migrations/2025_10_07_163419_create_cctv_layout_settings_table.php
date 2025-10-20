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
        Schema::create('cctv_layout_settings', function (Blueprint $table) {
            $table->id();
            $table->string('layout_name', 150);
            $table->string('layout_type', 20);
            $table->text('description')->nullable();
            $table->integer('total_positions');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index('layout_type');
            $table->index('is_default');
            $table->index('is_active');
            $table->index('created_by');
        });

        // Add CHECK constraint for layout_type (PostgreSQL)
        DB::statement("ALTER TABLE cctv_layout_settings ADD CONSTRAINT cctv_layout_settings_layout_type_check CHECK (layout_type IN ('4-window', '6-window', '8-window'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('cctv_layout_settings');
    }
};
