<?php

namespace Database\Seeders;

use App\Models\CompanyBranch;
use App\Models\CompanyGroup;
use Illuminate\Database\Seeder;

class CompanyBranchSeeder extends Seeder {
    public function run(): void {
        $jakartaGroup = CompanyGroup::where('province_code', 'JKT')->first();
        $bandungGroup = CompanyGroup::where('province_code', 'JABAR')->first();
        $surabayaGroup = CompanyGroup::where('province_code', 'JATIM')->first();

        $branches = [
            // Jakarta branches
            [
                'group_id' => $jakartaGroup->id,
                'branch_code' => 'JKT001',
                'branch_name' => 'Jakarta Central Branch',
                'city' => 'Jakarta Pusat',
                'address' => 'Jl. Thamrin No.1, Jakarta Pusat',
                'phone' => '021-11111111',
                'email' => 'jkt.central@cctv.com',
                'latitude' => -6.200000,
                'longitude' => 106.816666,
                'status' => 'active',
            ],
            [
                'group_id' => $jakartaGroup->id,
                'branch_code' => 'JKT002',
                'branch_name' => 'Jakarta South Branch',
                'city' => 'Jakarta Selatan',
                'address' => 'Jl. Sudirman No.100, Jakarta Selatan',
                'phone' => '021-22222222',
                'email' => 'jkt.south@cctv.com',
                'latitude' => -6.261493,
                'longitude' => 106.810600,
                'status' => 'active',
            ],
            [
                'group_id' => $jakartaGroup->id,
                'branch_code' => 'JKT003',
                'branch_name' => 'Jakarta West Branch',
                'city' => 'Jakarta Barat',
                'address' => 'Jl. Gajah Mada No.50, Jakarta Barat',
                'phone' => '021-33333333',
                'email' => 'jkt.west@cctv.com',
                'latitude' => -6.168056,
                'longitude' => 106.813889,
                'status' => 'active',
            ],

            // Bandung branches
            [
                'group_id' => $bandungGroup->id,
                'branch_code' => 'BDG001',
                'branch_name' => 'Bandung City Branch',
                'city' => 'Bandung',
                'address' => 'Jl. Asia Afrika No.50, Bandung',
                'phone' => '022-33333333',
                'email' => 'bdg.city@cctv.com',
                'latitude' => -6.917464,
                'longitude' => 107.619125,
                'status' => 'active',
            ],
            [
                'group_id' => $bandungGroup->id,
                'branch_code' => 'BDG002',
                'branch_name' => 'Bandung North Branch',
                'city' => 'Bandung Utara',
                'address' => 'Jl. Setiabudhi No.123, Bandung',
                'phone' => '022-44444444',
                'email' => 'bdg.north@cctv.com',
                'latitude' => -6.867778,
                'longitude' => 107.589111,
                'status' => 'active',
            ],

            // Surabaya branches
            [
                'group_id' => $surabayaGroup->id,
                'branch_code' => 'SBY001',
                'branch_name' => 'Surabaya Central Branch',
                'city' => 'Surabaya',
                'address' => 'Jl. Tunjungan No.25, Surabaya',
                'phone' => '031-44444444',
                'email' => 'sby.central@cctv.com',
                'latitude' => -7.250445,
                'longitude' => 112.768845,
                'status' => 'active',
            ],
            [
                'group_id' => $surabayaGroup->id,
                'branch_code' => 'SBY002',
                'branch_name' => 'Surabaya East Branch',
                'city' => 'Surabaya Timur',
                'address' => 'Jl. Ahmad Yani No.100, Surabaya',
                'phone' => '031-55555555',
                'email' => 'sby.east@cctv.com',
                'latitude' => -7.308333,
                'longitude' => 112.734444,
                'status' => 'active',
            ],
        ];

        foreach ($branches as $branch) {
            CompanyBranch::create($branch);
        }
    }
}
