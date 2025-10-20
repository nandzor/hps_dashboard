@extends('layouts.app')

@section('title', 'Device Masters')
@section('page-title', 'Device Masters Management')

@section('content')
  <x-card>
    <!-- Header -->
    <div class="p-6 border-b border-gray-200">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
        <div class="flex-1 max-w-md">
          <form method="GET" action="{{ route('device-masters.index') }}" class="flex">
            <x-input name="search" :value="$search ?? ''" placeholder="Search devices..." class="rounded-r-none border-r-0" />
            @if (request()->has('per_page'))
              <input type="hidden" name="per_page" value="{{ request()->get('per_page') }}">
            @endif
            <button type="submit"
              class="px-6 py-2 bg-gray-600 text-white rounded-r-lg hover:bg-gray-700 transition-colors">
              Search
            </button>
          </form>
        </div>

        <div class="flex items-center space-x-4">
          <!-- Per Page Selector -->
          <div class="flex items-center space-x-2">
            <x-per-page-selector :options="$perPageOptions ?? [10, 25, 50, 100]" :current="$perPage ?? 10" :url="route('device-masters.index')" type="server" />
          </div>

          <!-- Add Device Button -->
          <x-button variant="primary" size="sm" :href="route('device-masters.create')">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Device
          </x-button>
        </div>
      </div>
    </div>

    <!-- Table -->
    <x-table :headers="['Device ID', 'Device Name', 'Type', 'Branch', 'Status', 'Actions']">
      @forelse($deviceMasters as $device)
        <tr class="hover:bg-blue-50 transition-colors">
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="flex items-center">
              <div class="flex-shrink-0 h-10 w-10">
                @if($device->device_type === 'cctv')
                  <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center shadow-md">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                  </div>
                @elseif($device->device_type === 'node_ai')
                  <div class="h-10 w-10 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center shadow-md">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                  </div>
                @elseif($device->device_type === 'mikrotik')
                  <div class="h-10 w-10 rounded-full bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center shadow-md">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                    </svg>
                  </div>
                @else
                  <div class="h-10 w-10 rounded-full bg-gradient-to-br from-gray-400 to-gray-600 flex items-center justify-center shadow-md">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                  </div>
                @endif
              </div>
              <div class="ml-4">
                <div class="text-sm font-medium text-gray-900">{{ $device->device_id }}</div>
              </div>
            </div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-900">{{ $device->device_name }}</div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <x-badge :variant="$device->device_type === 'cctv' ? 'primary' : ($device->device_type === 'node_ai' ? 'purple' : ($device->device_type === 'mikrotik' ? 'success' : 'gray'))">
              {{ ucfirst(str_replace('_', ' ', $device->device_type)) }}
            </x-badge>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            {{ $device->branch->branch_name ?? 'N/A' }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <x-badge :variant="$device->status === 'active' ? 'success' : 'danger'">
              {{ ucfirst($device->status) }}
            </x-badge>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
            <x-action-dropdown>
              <x-dropdown-link :href="route('device-masters.show', $device)">
                👁️ View Details
              </x-dropdown-link>

              <x-dropdown-link :href="route('device-masters.edit', $device)">
                ✏️ Edit Device
              </x-dropdown-link>

              <x-dropdown-divider />

              <x-dropdown-button type="button" onclick="confirmDelete({{ $device->id }})" variant="danger">
                🗑️ Delete Device
              </x-dropdown-button>

              <form id="delete-form-{{ $device->id }}" action="{{ route('device-masters.destroy', $device->id) }}" method="POST"
                class="hidden">
                @csrf
                @method('DELETE')
              </form>
            </x-action-dropdown>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="px-6 py-12 text-center">
            <div class="flex flex-col items-center justify-center">
              <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
              <p class="text-gray-500 text-lg font-medium">No devices found</p>
              <p class="text-gray-400 text-sm mt-1">Try adjusting your search criteria</p>
            </div>
          </td>
        </tr>
      @endforelse
    </x-table>

    <!-- Pagination Info & Controls -->
    <div class="px-6 py-4 border-t border-gray-200">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
        <!-- Pagination Info -->
        <div class="text-sm text-gray-700">
          Showing
          <span class="font-medium">{{ $deviceMasters->firstItem() ?? 0 }}</span>
          to
          <span class="font-medium">{{ $deviceMasters->lastItem() ?? 0 }}</span>
          of
          <span class="font-medium">{{ $deviceMasters->total() }}</span>
          results
          @if (request()->has('search'))
            for "<span class="font-medium text-blue-600">{{ request()->get('search') }}</span>"
          @endif
        </div>

        <!-- Pagination Controls -->
        @if ($deviceMasters->hasPages())
          <x-pagination :paginator="$deviceMasters" />
        @endif
      </div>
    </div>
  </x-card>

  <!-- Delete Confirmation Modal -->
  <x-confirm-modal id="confirm-delete" title="Confirm Delete"
    message="This action cannot be undone. The device will be permanently deleted." confirmText="Delete Device"
    cancelText="Cancel" icon="warning" confirmAction="handleDeleteConfirm(data)" />
@endsection

@push('scripts')
  <script>
    // Store deviceId for deletion
    let pendingDeleteDeviceId = null;

    function confirmDelete(deviceId) {
      pendingDeleteDeviceId = deviceId;
      // Dispatch event to open modal with deviceId
      window.dispatchEvent(new CustomEvent('open-modal-confirm-delete', {
        detail: {
          deviceId: deviceId
        }
      }));
    }

    function handleDeleteConfirm(data) {
      const deviceId = data?.deviceId || pendingDeleteDeviceId;
      if (deviceId) {
        const form = document.getElementById('delete-form-' + deviceId);
        if (form) {
          form.submit();
        }
      }
    }

    // Make functions globally available
    window.confirmDelete = confirmDelete;
    window.handleDeleteConfirm = handleDeleteConfirm;
  </script>
@endpush







