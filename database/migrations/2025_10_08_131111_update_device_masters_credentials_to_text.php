<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('device_masters', function (Blueprint $table) {
            // Change columns to TEXT to support encrypted data
            $table->text('url')->nullable()->change();
            $table->text('username')->nullable()->change();
            $table->text('password')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('device_masters', function (Blueprint $table) {
            // Revert to original VARCHAR length
            $table->string('url', 255)->nullable()->change();
            $table->string('username', 255)->nullable()->change();
            $table->string('password', 255)->nullable()->change();
        });
    }
};
