<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use App\Services\CompanyGroupService;
use App\Services\CompanyBranchService;
use App\Services\DeviceMasterService;
use App\Services\ReIdMasterService;
use App\Models\EventLog;
use App\Models\ReIdBranchDetection;

class DashboardController extends Controller {
    protected $userService;
    protected $companyGroupService;
    protected $companyBranchService;
    protected $deviceMasterService;
    protected $reIdMasterService;

    public function __construct(
        UserService $userService,
        CompanyGroupService $companyGroupService,
        CompanyBranchService $companyBranchService,
        DeviceMasterService $deviceMasterService,
        ReIdMasterService $reIdMasterService
    ) {
        $this->userService = $userService;
        $this->companyGroupService = $companyGroupService;
        $this->companyBranchService = $companyBranchService;
        $this->deviceMasterService = $deviceMasterService;
        $this->reIdMasterService = $reIdMasterService;
    }

    /**
     * Display dashboard with comprehensive statistics
     */
    public function index() {
        // User statistics - use direct model query instead of service getAll
        $totalUsers = \App\Models\User::count();

        // Company statistics - use direct model queries
        $totalGroups = \App\Models\CompanyGroup::count();
        $totalBranches = \App\Models\CompanyBranch::count();
        $totalDevices = \App\Models\DeviceMaster::count();

        // Re-ID statistics (today)
        $reIdStats = $this->reIdMasterService->getStatistics(['date' => now()->toDateString()]);

        // Today's detections count
        $todayDetections = ReIdBranchDetection::whereDate('detection_timestamp', now()->toDateString())->count();

        // Yesterday's detections count
        $yesterdayDetections = ReIdBranchDetection::whereDate('detection_timestamp', now()->subDay()->toDateString())->count();

        // Calculate percentage change
        $todayDetectionTrend = null;
        $todayDetectionTrendUp = true;

        if ($yesterdayDetections > 0) {
            $change = (($todayDetections - $yesterdayDetections) / $yesterdayDetections) * 100;
            $todayDetectionTrend = number_format(abs($change), 1) . '%';
            $todayDetectionTrendUp = $change >= 0;
        } elseif ($todayDetections > 0) {
            $todayDetectionTrend = 'New';
            $todayDetectionTrendUp = true;
        }

        // Recent detections (last 10)
        $recentDetections = ReIdBranchDetection::with(['reIdMaster', 'branch', 'device'])
            ->latest('detection_timestamp')
            ->limit(10)
            ->get();

        // Detection trend (last 7 days) - Fill all 7 days even if no data
        $detectionData = ReIdBranchDetection::selectRaw('DATE(detection_timestamp) as date, COUNT(*) as count')
            ->where('detection_timestamp', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Fill all 7 days (including days with 0 detections)
        $detectionTrend = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $detectionTrend->push((object)[
                'date' => $date,
                'count' => $detectionData->get($date)->count ?? 0
            ]);
        }

        $maxDetectionCount = $detectionTrend->max('count') ?: 1;

        // Recent events (last 10)
        $recentEvents = EventLog::with(['branch', 'device'])
            ->latest('event_timestamp')
            ->limit(10)
            ->get();

        $hasRecentDetections = $recentDetections->count() > 0;
        $hasRecentEvents = $recentEvents->count() > 0;

        return view('dashboard.index', compact(
            'totalUsers',
            'totalGroups',
            'totalBranches',
            'totalDevices',
            'reIdStats',
            'todayDetections',
            'todayDetectionTrend',
            'todayDetectionTrendUp',
            'recentDetections',
            'hasRecentDetections',
            'detectionTrend',
            'maxDetectionCount',
            'recentEvents',
            'hasRecentEvents'
        ));
    }
}
