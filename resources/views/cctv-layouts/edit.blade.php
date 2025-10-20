@extends('layouts.app')

@section('title', 'Edit CCTV Layout')

@section('content')
  <div class="max-w-5xl mx-auto">
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-900">Edit CCTV Layout</h1>
      <p class="mt-2 text-gray-600">Modify existing CCTV grid layout configuration</p>
    </div>

    <x-card>
      <form action="{{ route('cctv-layouts.update', $layout) }}" method="POST" x-data="layoutForm()">
        @csrf
        @method('PUT')

        <x-form-input label="Layout Name" name="layout_name" :value="$layout->layout_name" :required="true"
          placeholder="e.g., Main Monitoring Layout" />

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Layout Type <span
              class="text-red-500">*</span></label>
          <div class="grid grid-cols-3 gap-4">
            <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500"
              :class="{ 'border-blue-500 bg-blue-50': layoutType === '4-window' }">
              <input type="radio" name="layout_type" value="4-window" x-model="layoutType" @change="updatePositions"
                class="mr-3" {{ $layout->layout_type === '4-window' ? 'checked' : '' }} required>
              <div>
                <p class="font-semibold">4-Window</p>
                <p class="text-xs text-gray-500">2x2 Grid</p>
              </div>
            </label>
            <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500"
              :class="{ 'border-blue-500 bg-blue-50': layoutType === '6-window' }">
              <input type="radio" name="layout_type" value="6-window" x-model="layoutType" @change="updatePositions"
                class="mr-3" {{ $layout->layout_type === '6-window' ? 'checked' : '' }}>
              <div>
                <p class="font-semibold">6-Window</p>
                <p class="text-xs text-gray-500">2x3 Grid</p>
              </div>
            </label>
            <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500"
              :class="{ 'border-blue-500 bg-blue-50': layoutType === '8-window' }">
              <input type="radio" name="layout_type" value="8-window" x-model="layoutType" @change="updatePositions"
                class="mr-3" {{ $layout->layout_type === '8-window' ? 'checked' : '' }}>
              <div>
                <p class="font-semibold">8-Window</p>
                <p class="text-xs text-gray-500">2x4 Grid</p>
              </div>
            </label>
          </div>
        </div>

        <x-form-input label="Description" name="description" type="textarea" :value="$layout->description" />

        <div class="mb-4">
          <x-checkbox name="is_default" label="Set as default layout" value="1" :checked="$layout->is_default" />
        </div>

        <div class="mb-4">
          <x-checkbox name="is_active" label="Set layout as active" value="1" :checked="$layout->is_active" />
        </div>

        <!-- Position Configuration -->
        <div class="mt-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Position Configuration</h3>
          <div class="space-y-4">
            @foreach ($layout->positions as $index => $position)
              <div class="p-4 border border-gray-200 rounded-lg">
                <div class="flex justify-between items-start mb-3">
                  <h4 class="font-semibold text-gray-700">Position {{ $index + 1 }}</h4>
                  <label class="flex items-center text-sm">
                    <input type="checkbox" name="positions[{{ $index }}][is_enabled]" value="1"
                      {{ $position->is_enabled ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 mr-2">
                    <span class="text-gray-700">Enabled</span>
                  </label>
                </div>
                <input type="hidden" name="positions[{{ $index }}][position_number]"
                  value="{{ $position->position_number }}">
                <input type="hidden" name="positions[{{ $index }}][id]" value="{{ $position->id }}">

                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                    <select name="positions[{{ $index }}][branch_id]" required
                      class="w-full px-4 py-2 border rounded-lg">
                      <option value="">Select Branch</option>
                      @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" {{ $position->branch_id == $branch->id ? 'selected' : '' }}>
                          {{ $branch->branch_name }}
                        </option>
                      @endforeach
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Device</label>
                    <select name="positions[{{ $index }}][device_id]" required
                      class="w-full px-4 py-2 border rounded-lg">
                      <option value="">Select Device</option>
                      @foreach ($branches as $branch)
                        @foreach ($branch->devices as $device)
                          <option value="{{ $device->device_id }}"
                            {{ $position->device_id == $device->device_id ? 'selected' : '' }}>
                            {{ $device->device_name }} ({{ $branch->branch_name }})
                          </option>
                        @endforeach
                      @endforeach
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Position Name</label>
                    <input type="text" name="positions[{{ $index }}][position_name]"
                      value="{{ $position->position_name }}" placeholder="e.g., Main Entrance"
                      class="w-full px-4 py-2 border rounded-lg">
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quality</label>
                    <select name="positions[{{ $index }}][quality]" class="w-full px-4 py-2 border rounded-lg">
                      <option value="high" {{ $position->quality === 'high' ? 'selected' : '' }}>High</option>
                      <option value="medium" {{ $position->quality === 'medium' ? 'selected' : '' }}>Medium</option>
                      <option value="low" {{ $position->quality === 'low' ? 'selected' : '' }}>Low</option>
                    </select>
                  </div>
                  <div>
                    <label class="flex items-center">
                      <input type="checkbox" name="positions[{{ $index }}][auto_switch]" value="1"
                        {{ $position->auto_switch ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 mr-2">
                      <span class="text-sm text-gray-700">Auto-switch</span>
                    </label>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Switch Interval (seconds)</label>
                    <input type="number" name="positions[{{ $index }}][switch_interval]"
                      value="{{ $position->switch_interval ?? 10 }}" min="5" max="60"
                      class="w-full px-4 py-2 border rounded-lg">
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>

        <div class="flex justify-end space-x-3 mt-6">
          <x-button variant="secondary" :href="route('cctv-layouts.show', $layout)">
            Cancel
          </x-button>
          <x-button type="submit" variant="primary">
            Update Layout
          </x-button>
        </div>
      </form>
    </x-card>
  </div>

  <script>
    function layoutForm() {
      return {
        layoutType: '{{ $layout->layout_type }}',
        updatePositions() {
          // Note: When changing layout type in edit mode,
          // you may want to warn the user about losing position data
          if (!confirm('Changing layout type may require reconfiguring positions. Continue?')) {
            this.layoutType = '{{ $layout->layout_type }}';
            return;
          }
        }
      }
    }
  </script>
@endsection
