<?php

namespace Database\Seeders;

use App\Models\WhatsAppSettings;
use Illuminate\Database\Seeder;

class WhatsAppSettingsSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        // Clear existing data
        WhatsAppSettings::truncate();

        // Create default WhatsApp settings
        WhatsAppSettings::create([
            'name' => 'Default',
            'description' => 'Default WhatsApp settings for all notifications',
            'phone_numbers' => ['081234567890', '081234567891'],
            'message_template' => 'Detection alert from {branch_name} - Device: {device_name} at {detection_time}',
            'is_active' => true,
            'is_default' => true,
        ]);

        // Create emergency WhatsApp settings
        WhatsAppSettings::create([
            'name' => 'Emergency',
            'description' => 'Emergency WhatsApp settings for critical alerts',
            'phone_numbers' => ['081234567892', '081234567893', '081234567894'],
            'message_template' => '🚨 EMERGENCY ALERT 🚨\nBranch: {branch_name}\nDevice: {device_name}\nTime: {detection_time}\nPlease check immediately!',
            'is_active' => true,
            'is_default' => false,
        ]);

        // Create admin WhatsApp settings
        WhatsAppSettings::create([
            'name' => 'Admin',
            'description' => 'Admin WhatsApp settings for system notifications',
            'phone_numbers' => ['081234567895'],
            'message_template' => 'Admin Notification\nBranch: {branch_name}\nDevice: {device_name}\nTime: {detection_time}\nStatus: {status}',
            'is_active' => true,
            'is_default' => false,
        ]);
    }
}
