<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDetectionRequest;
use App\Helpers\ApiResponseHelper;
use App\Helpers\StorageHelper;
use App\Jobs\ProcessDetectionJob;
use App\Models\ReIdMaster;
use App\Models\ReIdBranchDetection;
use App\Models\CompanyBranch;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DetectionController extends Controller {
    /**
     * Log a new detection event (Async - 202 Accepted)
     *
     * @param StoreDetectionRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreDetectionRequest $request) {
        try {
            $data = $request->validated();
            $imagePath = null;

            // Upload image if present (sync for immediate availability)
            if ($request->hasFile('image')) {
                $imageResult = StorageHelper::store(
                    $request->file('image'),
                    'local',
                    'whatsapp_detection_' . now()->format('d-m-Y'),
                    [
                        'related_table' => 'event_logs',
                        'uploaded_by' => null, // API upload
                    ]
                );

                if ($imageResult['success']) {
                    $imagePath = $imageResult['file_path'];
                }
            }

            // Generate unique job ID
            $jobId = (string) Str::uuid();

            // Dispatch to queue (non-blocking)
            ProcessDetectionJob::dispatch(
                $data['re_id'],
                $data['branch_id'],
                $data['device_id'],
                $data['detected_count'],
                $data['detection_data'] ?? [],
                $imagePath,
                $jobId
            )->onQueue('detections');

            // Return 202 Accepted (immediate response)
            return ApiResponseHelper::accepted([
                'job_id' => $jobId,
                'status' => 'processing',
                'message' => 'Detection queued for processing',
                're_id' => $data['re_id'],
                'branch_id' => $data['branch_id'],
                'device_id' => $data['device_id'],
            ], 'Detection event received and queued successfully');
        } catch (\Exception $e) {
            return ApiResponseHelper::serverError(
                'Failed to process detection request',
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Get all detections (paginated)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {
        try {
            $query = ReIdBranchDetection::with(['branch', 'device', 'reIdMaster']);

            // Filter by date range
            if ($request->filled('date_from')) {
                $query->whereDate('detection_timestamp', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('detection_timestamp', '<=', $request->date_to);
            }

            // Filter by branch
            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }

            // Filter by device
            if ($request->filled('device_id')) {
                $query->where('device_id', $request->device_id);
            }

            // Filter by re_id
            if ($request->filled('re_id')) {
                $query->where('re_id', $request->re_id);
            }

            // Order by latest
            $query->latest('detection_timestamp');

            $detections = $query->paginate($request->input('per_page', 15));

            return ApiResponseHelper::paginated($detections, 'Detections retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponseHelper::serverError(
                'Failed to retrieve detections',
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Get person info by re_id
     *
     * @param string $reId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function showPerson(string $reId, Request $request) {
        try {
            $date = $request->input('date', now()->toDateString());

            $person = ReIdMaster::where('re_id', $reId)
                ->where('detection_date', $date)
                ->first();

            if (!$person) {
                return ApiResponseHelper::notFound("Person with re_id '{$reId}' not found for date {$date}");
            }

            // Get detected branches for this person today
            $detectedBranches = ReIdBranchDetection::where('re_id', $reId)
                ->whereDate('detection_timestamp', $date)
                ->with('branch')
                ->select('branch_id', DB::raw('COUNT(*) as detection_count'))
                ->groupBy('branch_id')
                ->get()
                ->map(function ($item) {
                    return [
                        'branch_id' => $item->branch_id,
                        'branch_name' => $item->branch->branch_name ?? 'N/A',
                        'city' => $item->branch->city ?? 'N/A',
                        'detection_count' => $item->detection_count,
                    ];
                });

            return ApiResponseHelper::success([
                're_id' => $person->re_id,
                'detection_date' => $person->detection_date,
                'detection_time' => $person->detection_time,
                'person_name' => $person->person_name,
                'appearance_features' => $person->appearance_features,
                'total_detection_branch_count' => $person->total_detection_branch_count,
                'total_actual_count' => $person->total_actual_count,
                'first_detected_at' => $person->first_detected_at,
                'last_detected_at' => $person->last_detected_at,
                'status' => $person->status,
                'detected_branches' => $detectedBranches,
            ], 'Person information retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponseHelper::serverError(
                'Failed to retrieve person info',
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Get detection history for specific person
     *
     * @param string $reId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function personDetections(string $reId, Request $request) {
        try {
            $query = ReIdBranchDetection::where('re_id', $reId)
                ->with(['branch', 'device']);

            // Filter by date range
            if ($request->filled('date_from')) {
                $query->whereDate('detection_timestamp', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('detection_timestamp', '<=', $request->date_to);
            }

            // Filter by branch
            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }

            $detections = $query->latest('detection_timestamp')
                ->paginate($request->input('per_page', 20));

            return ApiResponseHelper::paginated($detections, "Detection history for re_id '{$reId}' retrieved successfully");
        } catch (\Exception $e) {
            return ApiResponseHelper::serverError(
                'Failed to retrieve person detections',
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Get detections by branch
     *
     * @param int $branchId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function branchDetections(int $branchId, Request $request) {
        try {
            // Verify branch exists
            $branch = CompanyBranch::find($branchId);
            if (!$branch) {
                return ApiResponseHelper::notFound("Branch with ID {$branchId} not found");
            }

            $query = ReIdBranchDetection::where('branch_id', $branchId)
                ->with(['device', 'reIdMaster']);

            // Filter by date
            $date = $request->input('date', now()->toDateString());
            $query->whereDate('detection_timestamp', $date);

            // Filter by device
            if ($request->filled('device_id')) {
                $query->where('device_id', $request->device_id);
            }

            $detections = $query->latest('detection_timestamp')
                ->paginate($request->input('per_page', 20));

            // Get branch statistics for the date
            $stats = [
                'branch_id' => $branchId,
                'branch_name' => $branch->branch_name,
                'city' => $branch->city,
                'date' => $date,
                'total_detections' => ReIdBranchDetection::where('branch_id', $branchId)
                    ->whereDate('detection_timestamp', $date)
                    ->count(),
                'unique_persons' => ReIdBranchDetection::where('branch_id', $branchId)
                    ->whereDate('detection_timestamp', $date)
                    ->distinct('re_id')
                    ->count('re_id'),
                'unique_devices' => ReIdBranchDetection::where('branch_id', $branchId)
                    ->whereDate('detection_timestamp', $date)
                    ->distinct('device_id')
                    ->count('device_id'),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Branch detections retrieved successfully',
                'data' => $detections->items(),
                'statistics' => $stats,
                'pagination' => [
                    'current_page' => $detections->currentPage(),
                    'per_page' => $detections->perPage(),
                    'total' => $detections->total(),
                    'last_page' => $detections->lastPage(),
                    'from' => $detections->firstItem(),
                    'to' => $detections->lastItem(),
                ],
                'meta' => [
                    'timestamp' => now()->toIso8601String(),
                    'version' => '1.0',
                    'request_id' => (string) Str::uuid(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return ApiResponseHelper::serverError(
                'Failed to retrieve branch detections',
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Get global detection summary
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function summary(Request $request) {
        try {
            $date = $request->input('date', now()->toDateString());

            // Global statistics
            $totalDetections = ReIdBranchDetection::whereDate('detection_timestamp', $date)->count();
            $uniquePersons = ReIdBranchDetection::whereDate('detection_timestamp', $date)
                ->distinct('re_id')
                ->count('re_id');
            $uniqueBranches = ReIdBranchDetection::whereDate('detection_timestamp', $date)
                ->distinct('branch_id')
                ->count('branch_id');
            $uniqueDevices = ReIdBranchDetection::whereDate('detection_timestamp', $date)
                ->distinct('device_id')
                ->count('device_id');

            // Top branches by detections
            $topBranches = ReIdBranchDetection::whereDate('detection_timestamp', $date)
                ->select('branch_id', DB::raw('COUNT(*) as detection_count'))
                ->with('branch:id,branch_name,city')
                ->groupBy('branch_id')
                ->orderByDesc('detection_count')
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    return [
                        'branch_id' => $item->branch_id,
                        'branch_name' => $item->branch->branch_name ?? 'N/A',
                        'city' => $item->branch->city ?? 'N/A',
                        'detection_count' => $item->detection_count,
                    ];
                });

            // Top persons by detections
            $topPersons = ReIdBranchDetection::whereDate('detection_timestamp', $date)
                ->select('re_id', DB::raw('COUNT(*) as detection_count'))
                ->groupBy('re_id')
                ->orderByDesc('detection_count')
                ->limit(10)
                ->get();

            // Hourly trend
            $hourlyTrend = ReIdBranchDetection::whereDate('detection_timestamp', $date)
                ->select(
                    DB::raw('EXTRACT(HOUR FROM detection_timestamp) as hour'),
                    DB::raw('COUNT(*) as count'),
                    DB::raw('COUNT(DISTINCT re_id) as unique_persons')
                )
                ->groupBy(DB::raw('EXTRACT(HOUR FROM detection_timestamp)'))
                ->orderBy('hour')
                ->get();

            return ApiResponseHelper::success([
                'date' => $date,
                'summary' => [
                    'total_detections' => $totalDetections,
                    'unique_persons' => $uniquePersons,
                    'unique_branches' => $uniqueBranches,
                    'unique_devices' => $uniqueDevices,
                ],
                'top_branches' => $topBranches,
                'top_persons' => $topPersons,
                'hourly_trend' => $hourlyTrend,
            ], 'Detection summary retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponseHelper::serverError(
                'Failed to retrieve detection summary',
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Get detection status by job ID
     *
     * @param string $jobId
     * @return \Illuminate\Http\JsonResponse
     */
    public function status(string $jobId) {
        // Check if job exists in jobs table (still processing)
        $job = DB::table('jobs')->where('id', $jobId)->first();

        if ($job) {
            return ApiResponseHelper::success([
                'job_id' => $jobId,
                'status' => 'processing',
                'attempts' => $job->attempts,
            ], 'Job is still processing');
        }

        // Check failed_jobs
        $failedJob = DB::table('failed_jobs')->where('uuid', $jobId)->first();

        if ($failedJob) {
            return ApiResponseHelper::error(
                'Job processing failed',
                'JOB_FAILED',
                ['error' => $failedJob->exception],
                500
            );
        }

        // Job completed (not in queue tables)
        return ApiResponseHelper::success([
            'job_id' => $jobId,
            'status' => 'completed',
        ], 'Job completed successfully');
    }
}
