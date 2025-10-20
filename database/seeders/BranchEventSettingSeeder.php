<?php

namespace Database\Seeders;

use App\Models\BranchEventSetting;
use App\Models\CompanyBranch;
use App\Models\DeviceMaster;
use Illuminate\Database\Seeder;

class BranchEventSettingSeeder extends Seeder {
    public function run(): void {
        $branches = CompanyBranch::with('devices')->get();

        foreach ($branches as $branch) {
            foreach ($branch->devices as $device) {
                // Create event settings for each device
                BranchEventSetting::create([
                    'branch_id' => $branch->id,
                    'device_id' => $device->device_id,
                    'is_active' => true,
                    'send_image' => true,
                    'send_message' => true,
                    'send_notification' => true,
                    'whatsapp_enabled' => in_array($device->device_type, ['camera', 'node_ai', 'cctv']), // Enable for cameras
                    'whatsapp_numbers' => ['+6281234567890', '+6287654321098'], // Sample numbers
                    'message_template' => 'Alert from {branch_name}: Person detected at {device_name} on {timestamp}',
                    'notification_template' => 'Detection alert: Person detected at {device_name}',
                ]);
            }
        }
    }
}
