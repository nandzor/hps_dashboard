<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cctv_streams', function (Blueprint $table) {
            // Change columns to TEXT to accommodate encrypted data
            $table->text('stream_url')->nullable()->change();
            $table->text('stream_username')->nullable()->change();
            $table->text('stream_password')->nullable()->change();
            $table->text('stream_protocol')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cctv_streams', function (Blueprint $table) {
            // Revert back to original column types
            $table->string('stream_url', 500)->nullable()->change();
            $table->string('stream_username', 100)->nullable()->change();
            $table->string('stream_password', 255)->nullable()->change();
            $table->string('stream_protocol', 20)->nullable()->change();
        });
    }
};