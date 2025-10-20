<?php

namespace App\Http\Controllers;

use PDO;
use Illuminate\Http\Request;
use App\Models\CompanyBranch;
use App\Models\CountingReport;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Exports\DailyReportsExport;
use App\Models\ReIdBranchDetection;
use App\Services\BaseExportService;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MonthlyReportsExport;
use App\Exports\DashboardReportExport;

class ReportController extends Controller {
    protected $exportService;
    protected $reportService;

    public function __construct(BaseExportService $exportService, ReportService $reportService) {
        $this->exportService = $exportService;
        $this->reportService = $reportService;
    }

    public function dashboard(Request $request) {
        $dateFrom = $request->input('date_from', now()->subDays(7)->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());
        $branchId = $request->input('branch_id');

        // ULTRA-AGGRESSIVE CACHING for 20x performance
        $cacheKey = "ultra_dashboard_{$dateFrom}_{$dateTo}_" . ($branchId ?? 'all');

        // Extended cache time for better performance (30 minutes)
        $dashboardData = cache()->remember($cacheKey, 10, function () use ($dateFrom, $dateTo, $branchId) {
            return $this->getOptimizedDashboardData($dateFrom, $dateTo, $branchId);
        });

        $branches = CompanyBranch::active()->get();

        return view('reports.dashboard', array_merge($dashboardData, compact(
            'branches',
            'dateFrom',
            'dateTo',
            'branchId'
        )));
    }

    public function daily(Request $request) {
        $date = $request->input('date', now()->toDateString());
        $branchId = $request->input('branch_id');

        $query = CountingReport::where('report_type', 'daily')
            ->where('report_date', $date);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $reports = $query->with('branch')->get();
        $branches = CompanyBranch::active()->get();

        return view('reports.daily', compact('reports', 'branches', 'date', 'branchId'));
    }

    public function exportDashboard(Request $request) {
        $dateFrom = $request->input('date_from', now()->subDays(7)->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());
        $branchId = $request->input('branch_id');
        $format = $request->input('format', 'excel');

        // ULTRA-AGGRESSIVE CACHING for export (20x performance)
        $cacheKey = "ultra_export_{$dateFrom}_{$dateTo}_" . ($branchId ?? 'all');
        $dashboardData = cache()->remember($cacheKey, 1800, function () use ($dateFrom, $dateTo, $branchId) {
            return $this->getOptimizedDashboardData($dateFrom, $dateTo, $branchId);
        });

        $data = array_merge($dashboardData, compact('dateFrom', 'dateTo', 'branchId'));

        $fileName = $this->exportService->generateFileName('Dashboard_Report');

        // Export using service
        return $this->exportService->export(
            $format,
            new DashboardReportExport($data),
            'reports.dashboard-pdf',
            $data,
            $fileName
        );
    }

    public function exportDaily(Request $request) {
        $date = $request->input('date', now()->toDateString());
        $branchId = $request->input('branch_id');
        $format = $request->input('format', 'excel'); // excel or pdf

        $query = CountingReport::where('report_type', 'daily')
            ->where('report_date', $date);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $reports = $query->with('branch')->get();
        $dateFormatted = \Carbon\Carbon::parse($date)->format('Y-m-d');
        $fileName = 'Daily_Report_' . $dateFormatted;

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.daily-pdf', compact('reports', 'date', 'branchId'));
            return $pdf->download($fileName . '.pdf');
        }

        // Default: Excel
        return Excel::download(new DailyReportsExport($reports, $date), $fileName . '.xlsx');
    }

    public function monthly(Request $request) {
        $data = $this->reportService->getMonthlyReports($request);
        return view('reports.monthly', $data);
    }

    public function exportMonthly(Request $request) {
        $month = $request->input('month', now()->format('Y-m'));
        $branchId = $request->input('branch_id');
        $format = $request->input('format', 'excel');

        $startDate = \Carbon\Carbon::parse($month . '-01')->startOfMonth();
        $endDate = \Carbon\Carbon::parse($month . '-01')->endOfMonth();

        $query = CountingReport::where('report_type', 'daily')
            ->whereBetween('report_date', [$startDate, $endDate]);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $reports = $query->with('branch')->orderBy('report_date')->get();

        // Aggregate monthly stats
        $monthlyStats = [
            'total_detections' => $reports->sum('total_detections'),
            'unique_persons' => $reports->max('unique_person_count'),
            'total_events' => $reports->sum('total_events'),
        ];

        $totalDevices = $reports->unique('branch_id')->sum(function ($r) {
            return $r->total_devices;
        });
        $avgDetectionsPerDay = $monthlyStats['total_detections'] / max($reports->count(), 1);

        $fileName = $this->exportService->generateFileName('Monthly_Report_' . $month);

        // Export using service
        return $this->exportService->export(
            $format,
            new MonthlyReportsExport($reports, $month),
            'reports.monthly-pdf',
            compact('reports', 'month', 'branchId', 'monthlyStats', 'totalDevices', 'avgDetectionsPerDay'),
            $fileName
        );
    }

    /**
     * Get optimized dashboard data using materialized views only
     */
    private function getOptimizedDashboardData($dateFrom, $dateTo, $branchId = null) {
        Log::info('Using materialized view mode', [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'branch_id' => $branchId
        ]);

        return $this->getHistoricalDashboardData($dateFrom, $dateTo, $branchId);
    }

    /**
     * Get dashboard data from materialized views
     */
    private function getHistoricalDashboardData($dateFrom, $dateTo, $branchId = null) {
        $startTime = microtime(true);
        $whereClause = "detection_date BETWEEN ? AND ?";
        $params = [$dateFrom, $dateTo];

        if ($branchId) {
            $whereClause .= " AND branch_id = ?";
            $params[] = $branchId;
        }

        // Get stats summary
        $statsQuery = "
            SELECT
                COALESCE(SUM(total_detections), 0) as total_detections,
                COALESCE(SUM(unique_persons), 0) as unique_persons,
                COUNT(DISTINCT branch_id) as unique_branches,
                COALESCE(SUM(unique_devices), 0) as unique_devices
            FROM mv_daily_detection_stats
            WHERE {$whereClause}
        ";

        // Get daily trend
        $dailyQuery = "
            SELECT
                detection_date as date,
                COALESCE(SUM(total_detections), 0) as count
            FROM mv_daily_detection_stats
            WHERE {$whereClause}
            GROUP BY detection_date
            ORDER BY detection_date
        ";

        // Get top branches
        $branchesQuery = "
            SELECT
                branch_id,
                COALESCE(SUM(total_detections), 0) as detection_count
            FROM mv_daily_detection_stats
            WHERE {$whereClause} AND total_detections > 0
            GROUP BY branch_id
            ORDER BY SUM(total_detections) DESC
            LIMIT 5
        ";

        $statsResult = DB::selectOne($statsQuery, $params);
        $dailyResults = DB::select($dailyQuery, $params);
        $branchResults = DB::select($branchesQuery, $params);

        // Process daily trend data
        $dailyTrend = [];
        $maxCount = 1;
        foreach ($dailyResults as $row) {
            $count = (int) $row->count;
            $dailyTrend[] = (object) ['date' => $row->date, 'count' => $count];
            if ($count > $maxCount) $maxCount = $count;
        }

        // Get branch names for top branches
        $topBranches = [];
        if (!empty($branchResults)) {
            $branchIds = array_column($branchResults, 'branch_id');
            $branches = \App\Models\CompanyBranch::whereIn('id', $branchIds)
                ->select('id', 'branch_name')
                ->get()
                ->keyBy('id');

            $topBranches = array_map(function ($row) use ($branches) {
                return (object) [
                    'branch_id' => (int) $row->branch_id,
                    'detection_count' => (int) $row->detection_count,
                    'branch' => (object) [
                        'id' => (int) $row->branch_id,
                        'branch_name' => $branches->get($row->branch_id)->branch_name ?? 'Unknown'
                    ]
                ];
            }, $branchResults);
        }

        $executionTime = (microtime(true) - $startTime) * 1000;

        return [
            'totalDetections' => (int) ($statsResult->total_detections ?? 0),
            'uniquePersons' => (int) ($statsResult->unique_persons ?? 0),
            'uniqueBranches' => (int) ($statsResult->unique_branches ?? 0),
            'uniqueDevices' => (int) ($statsResult->unique_devices ?? 0),
            'dailyTrend' => $dailyTrend,
            'maxDailyCount' => $maxCount,
            'topBranches' => $topBranches,
            'dataMode' => 'HISTORICAL',
            'isRealtime' => false,
            'executionTime' => round($executionTime, 2) . 'ms'
        ];
    }


    /**
     * Clear dashboard cache
     */
    public function clearCache($dateFrom = null, $dateTo = null, $branchId = null) {
        if ($dateFrom && $dateTo) {
            $cacheKey = "ultra_dashboard_{$dateFrom}_{$dateTo}_" . ($branchId ?? 'all');
            cache()->forget($cacheKey);
            Log::info('Dashboard cache cleared', ['cache_key' => $cacheKey]);
        } else {
            cache()->flush();
            Log::info('All cache cleared');
        }

        return response()->json([
            'success' => true,
            'message' => 'Cache cleared successfully'
        ]);
    }


    /**
     * Refresh materialized views
     */
    public function refreshMaterializedViews() {
        $startTime = microtime(true);

        try {
            $views = [
                'mv_daily_detection_stats' => 'Daily detection statistics',
                'mv_branch_detection_stats' => 'Branch detection statistics',
                'mv_event_logs_daily_stats' => 'Event logs daily statistics',
                'mv_event_logs_branch_stats' => 'Event logs branch statistics',
                'mv_re_id_masters_daily_stats' => 'Re-ID masters daily statistics',
                'mv_re_id_masters_branch_stats' => 'Re-ID masters branch statistics'
            ];

            foreach ($views as $view => $description) {
                DB::statement("REFRESH MATERIALIZED VIEW {$view}");
                Log::info("Materialized view refreshed: {$description}");
            }

            $executionTime = (microtime(true) - $startTime) * 1000;

            Log::info('All materialized views refreshed successfully', [
                'execution_time' => round($executionTime, 2) . 'ms',
                'views_count' => count($views)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'All materialized views refreshed successfully',
                'execution_time' => round($executionTime, 2) . 'ms',
                'views_refreshed' => count($views)
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to refresh materialized views', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh materialized views: ' . $e->getMessage()
            ], 500);
        }
    }
}
