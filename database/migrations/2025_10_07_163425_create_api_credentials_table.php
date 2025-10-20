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
        Schema::create('api_credentials', function (Blueprint $table) {
            $table->id();
            $table->string('credential_name', 150);
            $table->string('api_key', 100)->unique();
            $table->string('api_secret', 255);
            $table->foreignId('branch_id')->nullable()->constrained('company_branches')->onDelete('cascade');
            $table->string('device_id', 50)->nullable();
            $table->jsonb('permissions')->nullable();
            $table->integer('rate_limit')->default(1000);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->string('status', 20)->default('active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('device_id')->references('device_id')->on('device_masters')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index('api_key');
            $table->index('branch_id');
            $table->index('device_id');
            $table->index('status');
            $table->index('expires_at');
        });

        // GIN index for JSONB permissions (PostgreSQL)
        DB::statement("CREATE INDEX idx_api_credentials_permissions ON api_credentials USING GIN (permissions)");

        // Add CHECK constraint for status (PostgreSQL)
        DB::statement("ALTER TABLE api_credentials ADD CONSTRAINT api_credentials_status_check CHECK (status IN ('active', 'inactive', 'expired'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('api_credentials');
    }
};
