<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('whatsapp_delivery_summary', function (Blueprint $table) {
            $table->id();
            $table->date('summary_date');
            $table->foreignId('branch_id')->constrained('company_branches')->onDelete('cascade');
            $table->string('device_id', 50);
            $table->integer('total_sent')->default(0);
            $table->integer('total_delivered')->default(0);
            $table->integer('total_failed')->default(0);
            $table->integer('total_pending')->default(0);
            $table->integer('avg_delivery_time_ms')->nullable();
            $table->integer('unique_recipients')->default(0);
            $table->integer('messages_with_image')->default(0);
            $table->timestamps();

            // Foreign keys
            $table->foreign('device_id')->references('device_id')->on('device_masters')->onDelete('cascade');

            // Unique constraint
            $table->unique(['summary_date', 'branch_id', 'device_id'], 'whatsapp_delivery_unique');

            // Indexes
            $table->index('summary_date');
            $table->index('branch_id');
            $table->index('device_id');
            $table->index(['summary_date', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('whatsapp_delivery_summary');
    }
};
