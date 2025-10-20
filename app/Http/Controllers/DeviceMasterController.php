<?php

namespace App\Http\Controllers;

use App\Models\DeviceMaster;
use App\Models\CompanyBranch;
use App\Services\DeviceMasterService;
use App\Http\Requests\StoreDeviceMasterRequest;
use App\Http\Requests\UpdateDeviceMasterRequest;
use Illuminate\Http\Request;

class DeviceMasterController extends Controller {
    protected $deviceMasterService;

    public function __construct(DeviceMasterService $deviceMasterService) {
        $this->deviceMasterService = $deviceMasterService;
    }

    public function index(Request $request) {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);
        $filters = [];
        if ($request->filled('status')) $filters['status'] = $request->input('status');
        if ($request->filled('device_type')) $filters['device_type'] = $request->input('device_type');
        if ($request->filled('branch_id')) $filters['branch_id'] = $request->input('branch_id');

        $deviceMasters = $this->deviceMasterService->getPaginate($search, $perPage, $filters);
        $statistics = $this->deviceMasterService->getStatistics();
        $companyBranches = CompanyBranch::active()->get();

        return view('device-masters.index', compact('deviceMasters', 'statistics', 'companyBranches'));
    }

    public function create() {
        $companyBranches = CompanyBranch::active()->with('group')->get();
        return view('device-masters.create', compact('companyBranches'));
    }

    public function store(StoreDeviceMasterRequest $request) {
        try {
            $data = $request->validated();
            $data['status'] = $data['status'] ?? 'active';
            $this->deviceMasterService->createDevice($data);
            return redirect()->route('device-masters.index')->with('success', 'Device created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    public function show(DeviceMaster $deviceMaster) {
        $deviceMaster = $this->deviceMasterService->getDeviceWithRelationships($deviceMaster->device_id);
        if (!$deviceMaster) abort(404);
        return view('device-masters.show', compact('deviceMaster'));
    }

    public function edit(DeviceMaster $deviceMaster) {
        $companyBranches = CompanyBranch::active()->with('group')->get();
        return view('device-masters.edit', compact('deviceMaster', 'companyBranches'));
    }

    public function update(UpdateDeviceMasterRequest $request, DeviceMaster $deviceMaster) {
        try {
            $this->deviceMasterService->updateDevice($deviceMaster, $request->validated());
            return redirect()->route('device-masters.show', $deviceMaster)->with('success', 'Device updated.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    public function destroy(DeviceMaster $deviceMaster) {
        try {
            $this->deviceMasterService->deleteDevice($deviceMaster);
            return redirect()->route('device-masters.index')->with('success', 'Device deactivated.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed: ' . $e->getMessage());
        }
    }
}
