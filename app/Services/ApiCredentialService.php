<?php

namespace App\Services;

use App\Models\ApiCredential;

class ApiCredentialService extends BaseService {
    public function __construct() {
        $this->model = new ApiCredential();
        $this->searchableFields = ['credential_name', 'api_key'];
        $this->orderByColumn = 'created_at';
        $this->orderByDirection = 'desc';
    }

    /**
     * Get credential with relationships
     */
    public function getWithRelations(int $id) {
        return ApiCredential::with(['branch', 'device', 'creator'])
            ->findOrFail($id);
    }

    /**
     * Create new API credential
     */
    public function createCredential(array $data): ApiCredential {
        // Generate keys if not provided
        if (empty($data['api_key'])) {
            $data['api_key'] = ApiCredential::generateApiKey();
        }

        if (empty($data['api_secret'])) {
            $data['api_secret'] = ApiCredential::generateApiSecret();
        }

        // Set created_by
        if (auth()->check()) {
            $data['created_by'] = auth()->id();
        }

        return ApiCredential::create($data);
    }

    /**
     * Update credential
     */
    public function updateCredential(ApiCredential $credential, array $data): bool {
        // Don't allow updating api_key after creation
        unset($data['api_key']);

        // Only regenerate secret if explicitly requested
        if (isset($data['regenerate_secret']) && $data['regenerate_secret']) {
            $data['api_secret'] = ApiCredential::generateApiSecret();
        }
        unset($data['regenerate_secret']);

        return $credential->update($data);
    }

    /**
     * Get statistics
     */
    public function getStatistics(): array {
        return [
            'total' => ApiCredential::count(),
            'active' => ApiCredential::where('status', 'active')->count(),
            'inactive' => ApiCredential::where('status', 'inactive')->count(),
            'expired' => ApiCredential::where('status', 'expired')->count(),
            'global_access' => ApiCredential::whereNull('branch_id')->whereNull('device_id')->count(),
            'branch_scoped' => ApiCredential::whereNotNull('branch_id')->count(),
            'device_scoped' => ApiCredential::whereNotNull('device_id')->count(),
        ];
    }

    /**
     * Revoke credential (set to inactive)
     */
    public function revokeCredential(ApiCredential $credential): bool {
        return $credential->update(['status' => 'inactive']);
    }

    /**
     * Activate credential
     */
    public function activateCredential(ApiCredential $credential): bool {
        return $credential->update(['status' => 'active']);
    }
}
