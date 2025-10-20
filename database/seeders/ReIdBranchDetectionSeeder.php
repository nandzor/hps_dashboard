<?php

namespace Database\Seeders;

use App\Models\ReIdBranchDetection;
use App\Models\ReIdMaster;
use App\Models\CompanyBranch;
use App\Models\DeviceMaster;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ReIdBranchDetectionSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $this->command->info('Seeding Re-ID Branch Detections...');

        $branches = CompanyBranch::with('devices')->get();

        if ($branches->isEmpty()) {
            $this->command->warn('No branches found. Please run CompanyBranchSeeder first.');
            return;
        }

        // Get Re-ID data from cache (seeded by ReIdMasterSeeder)
        $reIdData = cache()->get('reid_seeder_data', []);

        if (empty($reIdData)) {
            // If no cache, get from database
            $reIdMasters = ReIdMaster::where('detection_date', '>=', now()->subDays(7)->startOfDay())
                ->get();

            if ($reIdMasters->isEmpty()) {
                $this->command->warn('No Re-ID masters found. Please run ReIdMasterSeeder first.');
                return;
            }

            foreach ($reIdMasters as $master) {
                $reIdData[] = [
                    're_id' => $master->re_id,
                    'detection_time' => $master->detection_time,
                    'branches_count' => $master->total_detection_branch_count,
                    'detections_count' => $master->total_actual_count,
                ];
            }
        }

        $totalDetections = 0;

        foreach ($reIdData as $data) {
            $reId = $data['re_id'];
            $baseTime = Carbon::parse($data['detection_time']);
            $branchesCount = $data['branches_count'];
            $detectionsCount = $data['detections_count'];
            $detectionDate = $data['detection_date'] ?? $baseTime->toDateString();

            // Select random branches for this person (ensure we don't exceed available branches)
            $selectedBranches = $branches->random(min($branchesCount, $branches->count()));

            foreach ($selectedBranches as $branch) {
                // Get random device from this branch
                if ($branch->devices->isEmpty()) {
                    continue;
                }

                $device = $branch->devices->random();

                // Create multiple detections at this branch
                $detectionsAtBranch = rand(1, ceil($detectionsCount / $branchesCount));

                for ($i = 0; $i < $detectionsAtBranch; $i++) {
                    // Spread detections over time (within the same day)
                    $detectionTimestamp = $baseTime->copy()->addMinutes($i * rand(5, 30));

                    // Ensure detection is within the same day
                    if ($detectionTimestamp->toDateString() !== $detectionDate) {
                        $detectionTimestamp = Carbon::parse($detectionDate)->addHours(rand(6, 20))->addMinutes(rand(0, 59));
                    }

                    ReIdBranchDetection::create([
                        're_id' => $reId,
                        'branch_id' => $branch->id,
                        'device_id' => $device->device_id,
                        'detection_timestamp' => $detectionTimestamp,
                        'detected_count' => rand(1, 3), // More realistic detected count
                        'detection_data' => [
                            'confidence' => rand(85, 99) / 100,
                            'frame_number' => rand(1000, 9999),
                            'camera_angle' => rand(0, 360),
                            'lighting_condition' => ['good', 'moderate', 'low'][rand(0, 2)],
                            'distance_meters' => rand(2, 15),
                            'appearance_features' => [
                                'gender' => ['male', 'female'][rand(0, 1)],
                                'age_group' => ['young', 'adult', 'senior'][rand(0, 2)],
                                'clothing' => ['formal', 'casual', 'sport'][rand(0, 2)],
                            ],
                        ],
                    ]);

                    $totalDetections++;
                }
            }
        }

        $this->command->info("Created {$totalDetections} Re-ID branch detections");

        // Clear cache
        cache()->forget('reid_seeder_data');

        // Show detection trend summary
        $this->showDetectionTrendSummary();
    }

    /**
     * Show detection trend summary for the last 7 days
     */
    private function showDetectionTrendSummary(): void {
        $this->command->info('');
        $this->command->info('=== Detection Trend (Last 7 Days) ===');

        $trend = ReIdBranchDetection::selectRaw('DATE(detection_timestamp) as date, COUNT(*) as count')
            ->where('detection_timestamp', '>=', now()->subDays(7)->startOfDay())
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        foreach ($trend as $day) {
            $bars = str_repeat('█', min(50, (int)($day->count / 2)));
            $this->command->info(sprintf(
                '%s: %s (%d detections)',
                Carbon::parse($day->date)->format('D, M d'),
                $bars,
                $day->count
            ));
        }

        $this->command->info('');
        $this->command->info('Total detections: ' . $trend->sum('count'));
        $this->command->info('Average per day: ' . round($trend->avg('count'), 1));
        $this->command->info('Peak day: ' . $trend->max('count') . ' detections');

        // Show branch detection summary
        $this->command->info('');
        $this->command->info('=== Branch Detection Summary ===');

        $branchStats = ReIdBranchDetection::join('company_branches', 're_id_branch_detections.branch_id', '=', 'company_branches.id')
            ->selectRaw('company_branches.branch_name, COUNT(*) as detection_count, SUM(detected_count) as total_detected_count')
            ->where('re_id_branch_detections.detection_timestamp', '>=', now()->subDays(7)->startOfDay())
            ->groupBy('company_branches.id', 'company_branches.branch_name')
            ->orderBy('detection_count', 'desc')
            ->get();

        foreach ($branchStats as $branch) {
            $this->command->info(sprintf(
                '  %-20s: %d detections, %d total count',
                $branch->branch_name,
                $branch->detection_count,
                $branch->total_detected_count
            ));
        }
    }
}
