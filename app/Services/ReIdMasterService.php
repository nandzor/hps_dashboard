<?php

namespace App\Services;

use App\Models\ReIdMaster;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ReIdMasterService extends BaseService {
    public function __construct() {
        $this->model = new ReIdMaster();
        $this->searchableFields = ['re_id', 'person_name'];
        $this->orderByColumn = 'last_detected_at';
        $this->orderByDirection = 'desc';
    }

    /**
     * Get person with detection history
     */
    public function getPersonWithDetections(string $reId, string $date) {
        return ReIdMaster::with([
            'branchDetections' => function ($query) use ($date) {
                $query->whereDate('detection_timestamp', $date)
                    ->with(['branch', 'device'])
                    ->orderBy('detection_timestamp', 'desc');
            }
        ])
            ->where('re_id', $reId)
            ->where('detection_date', $date)
            ->first();
    }

    /**
     * Get all detections for a person across all dates
     */
    public function getAllDetectionsForPerson(string $reId) {
        return ReIdMaster::where('re_id', $reId)
            ->with('branchDetections.branch')
            ->orderBy('last_detected_at', 'desc')
            ->get();
    }

    /**
     * Get persons by date range
     */
    public function getByDateRange(string $startDate, string $endDate, array $filters = []) {
        $query = ReIdMaster::whereBetween('detection_date', [$startDate, $endDate]);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['branch_id'])) {
            $query->whereHas('branchDetections', function ($q) use ($filters) {
                $q->where('branch_id', $filters['branch_id']);
            });
        }

        return $query->orderBy('last_detected_at', 'desc')
            ->orderBy('detection_date', 'desc')
            ->get();
    }

    /**
     * Get statistics
     */
    public function getStatistics(array $filters = []): array {
        $query = ReIdMaster::query();

        if (isset($filters['date'])) {
            $query->whereDate('detection_date', $filters['date']);
        }

        $total = (clone $query)->count();
        $active = (clone $query)->where('status', 'active')->count();
        $inactive = (clone $query)->where('status', 'inactive')->count();

        $totalDetections = (clone $query)->sum('total_detection_branch_count');
        $totalActual = (clone $query)->sum('total_actual_count');
        $uniquePersons = (clone $query)->distinct('re_id')->count('re_id');

        return [
            'total_records' => $total,
            'active_records' => $active,
            'inactive_records' => $inactive,
            'total_detections' => $totalDetections,
            'total_actual_count' => $totalActual,
            'unique_persons' => $uniquePersons,
        ];
    }

    /**
     * Update person status
     */
    public function updateStatus(string $reId, string $date, string $status): bool {
        return ReIdMaster::where('re_id', $reId)
            ->where('detection_date', $date)
            ->update(['status' => $status]);
    }

    /**
     * Get top detected persons
     */
    public function getTopDetectedPersons(int $limit = 10, array $filters = []) {
        $query = ReIdMaster::query();

        if (isset($filters['date_from']) && isset($filters['date_to'])) {
            $query->whereBetween('detection_date', [$filters['date_from'], $filters['date_to']]);
        }

        return $query->orderBy('total_actual_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get branch detection counts for a specific person and date
     */
    public function getBranchDetectionCounts(string $reId, string $date) {
        return DB::table('re_id_branch_detections as rbd')
            ->join('company_branches as cb', 'rbd.branch_id', '=', 'cb.id')
            ->where('rbd.re_id', $reId)
            ->whereDate('rbd.detection_timestamp', $date)
            ->select(
                'cb.id as branch_id',
                'cb.branch_name',
                'cb.branch_code',
                DB::raw('COUNT(rbd.id) as detection_count'),
                DB::raw('SUM(rbd.detected_count) as total_detected_count'),
                DB::raw('MIN(rbd.detection_timestamp) as first_detection'),
                DB::raw('MAX(rbd.detection_timestamp) as last_detection')
            )
            ->groupBy('cb.id', 'cb.branch_name', 'cb.branch_code')
            ->orderBy('total_detected_count', 'desc')
            ->get();
    }

    /**
     * Apply filters to query (Override parent to handle branch_id relationship)
     *
     * @param Builder $query
     * @param array $filters
     * @return Builder
     */
    protected function applyFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $field => $value) {
            if (!is_null($value) && $value !== '') {
                // Handle branch_id filter through relationship
                if ($field === 'branch_id') {
                    $query->whereHas('branchDetections', function ($q) use ($value) {
                        $q->where('branch_id', $value);
                    });
                } elseif (is_array($value)) {
                    $query->whereIn($field, $value);
                } else {
                    $query->where($field, $value);
                }
            }
        }

        return $query;
    }
}
