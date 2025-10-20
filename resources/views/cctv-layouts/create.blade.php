@extends('layouts.app')

@section('title', 'Create CCTV Layout')

@section('content')
  <div class="max-w-5xl mx-auto">
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-900">Create CCTV Layout</h1>
      <p class="mt-2 text-gray-600">Configure a new CCTV grid layout</p>
    </div>

    <x-card>
      <form action="{{ route('cctv-layouts.store') }}" method="POST" x-data="layoutForm()">
        @csrf

        <x-form-input label="Layout Name" name="layout_name" :required="true" placeholder="e.g., Main Monitoring Layout" />

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Layout Type <span
              class="text-red-500">*</span></label>
          <div class="grid grid-cols-3 gap-4">
            <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500"
              :class="{ 'border-blue-500 bg-blue-50': layoutType === '4-window' }">
              <input type="radio" name="layout_type" value="4-window" x-model="layoutType" @change="updatePositions"
                class="mr-3" required>
              <div>
                <p class="font-semibold">4-Window</p>
                <p class="text-xs text-gray-500">2x2 Grid</p>
              </div>
            </label>
            <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500"
              :class="{ 'border-blue-500 bg-blue-50': layoutType === '6-window' }">
              <input type="radio" name="layout_type" value="6-window" x-model="layoutType" @change="updatePositions"
                class="mr-3">
              <div>
                <p class="font-semibold">6-Window</p>
                <p class="text-xs text-gray-500">2x3 Grid</p>
              </div>
            </label>
            <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500"
              :class="{ 'border-blue-500 bg-blue-50': layoutType === '8-window' }">
              <input type="radio" name="layout_type" value="8-window" x-model="layoutType" @change="updatePositions"
                class="mr-3">
              <div>
                <p class="font-semibold">8-Window</p>
                <p class="text-xs text-gray-500">2x4 Grid</p>
              </div>
            </label>
          </div>
        </div>

        <x-form-input label="Description" name="description" type="textarea" />

        <div class="mb-4">
          <x-checkbox name="is_default" label="Set as default layout" value="1" />
        </div>

        <!-- Position Configuration -->
        <div class="mt-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Position Configuration</h3>
          <div class="space-y-4">
            <template x-for="(position, index) in positions" :key="index">
              <div class="p-4 border border-gray-200 rounded-lg">
                <h4 class="font-semibold text-gray-700 mb-3">Position <span x-text="index + 1"></span></h4>
                <input type="hidden" :name="'positions[' + index + '][position_number]'" :value="index + 1">

                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                    <select :name="'positions[' + index + '][branch_id]'" required
                      class="w-full px-4 py-2 border rounded-lg">
                      <option value="">Select Branch</option>
                      @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->branch_name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Device</label>
                    <select :name="'positions[' + index + '][device_id]'" required
                      class="w-full px-4 py-2 border rounded-lg">
                      <option value="">Select Device</option>
                      @foreach ($branches as $branch)
                        @foreach ($branch->devices as $device)
                          <option value="{{ $device->device_id }}">{{ $device->device_name }}</option>
                        @endforeach
                      @endforeach
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Position Name</label>
                    <input type="text" :name="'positions[' + index + '][position_name]'"
                      placeholder="e.g., Main Entrance" class="w-full px-4 py-2 border rounded-lg">
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quality</label>
                    <select :name="'positions[' + index + '][quality]'" class="w-full px-4 py-2 border rounded-lg">
                      <option value="high">High</option>
                      <option value="medium">Medium</option>
                      <option value="low">Low</option>
                    </select>
                  </div>
                </div>
              </div>
            </template>
          </div>
        </div>

        <div class="flex justify-end space-x-3 mt-6">
          <x-button variant="secondary" :href="route('cctv-layouts.index')">
            Cancel
          </x-button>
          <x-button type="submit" variant="primary">
            Create Layout
          </x-button>
        </div>
      </form>
    </x-card>
  </div>

  <script>
    function layoutForm() {
      return {
        layoutType: '4-window',
        positions: [{}, {}, {}, {} // Default 4 positions
        ],
        updatePositions() {
          const counts = {
            '4-window': 4,
            '6-window': 6,
            '8-window': 8
          };
          const count = counts[this.layoutType] || 4;
          this.positions = Array.from({
            length: count
          }, () => ({}));
        }
      }
    }
  </script>
@endsection
