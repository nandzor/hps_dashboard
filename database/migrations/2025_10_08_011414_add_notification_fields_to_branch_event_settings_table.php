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
        Schema::table('branch_event_settings', function (Blueprint $table) {
            // Check if columns don't exist before adding
            if (!Schema::hasColumn('branch_event_settings', 'send_notification')) {
                $table->boolean('send_notification')->default(true)->after('send_message');
                $table->index('send_notification');
            }

            if (!Schema::hasColumn('branch_event_settings', 'notification_template')) {
                $table->text('notification_template')->nullable()->after('message_template');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branch_event_settings', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['send_notification']);

            // Drop columns
            $table->dropColumn(['send_notification', 'notification_template']);
        });
    }
};
