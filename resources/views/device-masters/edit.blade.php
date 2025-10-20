@extends('layouts.app')

@section('title', 'Edit Device')
@section('page-title', 'Edit Device')

@section('content')
  <div class="max-w-3xl">
    <x-card title="Update Device Information">
      <div class="mb-6">
        <div class="flex items-center justify-between">
          <p class="text-sm text-gray-500">Modify the device details below</p>
          <x-badge :variant="$deviceMaster->status === 'active' ? 'success' : 'danger'">
            {{ ucfirst($deviceMaster->status) }}
          </x-badge>
        </div>
      </div>

      <form method="POST" action="{{ route('device-masters.update', $deviceMaster) }}" class="space-y-5">
        @csrf
        @method('PUT')

        <x-input name="device_id" label="Device ID" :value="$deviceMaster->device_id" placeholder="e.g., CAMERA_001" required
          hint="Unique identifier for the device (no spaces allowed)" onkeypress="return event.charCode != 32" />

        <x-input name="device_name" label="Device Name" :value="$deviceMaster->device_name" placeholder="Main Entrance Camera" required
          hint="Descriptive name for the device" />

        <x-device-type-select name="device_type" :value="$deviceMaster->device_type" label="Device Type" required
          hint="Select the type of device" />

        <x-company-branch-select name="branch_id" :value="$deviceMaster->branch_id" label="Branch" required
          hint="Select the branch where this device is located" />

        <x-input name="url" label="URL / IP Address" :value="$deviceMaster->url" placeholder="rtsp://192.168.1.100:554/stream1"
          hint="Network address or URL for the device" />

        <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
          <div class="flex">
            <svg class="w-5 h-5 text-yellow-600 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
              viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
              <p class="text-sm font-medium text-yellow-800">Credential Update</p>
              <p class="text-xs text-yellow-700 mt-1">Leave password field empty to keep the current password</p>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <x-input name="username" label="Username" :value="$deviceMaster->username" placeholder="Enter username"
            hint="Login username for the device" />
          <x-input type="password" name="password" label="New Password" placeholder="Leave blank to keep current"
            hint="Login password for the device" />
        </div>

        <x-textarea name="notes" label="Notes" :value="$deviceMaster->notes"
          placeholder="Additional information about the device..." rows="3" hint="Optional notes or comments" />

        <x-status-select name="status" label="Status" :value="$deviceMaster->status" required hint="Device status"
          :showAllOption="false" />

        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
          <x-button variant="secondary" :href="route('device-masters.show', $deviceMaster)">
            Cancel
          </x-button>
          <x-button type="submit" variant="primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Update Device
          </x-button>
        </div>
      </form>
    </x-card>
  </div>
@endsection
