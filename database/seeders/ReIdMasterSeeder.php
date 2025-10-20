<?php

namespace Database\Seeders;

use App\Models\ReIdMaster;
use App\Models\CompanyBranch;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ReIdMasterSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $this->command->info('Seeding Re-ID Masters...');

        $branches = CompanyBranch::all();

        if ($branches->isEmpty()) {
            $this->command->warn('No branches found. Please run CompanyBranchSeeder first.');
            return;
        }

        // Create Re-ID masters for the last 7 days
        $reIds = [];
        $personNames = [
            'Ahmad Santoso',
            'Budi Wijaya',
            'Citra Dewi',
            'Dian Pratama',
            'Eko Saputra',
            'Fitri Handayani',
            'Gunawan Lim',
            'Hendra Kusuma',
            'Indah Permata',
            'Joko Widodo',
            'Kartika Sari',
            'Linda Wati',
            'Muhammad Rizki',
            'Nurul Aini',
            'Oscar Tanuwijaya',
        ];

        $features = [
            ['gender' => 'male', 'age_group' => 'adult', 'clothing' => 'formal'],
            ['gender' => 'female', 'age_group' => 'adult', 'clothing' => 'casual'],
            ['gender' => 'male', 'age_group' => 'young', 'clothing' => 'casual'],
            ['gender' => 'female', 'age_group' => 'senior', 'clothing' => 'formal'],
            ['gender' => 'male', 'age_group' => 'senior', 'clothing' => 'casual'],
        ];

        // Generate Re-IDs for the past 7 days
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();

            // Create 10-15 unique persons per day
            $personsPerDay = rand(10, 15);

            for ($j = 0; $j < $personsPerDay; $j++) {
                $reId = 'REID_' . $date->format('Ymd') . '_' . str_pad($j + 1, 4, '0', STR_PAD_LEFT);
                $detectionTime = $date->copy()->addHours(rand(6, 20))->addMinutes(rand(0, 59));

                // Generate realistic detection counts
                $totalActualCount = rand(1, 10);
                $totalDetectionBranchCount = rand(1, min(3, $branches->count()));

                $person = ReIdMaster::create([
                    're_id' => $reId,
                    'detection_date' => $date->toDateString(),
                    'detection_time' => $detectionTime,
                    'person_name' => $personNames[array_rand($personNames)],
                    'appearance_features' => $features[array_rand($features)],
                    'first_detected_at' => $detectionTime,
                    'last_detected_at' => $detectionTime->copy()->addHours(rand(0, 3)),
                    'total_detection_branch_count' => $totalDetectionBranchCount,
                    'total_actual_count' => $totalActualCount,
                    'status' => 'active',
                ]);

                $reIds[] = [
                    're_id' => $reId,
                    'detection_time' => $detectionTime,
                    'branches_count' => $totalDetectionBranchCount,
                    'detections_count' => $totalActualCount,
                    'detection_date' => $date->toDateString(),
                ];
            }
        }

        $this->command->info('Created ' . count($reIds) . ' Re-ID masters');

        // Store for use in ReIdBranchDetectionSeeder
        cache()->put('reid_seeder_data', $reIds, now()->addMinutes(10));
    }
}
