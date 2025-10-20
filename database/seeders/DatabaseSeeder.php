<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder {
    /**
     * Seed the application's database.
     */
    public function run(): void {
        // Seed in correct order (dependencies)
        $this->call([
            // 1. Users first (needed for created_by references)
            UserSeeder::class,

            // 2. Company structure
            CompanyGroupSeeder::class,
            CompanyBranchSeeder::class,

            // 3. Devices (depends on branches)
            DeviceMasterSeeder::class,

            // 4. Event settings (depends on branches and devices)
            BranchEventSettingSeeder::class,

            // 5. CCTV layouts (depends on users, branches, devices)
            CctvLayoutSeeder::class,

            // 6. API credentials (depends on users, branches, devices)
            ApiCredentialSeeder::class,

            // 7. Re-ID data (depends on branches and devices)
            ReIdMasterSeeder::class,
            ReIdBranchDetectionSeeder::class,

            // 8. Event logs (depends on branches, devices, and re-id data)
            EventLogSeeder::class,
        ]);

        $this->command->info('Database seeded successfully!');
        $this->command->info('');
        $this->command->info('=== Login Credentials ===');
        $this->command->info('Admin: admin@cctv.com / admin123');
        $this->command->info('User: operator.jakarta@cctv.com / password');
        $this->command->info('');
        $this->command->info('=== API Credentials ===');
        $this->command->info('Testing: cctv_test_dev_key / secret_test_dev_2024');
        $this->command->info('Admin: cctv_live_admin_global_key / secret_admin_global_2024');
    }
}
