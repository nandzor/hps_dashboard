<?php

namespace App\Services;

use App\Models\EventLog;

class EventLogService extends BaseService
{
    /**
     * EventLogService constructor.
     */
    public function __construct()
    {
        $this->model = new EventLog();
        $this->searchableFields = ['re_id', 'event_type'];
        $this->orderByColumn = 'created_at';
        $this->orderByDirection = 'desc';
    }

    /**
     * Get event logs with relationships
     */
    public function getEventLogsWithRelationships()
    {
        return EventLog::with(['branch', 'device', 'reIdMaster'])
            ->orderBy($this->orderByColumn, $this->orderByDirection);
    }

    /**
     * Get paginated event logs with relationships
     */
    public function getPaginatedEventLogs(?string $search = null, int $perPage = 10, array $filters = [])
    {
        $perPage = $this->validatePerPage($perPage);

        $query = $this->getEventLogsWithRelationships();

        // Apply filters
        if (!empty($filters)) {
            $query = $this->applyFilters($query, $filters);
        }

        // Apply search if provided
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('re_id', 'like', "%{$search}%")
                  ->orWhere('event_type', 'like', "%{$search}%")
                  ->orWhereHas('branch', function($branchQuery) use ($search) {
                      $branchQuery->where('branch_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('device', function($deviceQuery) use ($search) {
                      $deviceQuery->where('device_name', 'like', "%{$search}%");
                  });
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Get event log statistics
     */
    public function getStatistics()
    {
        return [
            'total_events' => EventLog::count(),
            'today_events' => EventLog::whereDate('created_at', today())->count(),
            'this_week_events' => EventLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month_events' => EventLog::whereMonth('created_at', now()->month)->count(),
        ];
    }
}
