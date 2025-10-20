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
        Schema::create('storage_files', function (Blueprint $table) {
            $table->id();
            $table->string('file_path', 500)->unique();
            $table->string('file_type', 100);
            $table->bigInteger('file_size');
            $table->string('storage_disk', 50)->default('local');
            $table->string('related_table', 100)->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index('file_path');
            $table->index('file_type');
            $table->index('storage_disk');
            $table->index('related_table');
            $table->index(['related_table', 'related_id']);
            $table->index('uploaded_by');
            $table->index('created_at');
        });

        // GIN index for JSONB metadata (PostgreSQL)
        DB::statement("CREATE INDEX idx_storage_files_metadata ON storage_files USING GIN (metadata)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('storage_files');
    }
};
