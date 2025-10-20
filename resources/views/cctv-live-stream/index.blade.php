@extends('layouts.app')

@section('title', 'CCTV Live Stream')
@section('page-title', 'Live CCTV Monitoring')

{{-- Blok @push('styles') tidak lagi diperlukan dan bisa dihapus --}}

@section('content')
  <div class="max-w-7xl mx-auto">
    <!-- Header Controls -->
    <x-card title="Live CCTV Monitoring" class="mb-6">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
        <div>
          <h2 class="text-xl font-semibold text-gray-900">{{ $layout->layout_name ?? 'Default Layout' }}</h2>
          <p class="mt-1 text-sm text-gray-600">Real-time monitoring dashboard</p>
        </div>

        {{-- REFACTOR: Mengubah div ini agar lebih responsif dan menempatkan kontrol dalam satu baris --}}
        <div class="flex flex-wrap items-center gap-4">
          <!-- Layout Selector -->
          <div class="flex items-center space-x-2">
            <label class="text-sm font-medium text-gray-700">Layout:</label>
            <select id="layout-selector"
              class="w-48 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
              @foreach ($availableLayouts as $availableLayout)
                <option value="{{ $availableLayout->id }}"
                  {{ $layout && $layout->id == $availableLayout->id ? 'selected' : '' }}>
                  {{ $availableLayout->layout_name }} ({{ $availableLayout->layout_type }})
                </option>
              @endforeach
            </select>
          </div>

          <!-- Stream Controls -->
          <div class="flex items-center space-x-2">
            <x-button variant="success" size="sm" onclick="startAllStreams()">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Start All
            </x-button>

            <x-button variant="danger" size="sm" onclick="stopAllStreams()">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Stop All
            </x-button>
          </div>
        </div>
      </div>
    </x-card>

    @if ($layout)
      <!-- CCTV Grid Container -->
      <x-card title="Live Streams" class="overflow-hidden">
        {{--
    REFACTOR:
    - Menggunakan 'grid' untuk mengaktifkan CSS Grid.
    - 'grid-cols-1' untuk tampilan mobile (default).
    - 'md:grid-cols-2' untuk 2 kolom pada layar medium dan lebih besar.
    - 'gap-4' untuk memberi jarak antar jendela CCTV.
    - Class .cctv-grid dan layout_type tidak lagi diperlukan di sini.
    --}}
        <div id="cctv-grid" class="grid grid-cols-1 md:grid-cols-2 gap-4">
          @foreach ($layout->positions as $position)
            {{-- REFACTOR: Class dari CSS kustom diubah menjadi utility classes Tailwind --}}
            <div class="flex flex-col min-h-0 bg-gray-100 border border-gray-300 rounded-lg overflow-hidden"
              data-position="{{ $position->position_number }}">
              <!-- Position Header -->
              <div class="shrink-0 bg-gray-800 text-white p-3 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <h4 class="font-semibold">Position {{ $position->position_number }}</h4>
                  <x-badge :variant="$position->is_enabled ? 'success' : 'danger'" size="sm">
                    {{ $position->is_enabled ? 'Online' : 'Offline' }}
                  </x-badge>
                </div>
                <div class="flex items-center space-x-1">
                  {{-- <x-button size="sm" variant="secondary" onclick="togglePosition({{ $position->position_number }})">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </x-button> --}}
                  <x-button size="sm" variant="secondary"
                    onclick="captureScreenshot({{ $position->position_number }})">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                  </x-button>
                </div>
              </div>

              <!-- Position Configuration -->
              <div class="shrink-0 p-3 bg-gray-50 border-b">
                <div class="grid grid-cols-2 gap-2">
                  <x-company-branch-select class="branch-select w-full" data-position="{{ $position->position_number }}"
                    onchange="updateBranchDevices({{ $position->position_number }}, this.value)" :value="$position->branch_id"
                    placeholder="Select Branch" />

                  <select class="device-select w-full px-2 py-1 text-sm border border-gray-300 rounded"
                    data-position="{{ $position->position_number }}"
                    onchange="updateStream({{ $position->position_number }}, this.value)">
                    <option value="">Select Device</option>
                    @if ($position->device)
                      <option value="{{ $position->device->device_id }}" selected>
                        {{ $position->device->device_name }}
                      </option>
                    @endif
                  </select>
                </div>
              </div>

              <!-- Stream Container -->
              {{-- REFACTOR: Menaikkan tinggi minimal stream container menjadi 400px --}}
              <div class="flex-1 min-h-[400px] relative bg-black">
                {{-- REFACTOR: Class dari CSS kustom diubah menjadi utility classes Tailwind --}}
                <div class="h-full flex items-center justify-center text-gray-400">
                  @if ($position->device && $position->is_enabled)
                    <div class="text-center">
                      <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                      </svg>
                      <p class="text-sm">{{ $position->device->device_name }}</p>
                      <p class="text-xs text-gray-500">{{ $position->branch->branch_name ?? 'No Branch' }}</p>
                    </div>
                  @else
                    <div class="text-center">
                      <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                      </svg>
                      <p class="text-sm">No Stream</p>
                      <p class="text-xs text-gray-500">Select device to start</p>
                    </div>
                  @endif
                </div>

                <!-- Stream Controls Overlay -->
                <div
                  class="stream-controls absolute bottom-2 right-2 flex space-x-1 opacity-0 hover:opacity-100 transition-opacity duration-300 ease-in-out">
                  <x-button size="sm" variant="danger" onclick="toggleRecording({{ $position->position_number }})">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                      <circle cx="12" cy="12" r="10" />
                    </svg>
                  </x-button>
                  <x-button size="sm" variant="primary"
                    onclick="captureScreenshot({{ $position->position_number }})">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    </svg>
                  </x-button>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </x-card>
    @else
      <!-- No Layout Available -->
      <x-card title="No Layout Available" class="text-center">
        <div class="py-12">
          <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
          </svg>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No Layout Available</h3>
          <p class="text-gray-500 mb-4">Please create a CCTV layout first to view live streams.</p>
          <x-button variant="primary" :href="route('cctv-layouts.create')">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Create Layout
          </x-button>
        </div>
      </x-card>
    @endif
  </div>
@endsection

@push('scripts')
  <script>
    // ============================================
    // GLOBAL STATE
    // ============================================
    let currentLayout = {{ $layout->id ?? 'null' }};
    let activeStreams = new Map();
    let positionStates = new Map(); // Track branch_id for each position

    // ============================================
    // UTILITY FUNCTIONS
    // ============================================

    /**
     * Show notification toast
     * @param {string} message - Message to display
     * @param {string} type - Type of notification (success, error, info)
     */
    function showNotification(message, type = 'success') {
      const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        info: 'bg-blue-500'
      };

      const notification = document.createElement('div');
      notification.className =
        `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-opacity duration-300`;
      notification.textContent = message;
      document.body.appendChild(notification);

      setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 300);
      }, 3000);
    }

    /**
     * Get CSRF token from meta tag
     * @returns {string} CSRF token
     */
    function getCsrfToken() {
      return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }

    /**
     * Get element by position number and selector class
     * @param {number} positionNumber
     * @param {string} selectorClass
     * @returns {HTMLElement}
     */
    function getPositionElement(positionNumber, selectorClass) {
      return document.querySelector(`[data-position="${positionNumber}"].${selectorClass}`);
    }

    // ============================================
    // API CALLS
    // ============================================

    /**
     * Fetch devices for a specific branch
     * @param {number} branchId
     * @returns {Promise<Array>}
     */
    async function fetchBranchDevices(branchId) {
      const response = await fetch(`/api/v1/cctv/branches/${branchId}/devices`);
      if (!response.ok) throw new Error('Failed to fetch devices');
      const result = await response.json();
      return result.data || result; // Handle both old and new format
    }

    /**
     * Fetch stream data for a specific device
     * @param {string} deviceId
     * @returns {Promise<Object>}
     */
    async function fetchStreamData(deviceId) {
      const response = await fetch(`/api/v1/cctv/streams/${deviceId}`);
      if (!response.ok) throw new Error('Failed to fetch stream data');
      const result = await response.json();
      return result.data || result; // Handle both old and new format
    }

    /**
     * Update position configuration in database
     * @param {number} positionNumber
     * @param {Object} data
     * @returns {Promise<Object>}
     */
    async function savePositionConfig(positionNumber, data) {
      const response = await fetch(`/api/v1/cctv/layouts/${currentLayout}/positions/${positionNumber}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': getCsrfToken()
        },
        body: JSON.stringify(data)
      });

      if (!response.ok) throw new Error('Failed to update position');
      const result = await response.json();
      return result; // Already has success flag
    }

    // ============================================
    // MAIN FUNCTIONS
    // ============================================

    /**
     * Layout switching handler
     */
    document.getElementById('layout-selector').addEventListener('change', function() {
      const layoutId = this.value;
      window.location.href = `{{ route('cctv-live-stream.index') }}?layout_id=${layoutId}`;
    });

    /**
     * Update device dropdown when branch changes
     * Only updates UI, no save to database
     */
    async function updateBranchDevices(positionNumber, branchId) {
      if (!branchId) {
        const deviceSelect = getPositionElement(positionNumber, 'device-select');
        deviceSelect.innerHTML = '<option value="">Select Device</option>';
        return;
      }

      try {
        // Fetch devices for selected branch
        const devices = await fetchBranchDevices(branchId);

        // Update device dropdown
        const deviceSelect = getPositionElement(positionNumber, 'device-select');
        deviceSelect.innerHTML = '<option value="">Select Device</option>';

        devices.forEach(device => {
          const option = document.createElement('option');
          option.value = device.device_id;
          option.textContent = device.device_name;
          deviceSelect.appendChild(option);
        });

        // Store branch_id for this position (for later use when device is selected)
        positionStates.set(positionNumber, {
          branch_id: branchId
        });

      } catch (error) {
        console.error('Error fetching devices:', error);
        showNotification('Failed to load devices', 'error');
      }
    }

    /**
     * Update stream and save configuration to database
     * This is called when device is selected from dropdown
     */
    async function updateStream(positionNumber, deviceId) {
      if (!deviceId) return;

      try {
        // Get branch_id from stored state
        const state = positionStates.get(positionNumber);
        const branchSelect = getPositionElement(positionNumber, 'branch-select');
        const branchId = state?.branch_id || branchSelect?.value;

        if (!branchId) {
          showNotification('Please select a branch first', 'error');
          return;
        }

        // Fetch stream data
        const streamData = await fetchStreamData(deviceId);

        // Auto-save position configuration to database
        const saveData = {
          branch_id: branchId,
          device_id: deviceId,
          is_enabled: true
        };

        const result = await savePositionConfig(positionNumber, saveData);

        if (result.success) {
          // Update stored state
          positionStates.set(positionNumber, saveData);

          // Start/update stream display
          startStream(positionNumber, streamData);

          showNotification(`Position ${positionNumber} updated successfully`, 'success');
        }

      } catch (error) {
        console.error('Error updating stream:', error);
        showNotification('Failed to update position', 'error');
      }
    }

    // ============================================
    // STREAM MANAGEMENT
    // ============================================

    /**
     * Start/display stream for a position
     * @param {number} positionNumber
     * @param {Object} streamData
     */
    function startStream(positionNumber, streamData) {
      const position = document.querySelector(`[data-position="${positionNumber}"]`);
      const streamContainer = position.querySelector('.flex-1.min-h-\\[400px\\]');

      if (!streamContainer) {
        console.error('Stream container not found for position:', positionNumber);
        return;
      }

      // Update stream display
      streamContainer.innerHTML = `
          <div class="h-full flex items-center justify-center text-gray-400">
              <div class="text-center">
                  <svg class="w-12 h-12 mx-auto mb-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                  </svg>
                  <p class="text-sm font-semibold text-white">${streamData.device_name}</p>
                  <p class="text-xs text-green-400 mt-1">● Stream Active</p>
                  <p class="text-xs text-gray-500 mt-2">URL: ${streamData.stream_url || 'N/A'}</p>
              </div>
          </div>
          <div class="stream-controls absolute bottom-2 right-2 flex space-x-1 opacity-0 hover:opacity-100 transition-opacity duration-300 ease-in-out">
              <button onclick="toggleRecording(${positionNumber})" class="px-2 py-2 text-xs bg-red-600 text-white rounded hover:bg-red-700 transition-colors">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                      <circle cx="12" cy="12" r="10" />
                  </svg>
              </button>
              <button onclick="captureScreenshot(${positionNumber})" class="px-2 py-2 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                  </svg>
              </button>
          </div>
      `;

      // Store stream data
      activeStreams.set(positionNumber, streamData);
    }

    /**
     * Stop stream for a position
     * @param {number} positionNumber
     */
    function stopStream(positionNumber) {
      const position = document.querySelector(`[data-position="${positionNumber}"]`);
      const streamContainer = position?.querySelector('.flex-1.min-h-\\[400px\\]');

      if (streamContainer) {
        streamContainer.innerHTML = `
              <div class="h-full flex items-center justify-center text-gray-400">
                  <div class="text-center">
                      <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                      </svg>
                      <p class="text-sm">Stream Stopped</p>
                  </div>
              </div>
          `;
      }

      activeStreams.delete(positionNumber);
    }

    // ============================================
    // STREAM ACTIONS
    // ============================================

    /**
     * Capture screenshot from stream
     * @param {number} positionNumber
     */
    async function captureScreenshot(positionNumber) {
      const streamData = activeStreams.get(positionNumber);

      if (!streamData) {
        showNotification('No active stream for this position', 'error');
        return;
      }

      try {
        const response = await fetch(`/api/v1/cctv/screenshots/${streamData.device_id}`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': getCsrfToken()
          }
        });

        const result = await response.json();

        if (result.success) {
          showNotification('Screenshot captured successfully!', 'success');
        } else {
          showNotification('Failed to capture screenshot', 'error');
        }
      } catch (error) {
        console.error('Error capturing screenshot:', error);
        showNotification('Error capturing screenshot', 'error');
      }
    }

    /**
     * Toggle recording for stream
     * @param {number} positionNumber
     */
    async function toggleRecording(positionNumber) {
      const streamData = activeStreams.get(positionNumber);

      if (!streamData) {
        showNotification('No active stream for this position', 'error');
        return;
      }

      try {
        const response = await fetch(`/api/v1/cctv/recordings/${streamData.device_id}`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken()
          },
          body: JSON.stringify({
            action: 'toggle'
          })
        });

        const result = await response.json();

        if (result.success) {
          showNotification(`Recording ${result.action}ed successfully!`, 'success');
        } else {
          showNotification('Failed to toggle recording', 'error');
        }
      } catch (error) {
        console.error('Error toggling recording:', error);
        showNotification('Error toggling recording', 'error');
      }
    }

    // ============================================
    // BULK ACTIONS
    // ============================================

    /**
     * Start all streams that have devices selected
     */
    function startAllStreams() {
      const deviceSelects = document.querySelectorAll('.device-select');
      let startedCount = 0;

      deviceSelects.forEach(select => {
        if (select.value) {
          const positionNumber = parseInt(select.dataset.position);
          updateStream(positionNumber, select.value);
          startedCount++;
        }
      });

      if (startedCount > 0) {
        showNotification(`Starting ${startedCount} stream(s)...`, 'info');
      } else {
        showNotification('No devices selected', 'error');
      }
    }

    /**
     * Stop all active streams
     */
    function stopAllStreams() {
      const activeCount = activeStreams.size;

      activeStreams.forEach((streamData, positionNumber) => {
        stopStream(positionNumber);
      });

      if (activeCount > 0) {
        showNotification(`Stopped ${activeCount} stream(s)`, 'info');
      } else {
        showNotification('No active streams', 'error');
      }
    }

    // ============================================
    // INITIALIZATION
    // ============================================

    /**
     * Initialize position states from existing configuration
     * This runs when page loads to track existing branch/device selections
     */
    function initializePositionStates() {
      document.querySelectorAll('.branch-select').forEach(branchSelect => {
        const positionNumber = parseInt(branchSelect.dataset.position);
        const branchId = branchSelect.value;
        const deviceSelect = getPositionElement(positionNumber, 'device-select');
        const deviceId = deviceSelect?.value;

        if (branchId && deviceId) {
          positionStates.set(positionNumber, {
            branch_id: branchId,
            device_id: deviceId
          });
        }
      });

      console.log('Initialized position states:', positionStates);
    }

    // Run initialization when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
      initializePositionStates();
    });
  </script>
@endpush
