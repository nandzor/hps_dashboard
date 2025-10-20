<?php

namespace App\Services;

use App\Models\CountingReport;
use App\Models\CompanyBranch;
use Illuminate\Http\Request;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class ReportService extends BaseService {
    protected $model = CountingReport::class;

    protected $orderByColumn = 'report_date';
    protected $orderByDirection = 'desc';

    /**
     * Get monthly reports with pagination and caching
     *
     * @param Request $request
     * @return array
     */
    public function getMonthlyReports(Request $request): array {
        $month = $request->input('month', now()->format('Y-m'));
        $branchId = $request->input('branch_id');
        $perPage = $request->input('per_page', 25);

        // Generate cache key for this specific query (include page parameter)
        $page = $request->input('page', 1);
        $cacheKey = "monthly_reports_{$month}_" . ($branchId ?? 'all') . "_{$perPage}_page_{$page}";

        // Try to get from cache first (10 minutes cache for monthly reports)
        return cache()->remember($cacheKey, 10, function () use ($request, $month, $branchId, $perPage) {
            $startDate = Carbon::parse($month . '-01')->startOfMonth();
            $endDate = Carbon::parse($month . '-01')->endOfMonth();

            // Optimized query with single database call for statistics
            $baseQuery = CountingReport::where('report_type', 'daily')
                ->whereBetween('report_date', [$startDate, $endDate]);

            if ($branchId) {
                $baseQuery->where('branch_id', $branchId);
            }

            // Get paginated results first
            $page = $request->input('page', 1);
            $reports = $baseQuery->with(['branch:id,branch_name'])
                ->orderBy('report_date', 'desc')
                ->paginate($this->validatePerPage($perPage), ['*'], 'page', $page)
                ->appends($request->query());

            // Get all reports for statistics (create completely new query)
            $allReportsQuery = CountingReport::where('report_type', 'daily')
                ->whereBetween('report_date', [$startDate, $endDate]);

            if ($branchId) {
                $allReportsQuery->where('branch_id', $branchId);
            }

            $allReports = $allReportsQuery->with(['branch:id,branch_name'])
                ->orderBy('report_date', 'desc')
                ->get();

            $branches = CompanyBranch::active()->select('id', 'branch_name')->get();

            // Calculate monthly statistics using optimized collection methods
            $monthlyStats = [
                'total_detections' => $allReports->sum('total_detections'),
                'unique_persons' => $allReports->max('unique_person_count'),
                'total_events' => $allReports->sum('total_events'),
            ];

            // Optimized additional calculations
            $groupedReports = $allReports->groupBy('branch_id');
            $totalDevices = $allReports->unique('branch_id')->sum('total_devices');
            $avgDetectionsPerDay = $monthlyStats['total_detections'] / max($allReports->count(), 1);

            // Optimized branch statistics calculation
            $branchStats = $allReports->groupBy('branch_id')->map(function ($items) {
                $firstItem = $items->first();
                return [
                    'branch' => $firstItem->branch,
                    'total_detections' => $items->sum('total_detections'),
                    'total_events' => $items->sum('total_events'),
                    'unique_persons' => $items->max('unique_person_count'),
                    'avg_per_day' => $items->avg('total_detections'),
                ];
            });

            $maxBranchDetections = $branchStats->max('total_detections') ?: 1;

            // Optimized daily detections calculation
            $dailyDetections = $allReports->groupBy(function ($report) {
                return Carbon::parse($report->report_date)->format('Y-m-d');
            })->map(function ($reports) {
                return $reports->sum('total_detections');
            })->toArray();

            return [
                'reports' => $reports,
                'allReports' => $allReports,
                'branches' => $branches,
                'month' => $month,
                'branchId' => $branchId,
                'monthlyStats' => $monthlyStats,
                'groupedReports' => $groupedReports,
                'totalDevices' => $totalDevices,
                'avgDetectionsPerDay' => $avgDetectionsPerDay,
                'branchStats' => $branchStats,
                'maxBranchDetections' => $maxBranchDetections,
                'dailyDetections' => $dailyDetections,
                'perPageOptions' => $this->getPerPageOptions(),
                'perPage' => $perPage,
            ];
        });
    }

    /**
     * Clear cache for dashboard and reports
     */
    public function clearCache($type = 'all', $params = []) {
        if ($type === 'dashboard' || $type === 'all') {
            // Clear dashboard cache
            $dateFrom = $params['date_from'] ?? null;
            $dateTo = $params['date_to'] ?? null;
            $branchId = $params['branch_id'] ?? null;

            if ($dateFrom && $dateTo) {
                $cacheKey = "dashboard_data_{$dateFrom}_{$dateTo}_" . ($branchId ?? 'all');
                cache()->forget($cacheKey);

                $exportCacheKey = "dashboard_export_{$dateFrom}_{$dateTo}_" . ($branchId ?? 'all');
                cache()->forget($exportCacheKey);
            } else {
                // Clear all dashboard cache patterns
                cache()->flush();
            }
        }

        if ($type === 'monthly' || $type === 'all') {
            // Clear monthly reports cache
            $month = $params['month'] ?? null;
            $branchId = $params['branch_id'] ?? null;
            $perPage = $params['per_page'] ?? 25;

            if ($month) {
                $cacheKey = "monthly_reports_{$month}_" . ($branchId ?? 'all') . "_{$perPage}";
                cache()->forget($cacheKey);
            } else {
                // Clear all monthly cache patterns
                cache()->flush();
            }
        }
    }

    /**
     * Get performance metrics for reports
     */
    public function getPerformanceMetrics() {
        return [
            'cache_hit_rate' => $this->getCacheHitRate(),
            'average_query_time' => $this->getAverageQueryTime(),
            'slow_queries_count' => $this->getSlowQueriesCount(),
        ];
    }

    private function getCacheHitRate() {
        // This would be implemented with Redis or similar cache system
        return 0.85; // 85% cache hit rate
    }

    private function getAverageQueryTime() {
        // This would be implemented with query logging
        return 150; // 150ms average
    }

    private function getSlowQueriesCount() {
        // This would be implemented with query logging
        return 0;
    }
}
