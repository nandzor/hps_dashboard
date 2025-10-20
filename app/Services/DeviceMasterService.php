<?php

namespace App\Services;

use App\Models\DeviceMaster;
use App\Models\BranchEventSetting;
use App\Models\WhatsAppSettings;
use Illuminate\Support\Facades\DB;

class DeviceMasterService extends BaseService {
    public function __construct() {
        $this->model = new DeviceMaster();
        $this->searchableFields = ['device_id', 'device_name', 'device_type', 'notes'];
        $this->orderByColumn = 'created_at';
        $this->orderByDirection = 'desc';
    }

    public function getDeviceWithRelationships(string $deviceId): ?DeviceMaster {
        return DeviceMaster::with(['branch.group', 'reIdDetections', 'eventSettings'])
            ->where('device_id', $deviceId)->first();
    }

    public function getByBranch(int $branchId) {
        return DeviceMaster::where('branch_id', $branchId)->active()->get();
    }

    public function getByType(string $type) {
        return DeviceMaster::where('device_type', $type)->active()->get();
    }

    public function createDevice(array $data): DeviceMaster {
        return DB::transaction(function () use ($data) {
            // Create the device
            $device = $this->create($data);

            // Create default branch event setting for this device
            $this->createDefaultEventSetting($device);

            return $device;
        });
    }

    public function updateDevice(DeviceMaster $device, array $data): bool {
        return $this->update($device, $data);
    }

    public function deleteDevice(DeviceMaster $device): bool {
        return DB::transaction(function () use ($device) {
            // Delete associated branch event settings
            $this->deleteEventSettings($device);

            // Deactivate the device
            return $device->update(['status' => 'inactive']);
        });
    }

    public function getStatistics(): array {
        return [
            'total_devices' => DeviceMaster::count(),
            'active_devices' => DeviceMaster::active()->count(),
            'inactive_devices' => DeviceMaster::inactive()->count(),
            'by_type' => DeviceMaster::selectRaw('device_type, COUNT(*) as count')
                ->groupBy('device_type')->pluck('count', 'device_type')->toArray(),
        ];
    }

    /**
     * Create default event setting for a device
     */
    private function createDefaultEventSetting(DeviceMaster $device): BranchEventSetting {
        // Get default WhatsApp settings
        $defaultWhatsApp = WhatsAppSettings::getDefault();
        $whatsappNumbers = $defaultWhatsApp ? $defaultWhatsApp->phone_numbers : [];

        $defaultData = [
            'branch_id' => $device->branch_id,
            'device_id' => $device->device_id,
            'is_active' => true,
            'send_image' => false,
            'send_message' => false,
            'whatsapp_enabled' => false,
            'whatsapp_numbers' => $whatsappNumbers,
        ];

        return BranchEventSetting::create($defaultData);
    }

    /**
     * Delete all event settings for a device
     */
    private function deleteEventSettings(DeviceMaster $device): int {
        return BranchEventSetting::where('device_id', $device->device_id)->delete();
    }
}
