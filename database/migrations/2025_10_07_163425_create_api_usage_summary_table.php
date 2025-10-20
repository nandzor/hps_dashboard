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
        Schema::create('api_usage_summary', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_credential_id')->constrained('api_credentials')->onDelete('cascade');
            $table->date('summary_date');
            $table->string('endpoint', 255);
            $table->string('method', 10);
            $table->integer('total_requests')->default(0);
            $table->integer('success_requests')->default(0);
            $table->integer('error_requests')->default(0);
            $table->integer('avg_response_time_ms')->nullable();
            $table->integer('max_response_time_ms')->nullable();
            $table->integer('min_response_time_ms')->nullable();
            $table->integer('avg_query_count')->nullable();
            $table->integer('max_query_count')->nullable();
            $table->bigInteger('avg_memory_usage')->nullable();
            $table->bigInteger('max_memory_usage')->nullable();
            $table->timestamps();

            // Unique constraint
            $table->unique(['api_credential_id', 'summary_date', 'endpoint', 'method'], 'api_usage_unique');

            // Indexes
            $table->index('api_credential_id');
            $table->index('summary_date');
            $table->index('endpoint');
            $table->index('method');
            $table->index(['summary_date', 'api_credential_id']);
        });

        // Add CHECK constraint for method (PostgreSQL)
        DB::statement("ALTER TABLE api_usage_summary ADD CONSTRAINT api_usage_summary_method_check CHECK (method IN ('GET', 'POST', 'PUT', 'DELETE', 'PATCH'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('api_usage_summary');
    }
};
