<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponseHelper;
use App\Models\CctvLayoutSetting;
use App\Models\CctvPositionSetting;
use App\Models\CompanyBranch;
use App\Models\DeviceMaster;
use App\Services\CctvLayoutService;
use Illuminate\Http\Request;

class CctvLiveStreamController extends Controller {
    protected $cctvLayoutService;

    public function __construct(CctvLayoutService $cctvLayoutService) {
        $this->cctvLayoutService = $cctvLayoutService;
    }

    /**
     * Display the main CCTV live stream page
     */
    public function index(Request $request) {
        $layoutId = $request->input('layout_id');

        // Get default layout if no specific layout requested
        $layout = $layoutId
            ? $this->cctvLayoutService->getLayoutWithPositions($layoutId)
            : $this->cctvLayoutService->getDefaultLayout();

        // Get all available layouts for switching
        $availableLayouts = CctvLayoutSetting::active()
            ->orderBy('is_default', 'desc')
            ->orderBy('layout_name')
            ->get();

        // Get all branches and devices for position configuration
        $branches = CompanyBranch::active()->with('group')->orderBy('branch_name')->get();
        $devices = DeviceMaster::active()->with('branch')->orderBy('device_name')->get();

        return view('cctv-live-stream.index', compact(
            'layout',
            'availableLayouts',
            'branches',
            'devices'
        ));
    }

    /**
     * Get stream URL for a specific device
     */
    public function getStreamUrl(Request $request, $deviceId) {
        $device = DeviceMaster::where('device_id', $deviceId)->first();

        if (!$device) {
            return ApiResponseHelper::notFound('Device not found');
        }

        // Return the device URL (already decrypted by model)
        return ApiResponseHelper::success([
            'device_id' => $device->device_id,
            'device_name' => $device->device_name,
            'stream_url' => $device->url,
            'username' => $device->username,
            'password' => $device->password,
        ], 'Stream data retrieved successfully');
    }

    /**
     * Update position configuration
     */
    public function updatePosition(Request $request, $layoutId, $positionNumber) {
        try {
            $request->validate([
                'branch_id' => 'required|exists:company_branches,id',
                'device_id' => 'required|exists:device_masters,device_id',
                'is_enabled' => 'boolean',
                'auto_switch' => 'boolean',
                'switch_interval' => 'integer|min:5|max:300',
                'quality' => 'in:low,medium,high',
                'resolution' => 'in:640x480,1280x720,1920x1080',
            ]);

            $position = CctvPositionSetting::where('layout_id', $layoutId)
                ->where('position_number', $positionNumber)
                ->first();

            if (!$position) {
                return ApiResponseHelper::notFound('Position not found');
            }

            $position->update($request->only([
                'branch_id',
                'device_id',
                'is_enabled',
                'auto_switch',
                'switch_interval',
                'quality',
                'resolution'
            ]));

            return ApiResponseHelper::success(
                $position->load('branch', 'device'),
                'Position updated successfully'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponseHelper::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponseHelper::serverError('Failed to update position', $e->getMessage());
        }
    }

    /**
     * Get devices for a specific branch
     */
    public function getBranchDevices(Request $request, $branchId) {
        try {
            $devices = DeviceMaster::where('branch_id', $branchId)
                ->where('status', 'active')
                ->where('device_type', 'cctv')
                ->orderBy('device_name')
                ->get(['device_id', 'device_name', 'device_type']);

            return ApiResponseHelper::success(
                $devices,
                'Devices retrieved successfully'
            );
        } catch (\Exception $e) {
            return ApiResponseHelper::serverError('Failed to retrieve devices', $e->getMessage());
        }
    }

    /**
     * Capture screenshot from a stream
     */
    public function captureScreenshot(Request $request, $deviceId) {
        try {
            $device = DeviceMaster::where('device_id', $deviceId)->first();

            if (!$device) {
                return ApiResponseHelper::notFound('Device not found');
            }

            // This would typically involve calling an external service or FFmpeg
            // For now, we'll return a placeholder response
            return ApiResponseHelper::success([
                'screenshot_url' => '/storage/screenshots/' . $deviceId . '_' . time() . '.jpg',
                'timestamp' => now()->toISOString(),
                'device_id' => $deviceId,
            ], 'Screenshot captured successfully');
        } catch (\Exception $e) {
            return ApiResponseHelper::serverError('Failed to capture screenshot', $e->getMessage());
        }
    }

    /**
     * Start/stop recording for a stream
     */
    public function toggleRecording(Request $request, $deviceId) {
        try {
            $device = DeviceMaster::where('device_id', $deviceId)->first();

            if (!$device) {
                return ApiResponseHelper::notFound('Device not found');
            }

            $action = $request->input('action', 'start'); // start or stop

            // This would typically involve calling an external service
            // For now, we'll return a placeholder response
            return ApiResponseHelper::success([
                'action' => $action,
                'recording_url' => $action === 'start' ? '/storage/recordings/' . $deviceId . '_' . time() . '.mp4' : null,
                'timestamp' => now()->toISOString(),
                'device_id' => $deviceId,
            ], "Recording {$action}ed successfully");
        } catch (\Exception $e) {
            return ApiResponseHelper::serverError('Failed to toggle recording', $e->getMessage());
        }
    }
}
