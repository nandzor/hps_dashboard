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
        Schema::create('company_branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('company_groups')->onDelete('cascade');
            $table->string('branch_code', 50)->unique();
            $table->string('branch_name', 150);
            $table->string('city', 100);
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();

            // Indexes
            $table->index('group_id');
            $table->index('branch_code');
            $table->index('city');
            $table->index('status');
            $table->index(['latitude', 'longitude']);
        });

        // Add CHECK constraint for status (PostgreSQL)
        DB::statement("ALTER TABLE company_branches ADD CONSTRAINT company_branches_status_check CHECK (status IN ('active', 'inactive'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('company_branches');
    }
};
