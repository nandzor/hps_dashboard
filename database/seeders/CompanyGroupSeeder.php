<?php

namespace Database\Seeders;

use App\Models\CompanyGroup;
use Illuminate\Database\Seeder;

class CompanyGroupSeeder extends Seeder {
    public function run(): void {
        $groups = [
            [
                'province_code' => 'JKT',
                'province_name' => 'DKI Jakarta',
                'group_name' => 'Jakarta Group',
                'address' => 'Jl. Sudirman No.1, Jakarta Pusat',
                'phone' => '021-12345678',
                'email' => 'jakarta@cctv.com',
                'status' => 'active',
            ],
            [
                'province_code' => 'JABAR',
                'province_name' => 'Jawa Barat',
                'group_name' => 'Bandung Group',
                'address' => 'Jl. Asia Afrika No.1, Bandung',
                'phone' => '022-87654321',
                'email' => 'bandung@cctv.com',
                'status' => 'active',
            ],
            [
                'province_code' => 'JATIM',
                'province_name' => 'Jawa Timur',
                'group_name' => 'Surabaya Group',
                'address' => 'Jl. Tunjungan No.1, Surabaya',
                'phone' => '031-11223344',
                'email' => 'surabaya@cctv.com',
                'status' => 'active',
            ],
            [
                'province_code' => 'JATENG',
                'province_name' => 'Jawa Tengah',
                'group_name' => 'Semarang Group',
                'address' => 'Jl. Pandanaran No.100, Semarang',
                'phone' => '024-55667788',
                'email' => 'semarang@cctv.com',
                'status' => 'active',
            ],
            [
                'province_code' => 'BALI',
                'province_name' => 'Bali',
                'group_name' => 'Bali Group',
                'address' => 'Jl. Sunset Road No.50, Denpasar',
                'phone' => '0361-998877',
                'email' => 'bali@cctv.com',
                'status' => 'active',
            ],
        ];

        foreach ($groups as $group) {
            CompanyGroup::create($group);
        }
    }
}
