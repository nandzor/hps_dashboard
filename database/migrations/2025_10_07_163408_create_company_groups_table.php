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
        Schema::create('company_groups', function (Blueprint $table) {
            $table->id();
            $table->string('province_code', 10)->unique();
            $table->string('province_name', 100);
            $table->string('group_name', 150);
            $table->text('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();

            // Indexes
            $table->index('province_code');
            $table->index('status');
        });

        // Add CHECK constraint for status (PostgreSQL)
        DB::statement("ALTER TABLE company_groups ADD CONSTRAINT company_groups_status_check CHECK (status IN ('active', 'inactive'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('company_groups');
    }
};
