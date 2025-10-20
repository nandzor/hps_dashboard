<?php

namespace Database\Seeders;

use App\Models\DeviceMaster;
use App\Models\CompanyBranch;
use Illuminate\Database\Seeder;

class DeviceMasterSeeder extends Seeder {
    public function run(): void {
        $branches = CompanyBranch::all();

        $devices = [
            // Jakarta Central devices
            [
                'device_id' => 'CAM_JKT001_001',
                'device_name' => 'Jakarta Central - Main Entrance Camera',
                'device_type' => 'camera',
                'branch_id' => $branches->where('branch_code', 'JKT001')->first()->id,
                'url' => 'rtsp://192.168.1.100:554/stream1',
                'username' => 'admin',
                'password' => 'camera123',
                'notes' => 'Main entrance 24/7 monitoring',
                'status' => 'active',
            ],
            [
                'device_id' => 'CAM_JKT001_002',
                'device_name' => 'Jakarta Central - Parking Area Camera',
                'device_type' => 'camera',
                'branch_id' => $branches->where('branch_code', 'JKT001')->first()->id,
                'url' => 'rtsp://192.168.1.101:554/stream1',
                'username' => 'admin',
                'password' => 'camera123',
                'notes' => 'Parking area with night vision',
                'status' => 'active',
            ],
            [
                'device_id' => 'NODE_JKT001_001',
                'device_name' => 'Jakarta Central - AI Detection Node',
                'device_type' => 'node_ai',
                'branch_id' => $branches->where('branch_code', 'JKT001')->first()->id,
                'url' => 'http://192.168.1.50:8080/api',
                'username' => 'api_user',
                'password' => 'nodeai123',
                'notes' => 'AI-powered person detection node',
                'status' => 'active',
            ],

            // Jakarta South devices
            [
                'device_id' => 'CAM_JKT002_001',
                'device_name' => 'Jakarta South - Lobby Camera',
                'device_type' => 'cctv',
                'branch_id' => $branches->where('branch_code', 'JKT002')->first()->id,
                'url' => 'rtsp://192.168.2.100:554/stream1',
                'username' => 'admin',
                'password' => 'camera123',
                'notes' => 'Lobby CCTV with PTZ control',
                'status' => 'active',
            ],
            [
                'device_id' => 'CAM_JKT002_002',
                'device_name' => 'Jakarta South - Reception Camera',
                'device_type' => 'camera',
                'branch_id' => $branches->where('branch_code', 'JKT002')->first()->id,
                'url' => 'rtsp://192.168.2.101:554/stream1',
                'username' => 'admin',
                'password' => 'camera123',
                'notes' => 'Reception area monitoring',
                'status' => 'active',
            ],

            // Bandung devices
            [
                'device_id' => 'CAM_BDG001_001',
                'device_name' => 'Bandung - Entry Sensor Camera',
                'device_type' => 'camera',
                'branch_id' => $branches->where('branch_code', 'BDG001')->first()->id,
                'url' => 'rtsp://192.168.3.100:554/stream1',
                'username' => 'admin',
                'password' => 'camera123',
                'notes' => 'Entry point sensor camera',
                'status' => 'active',
            ],
            [
                'device_id' => 'NODE_BDG001_001',
                'device_name' => 'Bandung - AI Detection Node',
                'device_type' => 'node_ai',
                'branch_id' => $branches->where('branch_code', 'BDG001')->first()->id,
                'url' => 'http://192.168.3.50:8080/api',
                'username' => 'api_user',
                'password' => 'nodeai123',
                'notes' => 'AI detection for Bandung branch',
                'status' => 'active',
            ],

            // Surabaya devices
            [
                'device_id' => 'CAM_SBY001_001',
                'device_name' => 'Surabaya - Main Gate Camera',
                'device_type' => 'camera',
                'branch_id' => $branches->where('branch_code', 'SBY001')->first()->id,
                'url' => 'rtsp://192.168.4.100:554/stream1',
                'username' => 'admin',
                'password' => 'camera123',
                'notes' => 'Main gate 24/7 monitoring',
                'status' => 'active',
            ],
            [
                'device_id' => 'MIKROTIK_SBY001',
                'device_name' => 'Surabaya - Network Router',
                'device_type' => 'mikrotik',
                'branch_id' => $branches->where('branch_code', 'SBY001')->first()->id,
                'url' => 'https://192.168.4.1',
                'username' => 'admin',
                'password' => 'mikrotik123',
                'notes' => 'Main network router for CCTV',
                'status' => 'active',
            ],
        ];

        foreach ($devices as $device) {
            DeviceMaster::create($device);
        }
    }
}
