<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReIdBranchDetection;
use App\Models\CountingReport;
use App\Models\CompanyBranch;
use Illuminate\Support\Facades\DB;

class CountingReportSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $this->command->info('Starting Counting Report generation...');

        // Clear existing reports (use delete to avoid foreign key issues)
        CountingReport::query()->delete();

        // Get all detections
        $detections = ReIdBranchDetection::all();

        if ($detections->isEmpty()) {
            $this->command->warn('No detections found. Please run ReIdBranchDetectionSeeder first.');
            return;
        }

        // Group detections by date and branch
        $groupedDetections = $detections->groupBy(function ($detection) {
            return [
                'date' => \Carbon\Carbon::parse($detection->detection_timestamp)->format('Y-m-d'),
                'branch_id' => $detection->branch_id,
            ];
        });

        $reportCount = 0;

        foreach ($groupedDetections as $key => $items) {
            $keyParts = json_decode(json_encode($key), true);
            $date = $items->first()->detection_timestamp->format('Y-m-d');
            $branchId = $items->first()->branch_id;

            // Get unique devices for this date and branch
            $uniqueDevices = $items->pluck('device_id')->unique()->count();

            // Count total detections
            $totalDetections = $items->count();

            // Count unique persons
            $uniquePersons = $items->pluck('re_id')->unique()->count();

            // For events, we'll use detection count as proxy (or you can query event_logs)
            $totalEvents = $items->count(); // Simplified: same as detections

            // Create or update daily report
            CountingReport::updateOrCreate(
                [
                    'branch_id' => $branchId,
                    'report_type' => 'daily',
                    'report_date' => $date,
                ],
                [
                    'total_devices' => $uniqueDevices,
                    'total_detections' => $totalDetections,
                    'total_events' => $totalEvents,
                    'unique_person_count' => $uniquePersons,
                    'report_data' => [
                        'detection_breakdown' => [
                            'total' => $totalDetections,
                            'unique_persons' => $uniquePersons,
                            'devices_active' => $uniqueDevices,
                        ],
                        'generated_at' => now()->toISOString(),
                    ],
                ]
            );

            $reportCount++;
        }

        $this->command->info("✓ Generated {$reportCount} daily counting reports");

        // Generate overall reports (without branch_id)
        $this->generateOverallReports($detections);

        $this->command->info('✓ Counting Report generation completed!');
    }

    /**
     * Generate overall reports (all branches combined)
     */
    private function generateOverallReports($detections) {
        $groupedByDate = $detections->groupBy(function ($detection) {
            return \Carbon\Carbon::parse($detection->detection_timestamp)->format('Y-m-d');
        });

        $overallCount = 0;

        foreach ($groupedByDate as $date => $items) {
            $uniqueDevices = $items->pluck('device_id')->unique()->count();
            $totalDetections = $items->count();
            $uniquePersons = $items->pluck('re_id')->unique()->count();
            $uniqueBranches = $items->pluck('branch_id')->unique()->count();

            CountingReport::updateOrCreate(
                [
                    'branch_id' => null, // Overall report
                    'report_type' => 'daily',
                    'report_date' => $date,
                ],
                [
                    'total_devices' => $uniqueDevices,
                    'total_detections' => $totalDetections,
                    'total_events' => $totalDetections,
                    'unique_person_count' => $uniquePersons,
                    'report_data' => [
                        'overall' => true,
                        'total_branches' => $uniqueBranches,
                        'detection_breakdown' => [
                            'total' => $totalDetections,
                            'unique_persons' => $uniquePersons,
                            'devices_active' => $uniqueDevices,
                            'branches_active' => $uniqueBranches,
                        ],
                        'generated_at' => now()->toISOString(),
                    ],
                ]
            );

            $overallCount++;
        }

        $this->command->info("✓ Generated {$overallCount} overall daily counting reports");
    }
}
