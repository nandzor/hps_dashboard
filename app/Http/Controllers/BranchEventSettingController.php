<?php

namespace App\Http\Controllers;

use App\Models\BranchEventSetting;
use App\Models\CompanyBranch;
use App\Models\DeviceMaster;
use App\Services\BranchEventSettingService;
use Illuminate\Http\Request;

class BranchEventSettingController extends Controller {
    protected $branchEventSettingService;

    public function __construct(BranchEventSettingService $branchEventSettingService) {
        $this->branchEventSettingService = $branchEventSettingService;
    }

    public function index(Request $request) {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);
        $branchId = $request->input('branch_id');
        $deviceId = $request->input('device_id');
        $isActive = $request->input('is_active');

        $filters = [];
        if ($branchId) $filters['branch_id'] = $branchId;
        if ($deviceId) $filters['device_id'] = $deviceId;
        if ($isActive !== null && $isActive !== '') $filters['is_active'] = $isActive;

        $eventSettings = $this->branchEventSettingService->getPaginate($search, $perPage, $filters);
        $statistics = $this->branchEventSettingService->getStatistics();
        $branches = CompanyBranch::active()->orderBy('branch_name')->get();
        $devices = DeviceMaster::active()->orderBy('device_name')->get();

        return view('branch-event-settings.index', compact(
            'eventSettings',
            'statistics',
            'branches',
            'devices',
            'search',
            'perPage',
            'branchId',
            'deviceId',
            'isActive'
        ));
    }


    public function show(BranchEventSetting $branchEventSetting) {
        $eventSetting = $this->branchEventSettingService->getEventSettingWithRelationships($branchEventSetting->id);
        if (!$eventSetting) abort(404);

        return view('branch-event-settings.show', compact('eventSetting'));
    }

    public function edit(BranchEventSetting $branchEventSetting) {
        return view('branch-event-settings.edit', compact('branchEventSetting'));
    }

    public function update(Request $request, BranchEventSetting $branchEventSetting) {
        try {
            $data = $request->validate([
                'is_active' => ['nullable', 'boolean'],
                'send_image' => ['nullable', 'boolean'],
                'send_message' => ['nullable', 'boolean'],
                'whatsapp_enabled' => ['nullable', 'boolean'],
                'whatsapp_numbers' => ['nullable', 'string', 'max:1000'],
            ]);

            // Convert WhatsApp numbers to array
            if (isset($data['whatsapp_numbers'])) {
                $data['whatsapp_numbers'] = array_filter(explode(',', $data['whatsapp_numbers']));
            }

            $this->branchEventSettingService->updateEventSetting($branchEventSetting, $data);

            return redirect()->route('branch-event-settings.show', $branchEventSetting)
                ->with('success', 'Event setting updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to update event setting: ' . $e->getMessage());
        }
    }


    public function toggle(BranchEventSetting $branchEventSetting) {
        try {
            $branchEventSetting->update(['is_active' => !$branchEventSetting->is_active]);

            return response()->json([
                'success' => true,
                'is_active' => $branchEventSetting->is_active,
                'message' => 'Event setting ' . ($branchEventSetting->is_active ? 'activated' : 'deactivated') . ' successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle event setting: ' . $e->getMessage()
            ], 500);
        }
    }
}
