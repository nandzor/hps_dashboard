<?php

namespace App\Services;

use App\Models\BranchEventSetting;
use Illuminate\Database\Eloquent\Model;

class BranchEventSettingService extends BaseService {
    /**
     * BranchEventSettingService constructor.
     */
    public function __construct() {
        $this->model = new BranchEventSetting();
        $this->searchableFields = ['message_template'];
        $this->orderByColumn = 'created_at';
        $this->orderByDirection = 'desc';
    }

    /**
     * Get event setting by ID with all relationships
     */
    public function getEventSettingWithRelationships(int $id): ?BranchEventSetting {
        return BranchEventSetting::with([
            'branch',
            'device'
        ])->find($id);
    }

    /**
     * Get event settings by branch
     */
    public function getByBranch(int $branchId) {
        return BranchEventSetting::where('branch_id', $branchId)
            ->with(['device'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get event settings by device
     */
    public function getByDevice(string $deviceId) {
        return BranchEventSetting::where('device_id', $deviceId)
            ->with(['branch'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get active event settings
     */
    public function getActiveSettings() {
        return BranchEventSetting::active()
            ->with(['branch', 'device'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get WhatsApp enabled settings
     */
    public function getWhatsAppEnabledSettings() {
        return BranchEventSetting::whatsAppEnabled()
            ->active()
            ->with(['branch', 'device'])
            ->orderBy('created_at', 'desc')
            ->get();
    }


    /**
     * Update branch event setting
     */
    public function updateEventSetting(BranchEventSetting $eventSetting, array $data): bool {
        return $this->update($eventSetting, $data);
    }


    /**
     * Get statistics for dashboard
     */
    public function getStatistics(): array {
        $total = BranchEventSetting::count();
        $active = BranchEventSetting::active()->count();
        $whatsappEnabled = BranchEventSetting::whatsAppEnabled()->count();
        $sendImageEnabled = BranchEventSetting::where('send_image', true)->count();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $total - $active,
            'whatsapp_enabled' => $whatsappEnabled,
            'send_image_enabled' => $sendImageEnabled,
        ];
    }

    /**
     * Apply filters to query
     */
    protected function applyFilters(\Illuminate\Database\Eloquent\Builder $query, array $filters): \Illuminate\Database\Eloquent\Builder {
        if (isset($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (isset($filters['device_id'])) {
            $query->where('device_id', $filters['device_id']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query;
    }
}
