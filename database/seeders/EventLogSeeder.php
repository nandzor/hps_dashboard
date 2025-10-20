<?php

namespace Database\Seeders;

use App\Models\EventLog;
use App\Models\ReIdMaster;
use App\Models\CompanyBranch;
use App\Models\DeviceMaster;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class EventLogSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $this->command->info('Seeding Event Logs...');

        $branches = CompanyBranch::with('devices')->get();

        if ($branches->isEmpty()) {
            $this->command->warn('No branches found. Please run CompanyBranchSeeder first.');
            return;
        }

        // Get Re-IDs from last 7 days
        $reIds = ReIdMaster::where('detection_date', '>=', now()->subDays(7)->startOfDay())
            ->pluck('re_id')
            ->toArray();

        if (empty($reIds)) {
            $this->command->warn('No Re-ID masters found. Running without Re-ID references...');
        }

        $eventTypes = ['detection', 'alert', 'motion', 'manual'];
        $eventTypeWeights = [
            'detection' => 60,  // 60% detection events
            'alert' => 25,      // 25% alert events
            'motion' => 10,     // 10% motion events
            'manual' => 5,      // 5% manual events
        ];

        $totalEvents = 0;

        // Generate events for the last 7 days
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();

            // More events during business hours
            $eventsPerDay = $this->getEventsPerDay($date);

            for ($j = 0; $j < $eventsPerDay; $j++) {
                // Select random branch with devices
                $branch = $branches->random();

                if ($branch->devices->isEmpty()) {
                    continue;
                }

                $device = $branch->devices->random();

                // Weighted random event type
                $eventType = $this->getWeightedEventType($eventTypeWeights);

                // Random time during the day (weighted towards business hours)
                $hour = $this->getWeightedHour();
                $eventTimestamp = $date->copy()
                    ->addHours($hour)
                    ->addMinutes(rand(0, 59))
                    ->addSeconds(rand(0, 59));

                // Re-ID only for detection and alert events
                $reId = null;
                if (in_array($eventType, ['detection', 'alert']) && !empty($reIds)) {
                    $reId = $reIds[array_rand($reIds)];
                }

                // Generate event data based on type
                $eventData = $this->generateEventData($eventType);

                // Notification flags - Updated logic for better realism
                $notificationSent = $eventType === 'alert' ? (rand(1, 100) > 20) : false; // 80% alerts sent
                $messageSent = $notificationSent && (rand(1, 100) > 30); // 70% of notifications include message
                $imageSent = $notificationSent && (rand(1, 100) > 40); // 60% of notifications include image

                // Generate image path with new folder structure
                $imagePath = null;
                if ($imageSent) {
                    $imagePath = $this->generateImagePath($eventTimestamp, $device->device_id);
                }

                EventLog::create([
                    'branch_id' => $branch->id,
                    'device_id' => $device->device_id,
                    're_id' => $reId,
                    'event_type' => $eventType,
                    'detected_count' => $this->getDetectedCount($eventType),
                    'image_path' => $imagePath,
                    'image_sent' => $imageSent,
                    'message_sent' => $messageSent,
                    'notification_sent' => $notificationSent,
                    'event_data' => $eventData,
                    'event_timestamp' => $eventTimestamp,
                ]);

                $totalEvents++;
            }
        }

        $this->command->info("Created {$totalEvents} event logs");

        // Show event summary
        $this->showEventSummary();
    }

    /**
     * Get number of events per day (more on weekdays, less on weekends)
     */
    private function getEventsPerDay(Carbon $date): int {
        $isWeekend = $date->isWeekend();

        if ($isWeekend) {
            return rand(15, 25); // Fewer events on weekends
        }

        return rand(35, 55); // More events on weekdays
    }

    /**
     * Get weighted random event type
     */
    private function getWeightedEventType(array $weights): string {
        $rand = rand(1, 100);
        $cumulative = 0;

        foreach ($weights as $type => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $type;
            }
        }

        return 'detection'; // fallback
    }

    /**
     * Get weighted hour (more events during business hours)
     */
    private function getWeightedHour(): int {
        $rand = rand(1, 100);

        if ($rand <= 60) {
            // 60% during peak hours (8-17)
            return rand(8, 17);
        } elseif ($rand <= 85) {
            // 25% during extended hours (6-8, 17-20)
            return rand(1, 10) > 5 ? rand(6, 7) : rand(17, 20);
        } else {
            // 15% during off hours (20-6)
            return rand(20, 23);
        }
    }

    /**
     * Get detected count based on event type
     */
    private function getDetectedCount(string $eventType): int {
        return match ($eventType) {
            'detection' => rand(1, 3),
            'alert' => rand(1, 5),
            'motion' => rand(1, 2),
            'manual' => 0,
            default => 1,
        };
    }

    /**
     * Generate event data based on event type
     */
    private function generateEventData(string $eventType): array {
        $baseData = [
            'confidence' => rand(80, 99) / 100,
            'processing_time_ms' => rand(50, 500),
        ];

        return match ($eventType) {
            'detection' => array_merge($baseData, [
                'detection_zone' => ['entrance', 'lobby', 'parking', 'corridor'][rand(0, 3)],
                'movement_direction' => ['entering', 'exiting', 'passing'][rand(0, 2)],
                'dwell_time_seconds' => rand(2, 30),
            ]),
            'alert' => array_merge($baseData, [
                'alert_reason' => ['suspicious_behavior', 'loitering', 'restricted_area', 'after_hours'][rand(0, 3)],
                'severity' => ['low', 'medium', 'high'][rand(0, 2)],
                'auto_triggered' => true,
            ]),
            'motion' => array_merge($baseData, [
                'motion_area' => rand(10, 80), // percentage of frame
                'motion_intensity' => ['low', 'medium', 'high'][rand(0, 2)],
                'duration_seconds' => rand(1, 10),
            ]),
            'manual' => [
                'triggered_by' => 'system_operator',
                'action' => ['snapshot', 'recording_start', 'alarm_test'][rand(0, 2)],
                'notes' => 'Manual system check',
            ],
            default => $baseData,
        };
    }

    /**
     * Generate image path with new folder structure
     */
    private function generateImagePath(Carbon $timestamp, string $deviceId): string {
        $folderName = 'whatsapp_detection_' . $timestamp->format('d-m-Y');
        $filename = $timestamp->format('His') . '_' . $deviceId . '_' . uniqid() . '.jpg';

        return "{$folderName}/{$filename}";
    }

    /**
     * Show event summary
     */
    private function showEventSummary(): void {
        $this->command->info('');
        $this->command->info('=== Event Logs Summary ===');

        // Events by type
        $eventsByType = EventLog::selectRaw('event_type, COUNT(*) as count')
            ->where('event_timestamp', '>=', now()->subDays(7))
            ->groupBy('event_type')
            ->orderBy('count', 'desc')
            ->get();

        $this->command->info('');
        $this->command->info('Events by Type:');
        foreach ($eventsByType as $event) {
            $percentage = round(($event->count / $eventsByType->sum('count')) * 100, 1);
            $bars = str_repeat('█', min(30, (int)($event->count / 2)));
            $this->command->info(sprintf(
                '  %-12s: %s (%d events, %s%%)',
                ucfirst($event->event_type),
                $bars,
                $event->count,
                $percentage
            ));
        }

        // Events by day
        $this->command->info('');
        $this->command->info('Events per Day:');
        $eventsByDay = EventLog::selectRaw('DATE(event_timestamp) as date, COUNT(*) as count')
            ->where('event_timestamp', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        foreach ($eventsByDay as $day) {
            $bars = str_repeat('█', min(40, (int)($day->count / 2)));
            $this->command->info(sprintf(
                '  %s: %s (%d events)',
                Carbon::parse($day->date)->format('D, M d'),
                $bars,
                $day->count
            ));
        }

        // Notification stats
        $notificationStats = EventLog::where('event_timestamp', '>=', now()->subDays(7))
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN notification_sent THEN 1 ELSE 0 END) as notifications_sent,
                SUM(CASE WHEN image_sent THEN 1 ELSE 0 END) as images_sent,
                SUM(CASE WHEN message_sent THEN 1 ELSE 0 END) as messages_sent
            ')
            ->first();

        $this->command->info('');
        $this->command->info('Notification Stats:');
        $this->command->info("  Total events: {$notificationStats->total}");
        $this->command->info("  Notifications sent: {$notificationStats->notifications_sent}");
        $this->command->info("  Images sent: {$notificationStats->images_sent}");
        $this->command->info("  Messages sent: {$notificationStats->messages_sent}");
    }
}
