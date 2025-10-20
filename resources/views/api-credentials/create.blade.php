@extends('layouts.app')

@section('title', 'Create API Credential')
@section('page-title', 'Create New API Credential')

@section('content')
  <div class="max-w-3xl mx-auto">
    <x-card title="API Credential Information">
      <div class="mb-6">
        <p class="text-sm text-gray-500">Create a new API credential for external system integration</p>
      </div>

      <form method="POST" action="{{ route('api-credentials.store') }}" class="space-y-5">
        @csrf

        <x-input name="credential_name" label="Credential Name" placeholder="e.g., Mobile App API Key" required
          hint="Descriptive name for this credential" />

        <!-- Scope Configuration (Auto - Disabled) -->
        <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
          <h4 class="text-sm font-semibold text-blue-900 mb-3">Access Scope (Auto-configured)</h4>

          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">
                Branch Scope
              </label>
              <input type="text" value="Global - All Branches" disabled
                class="block w-full px-3 py-2 text-sm rounded-lg border-gray-300 bg-gray-50 text-gray-500 cursor-not-allowed">
              <p class="text-xs text-gray-600 mt-1">✅ Automatically set to global access (all branches)</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">
                Device Scope
              </label>
              <input type="text" value="Global - All Devices" disabled
                class="block w-full px-3 py-2 text-sm rounded-lg border-gray-300 bg-gray-50 text-gray-500 cursor-not-allowed">
              <p class="text-xs text-gray-600 mt-1">✅ Automatically set to global access (all devices)</p>
            </div>
          </div>
        </div>

        <!-- Permissions (Auto - Disabled) -->
        <div class="p-4 bg-purple-50 border border-purple-200 rounded-lg">
          <h4 class="text-sm font-semibold text-purple-900 mb-3">Permissions (Auto-configured)</h4>
          <div class="space-y-3">
            <label class="flex items-center">
              <input type="checkbox" checked disabled
                class="rounded border-gray-300 text-purple-600 bg-gray-50 cursor-not-allowed">
              <span class="ml-2 text-sm text-gray-700">✅ Read Access</span>
            </label>
            <label class="flex items-center">
              <input type="checkbox" checked disabled
                class="rounded border-gray-300 text-purple-600 bg-gray-50 cursor-not-allowed">
              <span class="ml-2 text-sm text-gray-700">✅ Write Access</span>
            </label>
            <label class="flex items-center">
              <input type="checkbox" checked disabled
                class="rounded border-gray-300 text-purple-600 bg-gray-50 cursor-not-allowed">
              <span class="ml-2 text-sm text-gray-700">✅ Delete Access</span>
            </label>
          </div>
          <p class="text-xs text-gray-600 mt-3">✅ All permissions automatically enabled (full access)</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="rate_limit" class="block text-sm font-medium text-gray-700 mb-1.5">
              Rate Limit (per hour)
            </label>
            <input type="text" id="rate_limit" value="10,000 requests/hour" disabled
              class="block w-full px-3 py-2 text-sm rounded-lg border-gray-300 bg-gray-50 text-gray-500 cursor-not-allowed">
            <p class="text-xs text-gray-600 mt-1">✅ Automatically set to 10,000 requests per hour</p>
          </div>

          <x-input type="date" name="expires_at" label="Expiration Date (Optional)" :value="old('expires_at')"
            hint="Leave empty for no expiration" />
        </div>

        <x-select name="status" label="Status" :options="['active' => 'Active', 'inactive' => 'Inactive']" selected="active" placeholder="" required
          hint="Initial status for this credential" />

        <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
          <div class="flex">
            <svg class="w-5 h-5 text-yellow-600 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
              viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div>
              <p class="text-sm font-medium text-yellow-800">Security Notice</p>
              <p class="text-xs text-yellow-700 mt-1">
                API Key and Secret will be generated automatically.
                <strong>Save the API Secret immediately</strong> as it will only be shown once after creation.
              </p>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
          <x-button variant="secondary" :href="route('api-credentials.index')">
            Cancel
          </x-button>
          <x-button type="submit" variant="primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
            </svg>
            Create Credential
          </x-button>
        </div>
      </form>
    </x-card>
  </div>
@endsection
