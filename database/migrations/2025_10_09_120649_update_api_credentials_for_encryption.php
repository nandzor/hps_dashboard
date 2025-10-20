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
        // Change api_key and api_secret columns to text to support encrypted data
        Schema::table('api_credentials', function (Blueprint $table) {
            // Change api_key from string(100) to text
            $table->text('api_key')->change();

            // Change api_secret from string(255) to text
            $table->text('api_secret')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        // Revert back to original column types
        Schema::table('api_credentials', function (Blueprint $table) {
            $table->string('api_key', 100)->change();
            $table->string('api_secret', 255)->change();
        });
    }
};
