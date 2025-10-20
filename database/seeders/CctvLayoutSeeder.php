<?php

namespace Database\Seeders;

use App\Models\CctvLayoutSetting;
use App\Models\CctvPositionSetting;
use App\Models\CompanyBranch;
use App\Models\DeviceMaster;
use App\Models\User;
use Illuminate\Database\Seeder;

class CctvLayoutSeeder extends Seeder {
    public function run(): void {
        $admin = User::where('role', 'admin')->first();
        $branches = CompanyBranch::all();
        $devices = DeviceMaster::all();

        // 1. Default 4-Window Layout
        $layout4 = CctvLayoutSetting::create([
            'layout_name' => 'Default 4-Window Layout',
            'layout_type' => '4-window',
            'total_positions' => 4,
            'is_default' => true,
            'is_active' => true,
            'description' => 'Standard quad view layout for main monitoring',
            'created_by' => $admin->id,
        ]);

        // Create positions for 4-window layout
        $positions4 = [
            ['position_number' => 1, 'position_name' => 'Main Entrance', 'quality' => 'high'],
            ['position_number' => 2, 'position_name' => 'Parking Area', 'quality' => 'high'],
            ['position_number' => 3, 'position_name' => 'Lobby View', 'quality' => 'medium'],
            ['position_number' => 4, 'position_name' => 'Entry Sensor', 'quality' => 'medium'],
        ];

        foreach ($positions4 as $index => $pos) {
            CctvPositionSetting::create([
                'layout_id' => $layout4->id,
                'position_number' => $pos['position_number'],
                'branch_id' => $branches->skip($index % $branches->count())->first()->id,
                'device_id' => $devices->skip($index % $devices->count())->first()->device_id,
                'position_name' => $pos['position_name'],
                'is_enabled' => true,
                'auto_switch' => false,
                'switch_interval' => 30,
                'quality' => $pos['quality'],
            ]);
        }

        // 2. Extended 6-Window Layout
        $layout6 = CctvLayoutSetting::create([
            'layout_name' => 'Extended 6-Window Layout',
            'layout_type' => '6-window',
            'total_positions' => 6,
            'is_default' => false,
            'is_active' => true,
            'description' => 'Extended view for comprehensive monitoring',
            'created_by' => $admin->id,
        ]);

        for ($i = 1; $i <= 6; $i++) {
            CctvPositionSetting::create([
                'layout_id' => $layout6->id,
                'position_number' => $i,
                'branch_id' => $branches->skip(($i - 1) % $branches->count())->first()->id,
                'device_id' => $devices->skip(($i - 1) % $devices->count())->first()->device_id,
                'position_name' => 'Position ' . $i,
                'is_enabled' => true,
                'auto_switch' => $i > 3,
                'switch_interval' => 60,
                'quality' => $i <= 3 ? 'high' : 'medium',
            ]);
        }

        // 3. Maximum 8-Window Layout
        $layout8 = CctvLayoutSetting::create([
            'layout_name' => 'Maximum 8-Window Layout',
            'layout_type' => '8-window',
            'total_positions' => 8,
            'is_default' => false,
            'is_active' => true,
            'description' => 'Maximum view for complete surveillance coverage',
            'created_by' => $admin->id,
        ]);

        for ($i = 1; $i <= 8; $i++) {
            CctvPositionSetting::create([
                'layout_id' => $layout8->id,
                'position_number' => $i,
                'branch_id' => $branches->skip(($i - 1) % $branches->count())->first()->id,
                'device_id' => $devices->skip(($i - 1) % $devices->count())->first()->device_id,
                'position_name' => 'Position ' . $i,
                'is_enabled' => true,
                'auto_switch' => $i % 2 === 0,
                'switch_interval' => 45,
                'quality' => $i <= 4 ? 'high' : 'medium',
            ]);
        }
    }
}
