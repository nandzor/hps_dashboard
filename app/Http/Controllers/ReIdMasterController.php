<?php

namespace App\Http\Controllers;

use App\Models\ReIdMaster;
use App\Services\ReIdMasterService;
use App\Services\BaseExportService;
use App\Exports\ReIdMastersExport;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class ReIdMasterController extends Controller {
    protected $reIdMasterService;
    protected $exportService;

    public function __construct(ReIdMasterService $reIdMasterService, BaseExportService $exportService) {
        $this->reIdMasterService = $reIdMasterService;
        $this->exportService = $exportService;
    }

    /**
     * Display a listing of persons (Re-ID)
     */
    public function index(Request $request) {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);
        $branchId = $request->input('branch_id');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $filters = [];
        if ($branchId) $filters['branch_id'] = $branchId;

        // If date range provided, use specialized method
        if ($dateFrom && $dateTo) {
            $persons = $this->reIdMasterService->getByDateRange($dateFrom, $dateTo, $filters);
            $statistics = $this->reIdMasterService->getStatistics(['date' => $dateFrom]);

            // Convert Collection to Paginator for consistent view handling
            $persons = $this->paginateCollection($persons, $perPage, $request);
        } else {
            $persons = $this->reIdMasterService->getPaginate($search, $perPage, $filters);
            $statistics = $this->reIdMasterService->getStatistics();
        }

        return view('re-id-masters.index', compact('persons', 'statistics', 'search', 'perPage', 'branchId'));
    }

    /**
     * Convert Collection to Paginator for consistent view handling
     */
    private function paginateCollection($collection, $perPage, Request $request): LengthAwarePaginator {
        $currentPage = $request->get('page', 1);
        $currentPage = max(1, (int) $currentPage);

        $total = $collection->count();
        $items = $collection->forPage($currentPage, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'pageName' => 'page',
            ]
        );
    }

    /**
     * Display the specified person with detection history
     */
    public function show(string $reId, Request $request) {
        // Get all dates this person was detected first
        $allDetections = $this->reIdMasterService->getAllDetectionsForPerson($reId);

        if ($allDetections->isEmpty()) {
            abort(404, 'Person not found');
        }

        // If date not specified, use the latest detection date
        $date = $request->input('date');

        if (!$date) {
            $latestDetection = $allDetections->first();
            $date = \Carbon\Carbon::parse($latestDetection->detection_date)->format('Y-m-d');
        }

        $person = $this->reIdMasterService->getPersonWithDetections($reId, $date);

        if (!$person) {
            // If not found for specific date, redirect to latest date
            $latestDetection = $allDetections->first();
            $latestDate = \Carbon\Carbon::parse($latestDetection->detection_date)->format('Y-m-d');
            return redirect()->route('re-id-masters.show', ['reId' => $reId, 'date' => $latestDate]);
        }

        $hasMultipleDetections = $allDetections->count() > 1;
        $branchDetectionCounts = $this->reIdMasterService->getBranchDetectionCounts($reId, $date);

        return view('re-id-masters.show', compact('person', 'allDetections', 'hasMultipleDetections', 'date', 'branchDetectionCounts'));
    }

    /**
     * Update person status (active/inactive)
     */
    public function update(Request $request, string $reId) {
        $request->validate([
            'date' => ['required', 'date'],
            'status' => ['required', 'in:active,inactive'],
            'person_name' => ['nullable', 'string', 'max:150'],
        ]);

        try {
            $data = ['status' => $request->status];
            if ($request->filled('person_name')) {
                $data['person_name'] = $request->person_name;
            }

            ReIdMaster::where('re_id', $reId)
                ->where('detection_date', $request->date)
                ->update($data);

            return redirect()->back()->with('success', 'Person status updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update: ' . $e->getMessage());
        }
    }

    /**
     * Export persons data
     */
    public function export(Request $request) {
        // Build filters using service
        $filterKeys = ['branch_id', 'date_from', 'date_to'];
        $filters = $this->exportService->buildFilters($request, $filterKeys);

        // Build query with branch detection relationship
        $query = ReIdMaster::query();

        if (isset($filters['branch_id'])) {
            // Filter by branch through re_id_branch_detections relationship
            $query->whereHas('branchDetections', function ($q) use ($filters) {
                $q->where('branch_id', $filters['branch_id']);
            });
        }

        if (isset($filters['date_from'])) {
            $query->where('detection_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('detection_date', '<=', $filters['date_to']);
        }

        $persons = $query->orderBy('detection_date', 'desc')->get();
        $format = $request->input('format', 'excel');

        // Generate filename using service
        $fileName = $this->exportService->generateFileName('Person_Tracking');

        // Export using service
        return $this->exportService->export(
            $format,
            new ReIdMastersExport($persons, $filters),
            're-id-masters.export-pdf',
            compact('persons', 'filters'),
            $fileName
        );
    }
}
