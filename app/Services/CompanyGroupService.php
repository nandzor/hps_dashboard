<?php

namespace App\Services;

use App\Models\CompanyGroup;
use Illuminate\Database\Eloquent\Model;

class CompanyGroupService extends BaseService {
    /**
     * CompanyGroupService constructor.
     */
    public function __construct() {
        $this->model = new CompanyGroup();
        $this->searchableFields = ['group_name', 'province', 'description'];
        $this->orderByColumn = 'created_at';
        $this->orderByDirection = 'desc';
    }

    /**
     * Override base query to include branches relationship and count
     */
    protected function getBaseQuery(): \Illuminate\Database\Eloquent\Builder {
        return parent::getBaseQuery()->with('branches')->withCount('branches');
    }

    /**
     * Get all active groups with branch count
     */
    public function getActiveGroupsWithBranchCount() {
        return CompanyGroup::active()
            ->withCount('branches')
            ->orderBy('group_name', 'asc')
            ->get();
    }

    /**
     * Get group by ID with branches
     */
    public function getGroupWithBranches(int $id): ?CompanyGroup {
        return CompanyGroup::with(['branches' => function ($query) {
            $query->active()->orderBy('branch_name', 'asc');
        }])->find($id);
    }

    /**
     * Get groups by province
     */
    public function getByProvince(string $province) {
        return CompanyGroup::where('province', $province)
            ->active()
            ->orderBy('group_name', 'asc')
            ->get();
    }

    /**
     * Create company group
     */
    public function createGroup(array $data): CompanyGroup {
        return $this->create($data);
    }

    /**
     * Update company group
     */
    public function updateGroup(CompanyGroup $group, array $data): bool {
        return $this->update($group, $data);
    }

    /**
     * Delete company group (soft delete via status)
     */
    public function deleteGroup(CompanyGroup $group): bool {
        // Check if group has active branches
        if ($group->branches()->where('status', 'active')->exists()) {
            throw new \Exception('Cannot delete group with active branches. Please deactivate branches first.');
        }

        // Soft delete by setting status to inactive
        return $group->update(['status' => 'inactive']);
    }

    /**
     * Activate company group
     */
    public function activateGroup(CompanyGroup $group): bool {
        return $group->update(['status' => 'active']);
    }

    /**
     * Get branch counts for a group
     */
    public function getBranchCounts(CompanyGroup $group): array {
        $branches = $group->branches;

        return [
            'total' => $branches->count(),
            'active' => $branches->where('status', 'active')->count(),
            'inactive' => $branches->where('status', 'inactive')->count(),
        ];
    }

    /**
     * Get statistics
     */
    public function getStatistics(): array {
        $total = CompanyGroup::count();
        $active = CompanyGroup::active()->count();
        $inactive = CompanyGroup::inactive()->count();
        $totalBranches = CompanyGroup::withCount('branches')->get()->sum('branches_count');

        return [
            'total_groups' => $total,
            'active_groups' => $active,
            'inactive_groups' => $inactive,
            'total_branches' => $totalBranches,
        ];
    }
}
