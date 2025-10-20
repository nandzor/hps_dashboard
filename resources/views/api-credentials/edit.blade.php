@extends('layouts.app')

@section('title', 'Edit API Credential')
@section('page-title', 'Edit API Credential')

@section('content')
  <div class="max-w-3xl mx-auto">
    <x-card title="Update API Credential">
      <div class="mb-6 flex items-center justify-between">
        <p class="text-sm text-gray-500">Modify API credential settings</p>
        <x-badge :variant="$apiCredential->status === 'active' ? 'success' : 'danger'">
          {{ ucfirst($apiCredential->status) }}
        </x-badge>
      </div>

      <form method="POST" action="{{ route('api-credentials.update', $apiCredential) }}" class="space-y-5">
        @csrf
        @method('PUT')

        <x-input name="credential_name" label="Credential Name" :value="$apiCredential->credential_name" placeholder="e.g., Mobile App API Key"
          required hint="Descriptive name for this credential" />

        <!-- API Key (Read-only) -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">API Key</label>
          <code
            class="block text-xs bg-gray-100 text-gray-700 p-3 rounded font-mono break-all">{{ $apiCredential->api_key }}</code>
          <p class="text-sm text-gray-600 mt-1">API Key cannot be changed after creation</p>
        </div>

        <!-- Scope Info (Always Global) -->
        <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
          <h4 class="text-sm font-semibold text-blue-900 mb-2">Access Scope</h4>
          <div class="flex items-center">
            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-sm text-blue-900 font-medium">Global Access - All Branches & Devices</span>
          </div>
          <p class="text-xs text-blue-700 mt-2 ml-7">This credential has full access to all branches and devices</p>
        </div>

        <!-- Regenerate Secret Option -->
        <div class="p-4 bg-orange-50 border border-orange-200 rounded-lg">
          <x-checkbox name="regenerate_secret" label="Regenerate API Secret" value="1"
            hint="Check this to generate a new API secret. Old secret will be invalidated." />
        </div>

        <x-input type="date" name="expires_at" label="Expiration Date (Optional)" :value="$apiCredential->expires_at ? $apiCredential->expires_at->format('Y-m-d') : ''"
          hint="Leave empty for no expiration" />

        <x-select name="status" label="Status" :options="['active' => 'Active', 'inactive' => 'Inactive', 'expired' => 'Expired']" :selected="$apiCredential->status" placeholder="" required
          hint="Credential status" />

        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
          <x-button variant="secondary" :href="route('api-credentials.show', $apiCredential)">
            Cancel
          </x-button>
          <x-button type="submit" variant="primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Update Credential
          </x-button>
        </div>
      </form>
    </x-card>
  </div>
@endsection
