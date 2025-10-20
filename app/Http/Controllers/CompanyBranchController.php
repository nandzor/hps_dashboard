<?php

namespace App\Http\Controllers;

use App\Models\CompanyBranch;
use App\Models\CompanyGroup;
use App\Services\CompanyBranchService;
use App\Http\Requests\StoreCompanyBranchRequest;
use App\Http\Requests\UpdateCompanyBranchRequest;
use Illuminate\Http\Request;

class CompanyBranchController extends Controller {
    protected $companyBranchService;

    public function __construct(CompanyBranchService $companyBranchService) {
        $this->companyBranchService = $companyBranchService;
    }

    public function index(Request $request) {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);
        $status = $request->input('status');
        $groupId = $request->input('group_id');

        $filters = [];
        if ($status) $filters['status'] = $status;
        if ($groupId) $filters['group_id'] = $groupId;

        $companyBranches = $this->companyBranchService->getPaginate($search, $perPage, $filters);
        $statistics = $this->companyBranchService->getStatistics();
        $companyGroups = CompanyGroup::active()->orderBy('group_name')->get();

        return view('company-branches.index', compact('companyBranches', 'statistics', 'companyGroups', 'search', 'perPage', 'status', 'groupId'));
    }

    public function create(Request $request) {
        $companyGroups = CompanyGroup::active()->orderBy('group_name')->get();
        $selectedGroupId = $request->input('group_id');
        return view('company-branches.create', compact('companyGroups', 'selectedGroupId'));
    }

    public function store(StoreCompanyBranchRequest $request) {
        try {
            $data = $request->validated();
            $data['status'] = $data['status'] ?? 'active';
            $this->companyBranchService->createBranch($data);

            return redirect()->route('company-branches.index')
                ->with('success', 'Company branch created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to create branch: ' . $e->getMessage());
        }
    }

    public function show(CompanyBranch $companyBranch) {
        $branch = $this->companyBranchService->getBranchWithRelationships($companyBranch->id);
        if (!$branch) abort(404);

        $deviceCounts = $this->companyBranchService->getDeviceCounts($branch);

        return view('company-branches.show', compact('branch', 'deviceCounts'));
    }

    public function edit(CompanyBranch $companyBranch) {
        $companyGroups = CompanyGroup::active()->orderBy('group_name')->get();
        return view('company-branches.edit', compact('companyBranch', 'companyGroups'));
    }

    public function update(UpdateCompanyBranchRequest $request, CompanyBranch $companyBranch) {
        try {
            $this->companyBranchService->updateBranch($companyBranch, $request->validated());
            return redirect()->route('company-branches.show', $companyBranch)
                ->with('success', 'Branch updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to update branch: ' . $e->getMessage());
        }
    }

    public function destroy(CompanyBranch $companyBranch) {
        try {
            $this->companyBranchService->deleteBranch($companyBranch);
            return redirect()->route('company-branches.index')
                ->with('success', 'Branch deactivated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete branch: ' . $e->getMessage());
        }
    }
}
