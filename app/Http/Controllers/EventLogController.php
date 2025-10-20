<?php

namespace App\Http\Controllers;

use App\Models\EventLog;
use App\Models\CompanyBranch;
use App\Exports\EventLogsExport;
use App\Services\BaseExportService;
use App\Services\EventLogService;
use Illuminate\Http\Request;

class EventLogController extends Controller {
    protected $exportService;
    protected $eventLogService;

    public function __construct(BaseExportService $exportService, EventLogService $eventLogService) {
        $this->exportService = $exportService;
        $this->eventLogService = $eventLogService;
    }

    public function index(Request $request) {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);
        $eventType = $request->input('event_type');
        $branchId = $request->input('branch_id');

        $filters = [];
        if ($eventType) $filters['event_type'] = $eventType;
        if ($branchId) $filters['branch_id'] = $branchId;

        $events = $this->eventLogService->getPaginatedEventLogs($search, $perPage, $filters);
        $statistics = $this->eventLogService->getStatistics();
        $branches = CompanyBranch::active()->orderBy('branch_name')->get();
        $perPageOptions = $this->eventLogService->getPerPageOptions();

        return view('event-logs.index', compact(
            'events',
            'statistics',
            'branches',
            'search',
            'perPage',
            'eventType',
            'branchId',
            'perPageOptions'
        ));
    }

    public function show(EventLog $eventLog) {
        $eventLog->load(['branch', 'device', 'reIdMaster']);
        return view('event-logs.show', compact('eventLog'));
    }

    public function export(Request $request) {
        $query = EventLog::with(['branch', 'device', 'reIdMaster']);

        // Build filters using service (only event_type and branch_id)
        $filterKeys = ['event_type', 'branch_id'];
        $filters = $this->exportService->buildFilters($request, $filterKeys);

        // Apply filters to query
        if (isset($filters['event_type'])) {
            $query->where('event_type', $filters['event_type']);
        }

        if (isset($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        $events = $query->latest('created_at')->get();
        $format = $request->input('format', 'excel');

        // Generate filename using service
        $fileName = $this->exportService->generateFileName('Event_Logs');

        // Export using service
        return $this->exportService->export(
            $format,
            new EventLogsExport($events, $filters),
            'event-logs.export-pdf',
            compact('events', 'filters'),
            $fileName
        );
    }
}
