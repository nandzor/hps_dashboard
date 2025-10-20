<?php

namespace App\Http\Controllers;

use App\Models\CompanyGroup;
use App\Services\CompanyGroupService;
use App\Http\Requests\StoreCompanyGroupRequest;
use App\Http\Requests\UpdateCompanyGroupRequest;
use Illuminate\Http\Request;

class CompanyGroupController extends Controller {
    protected $companyGroupService;

    public function __construct(CompanyGroupService $companyGroupService) {
        $this->companyGroupService = $companyGroupService;
    }

    /**
     * Display a listing of company groups.
     */
    public function index(Request $request) {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);
        $status = $request->input('status');

        $filters = [];
        if ($status) {
            $filters['status'] = $status;
        }

        $companyGroups = $this->companyGroupService->getPaginate($search, $perPage, $filters);
        $statistics = $this->companyGroupService->getStatistics();

        return view('company-groups.index', compact('companyGroups', 'statistics', 'search', 'perPage', 'status'));
    }

    /**
     * Show the form for creating a new company group.
     */
    public function create() {
        return view('company-groups.create');
    }

    /**
     * Store a newly created company group.
     */
    public function store(StoreCompanyGroupRequest $request) {
        try {
            $data = $request->validated();
            $data['status'] = $data['status'] ?? 'active';

            $this->companyGroupService->createGroup($data);

            return redirect()
                ->route('company-groups.index')
                ->with('success', 'Company group created successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create company group: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified company group.
     */
    public function show(CompanyGroup $companyGroup) {
        $group = $this->companyGroupService->getGroupWithBranches($companyGroup->id);

        if (!$group) {
            abort(404, 'Company group not found.');
        }

        $branchCounts = $this->companyGroupService->getBranchCounts($group);

        return view('company-groups.show', compact('group', 'branchCounts'));
    }

    /**
     * Show the form for editing the specified company group.
     */
    public function edit(CompanyGroup $companyGroup) {
        return view('company-groups.edit', compact('companyGroup'));
    }

    /**
     * Update the specified company group.
     */
    public function update(UpdateCompanyGroupRequest $request, CompanyGroup $companyGroup) {
        try {
            $data = $request->validated();
            $this->companyGroupService->updateGroup($companyGroup, $data);

            return redirect()
                ->route('company-groups.show', $companyGroup)
                ->with('success', 'Company group updated successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update company group: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified company group.
     */
    public function destroy(CompanyGroup $companyGroup) {
        try {
            $this->companyGroupService->deleteGroup($companyGroup);

            return redirect()
                ->route('company-groups.index')
                ->with('success', 'Company group deactivated successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to delete company group: ' . $e->getMessage());
        }
    }
}
