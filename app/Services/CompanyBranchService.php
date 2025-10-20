<?php

namespace App\Services;

use App\Models\CompanyBranch;
use Illuminate\Database\Eloquent\Model;

class CompanyBranchService extends BaseService {
    /**
     * CompanyBranchService constructor.
     */
    public function __construct() {
        $this->model = new CompanyBranch();
        $this->searchableFields = ['branch_name', 'city', 'address', 'contact_person', 'phone'];
        $this->orderByColumn = 'created_at';
        $this->orderByDirection = 'desc';
    }

    /**
     * Get all active branches with relationships
     */
    public function getActiveBranchesWithGroup() {
        return CompanyBranch::with('group')
            ->active()
            ->orderBy('branch_name', 'asc')
            ->get();
    }

    /**
     * Get branch by ID with all relationships
     */
    public function getBranchWithRelationships(int $id): ?CompanyBranch {
        return CompanyBranch::with([
            'group',
            'devices' => function ($query) {
                $query->orderBy('device_name', 'asc');
            },
            'eventSettings',
            'cctvStreams'
        ])->find($id);
    }

    /**
     * Get branches by group
     */
    public function getByGroup(int $groupId) {
        return CompanyBranch::where('group_id', $groupId)
            ->active()
            ->orderBy('branch_name', 'asc')
            ->get();
    }

    /**
     * Get branches by city
     */
    public function getByCity(string $city) {
        return CompanyBranch::where('city', $city)
            ->active()
            ->orderBy('branch_name', 'asc')
            ->get();
    }

    /**
     * Create company branch
     */
    public function createBranch(array $data): CompanyBranch {
        return $this->create($data);
    }

    /**
     * Update company branch
     */
    public function updateBranch(CompanyBranch $branch, array $data): bool {
        return $this->update($branch, $data);
    }

    /**
     * Delete company branch (soft delete via status)
     */
    public function deleteBranch(CompanyBranch $branch): bool {
        // Check if branch has active devices
        if ($branch->devices()->where('status', 'active')->exists()) {
            throw new \Exception('Cannot delete branch with active devices. Please deactivate devices first.');
        }

        // Soft delete by setting status to inactive
        return $branch->update(['status' => 'inactive']);
    }

    /**
     * Activate company branch
     */
    public function activateBranch(CompanyBranch $branch): bool {
        return $branch->update(['status' => 'active']);
    }

    /**
     * Get device counts for a branch
     */
    public function getDeviceCounts(CompanyBranch $branch): array {
        $devices = $branch->devices;

        return [
            'total' => $devices->count(),
            'active' => $devices->where('status', 'active')->count(),
            'inactive' => $devices->where('status', 'inactive')->count(),
        ];
    }

    /**
     * Get statistics
     */
    public function getStatistics(): array {
        $total = CompanyBranch::count();
        $active = CompanyBranch::active()->count();
        $inactive = CompanyBranch::inactive()->count();
        $totalDevices = CompanyBranch::withCount('devices')->get()->sum('devices_count');

        return [
            'total_branches' => $total,
            'active_branches' => $active,
            'inactive_branches' => $inactive,
            'total_devices' => $totalDevices,
        ];
    }
}
