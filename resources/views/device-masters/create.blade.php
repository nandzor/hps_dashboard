@extends('layouts.app')

@section('title', 'Create Device')
@section('page-title', 'Create New Device')

@section('content')
  <div class="max-w-3xl">
    <x-card title="Device Information">
      <div class="mb-6">
        <p class="text-sm text-gray-500">Fill in the details to create a new device</p>
      </div>

      <form method="POST" action="{{ route('device-masters.store') }}" class="space-y-5">
        @csrf

        <x-input name="device_id" label="Device ID" placeholder="e.g., CAMERA_001" required
          hint="Unique identifier for the device (no spaces allowed)" onkeypress="return event.charCode != 32" />

        <x-input name="device_name" label="Device Name" placeholder="Main Entrance Camera" required
          hint="Descriptive name for the device" />

        <x-device-type-select name="device_type" label="Device Type" required hint="Select the type of device" />

        <x-company-branch-select name="branch_id" label="Branch" required
          hint="Select the branch where this device is located" />

        <x-input name="url" label="URL / IP Address" placeholder="rtsp://192.168.1.100:554/stream1"
          hint="Network address or URL for the device" />

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <x-input name="username" label="Username" placeholder="Enter username" hint="Login username for the device" />
          <x-input type="password" name="password" label="Password" placeholder="Enter password"
            hint="Login password for the device" />
        </div>

        <x-textarea name="notes" label="Notes" placeholder="Additional information about the device..." rows="3"
          hint="Optional notes or comments" />

        <x-status-select name="status" label="Status" value="active" required hint="Device status" :showAllOption="false" />

        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
          <x-button variant="secondary" :href="route('device-masters.index')">
            Cancel
          </x-button>
          <x-button type="submit" variant="primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Create Device
          </x-button>
        </div>
      </form>
    </x-card>
  </div>
@endsection
