<?php

namespace Database\Seeders;

use App\Models\ApiCredential;
use App\Models\User;
use App\Models\CompanyBranch;
use App\Models\DeviceMaster;
use Illuminate\Database\Seeder;

class ApiCredentialSeeder extends Seeder {
    public function run(): void {
        $admin = User::where('role', 'admin')->first();
        $jakartaBranch = CompanyBranch::where('branch_code', 'JKT001')->first();
        $camera = DeviceMaster::where('device_id', 'CAM_JKT001_001')->first();

        $credentials = [
            // 1. Global Admin API Key (full access)
            [
                'credential_name' => 'Global Admin API Key',
                'api_key' => 'cctv_live_admin_global_key',
                'api_secret' => 'secret_admin_global_2024',
                'branch_id' => null, // Global access
                'device_id' => null, // All devices
                'permissions' => [
                    'read' => true,
                    'write' => true,
                    'delete' => true,
                ],
                'rate_limit' => 10000, // High limit for admin
                'expires_at' => null, // Never expires
                'status' => 'active',
                'created_by' => $admin->id,
            ],

            // 2. Branch-Scoped API Key (Jakarta branch only)
            [
                'credential_name' => 'Jakarta Branch API Key',
                'api_key' => 'cctv_live_jakarta_branch',
                'api_secret' => 'secret_jakarta_2024',
                'branch_id' => $jakartaBranch->id, // Jakarta only
                'device_id' => null, // All devices in branch
                'permissions' => [
                    'read' => true,
                    'write' => true,
                    'delete' => false,
                ],
                'rate_limit' => 5000,
                'expires_at' => now()->addYear(), // 1 year validity
                'status' => 'active',
                'created_by' => $admin->id,
            ],

            // 3. Device-Scoped API Key (specific camera)
            [
                'credential_name' => 'Camera JKT001-001 API Key',
                'api_key' => 'cctv_live_camera_jkt001_001',
                'api_secret' => 'secret_camera_jkt001',
                'branch_id' => $jakartaBranch->id,
                'device_id' => $camera->device_id, // Specific device only
                'permissions' => [
                    'read' => true,
                    'write' => true,
                    'delete' => false,
                ],
                'rate_limit' => 1000,
                'expires_at' => now()->addMonths(6), // 6 months validity
                'status' => 'active',
                'created_by' => $admin->id,
            ],

            // 4. Read-Only API Key (for monitoring/dashboard)
            [
                'credential_name' => 'Dashboard Read-Only API Key',
                'api_key' => 'cctv_live_readonly_dashboard',
                'api_secret' => 'secret_readonly_2024',
                'branch_id' => null, // All branches
                'device_id' => null, // All devices
                'permissions' => [
                    'read' => true,
                    'write' => false,
                    'delete' => false,
                ],
                'rate_limit' => 2000,
                'expires_at' => null, // Never expires
                'status' => 'active',
                'created_by' => $admin->id,
            ],

            // 5. Testing API Key (for development)
            [
                'credential_name' => 'Development Testing Key',
                'api_key' => 'cctv_test_dev_key',
                'api_secret' => 'secret_test_dev_2024',
                'branch_id' => null,
                'device_id' => null,
                'permissions' => [
                    'read' => true,
                    'write' => true,
                    'delete' => true,
                ],
                'rate_limit' => 50000, // High limit for testing
                'expires_at' => now()->addMonths(3),
                'status' => 'active',
                'created_by' => $admin->id,
            ],
        ];

        foreach ($credentials as $credential) {
            ApiCredential::create($credential);
        }

        $this->command->info('Created 5 API credentials (admin, branch, device, readonly, testing)');
    }
}
