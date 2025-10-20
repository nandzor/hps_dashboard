@extends('layouts.app')

@section('title', 'Layout Details')

@section('content')
  <div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">{{ $layout->layout_name }}</h1>
        <p class="mt-2 text-gray-600">{{ ucfirst($layout->layout_type) }} - {{ $layout->total_positions }} positions</p>
      </div>
      <div class="flex space-x-3">
        <x-button variant="warning" :href="route('cctv-layouts.edit', $layout)">
          Edit
        </x-button>
        @if (!$layout->is_default)
          <x-button variant="danger" @click="confirmDelete({{ $layout->id }})">
            Delete
          </x-button>
        @endif
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
      <x-card title="Layout Info">
        <dl class="space-y-3">
          <div>
            <dt class="text-sm font-medium text-gray-500">Type</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($layout->layout_type) }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-500">Positions</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ $layout->total_positions }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-500">Status</dt>
            <dd class="mt-1">
              <x-badge :variant="$layout->is_active ? 'success' : 'danger'" size="sm">
                {{ $layout->is_active ? 'Active' : 'Inactive' }}
              </x-badge>
            </dd>
          </div>
          @if ($layout->is_default)
            <div>
              <dt class="text-sm font-medium text-gray-500">Default</dt>
              <dd class="mt-1">
                <x-badge variant="info" size="sm">Yes</x-badge>
              </dd>
            </div>
          @endif
        </dl>
      </x-card>

      <div class="lg:col-span-3">
        <x-card title="Position Configuration">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach ($layout->positions as $position)
              <div class="p-4 border border-gray-200 rounded-lg">
                <div class="flex justify-between items-start mb-2">
                  <h4 class="font-semibold text-gray-900">Position {{ $position->position_number }}</h4>
                  <x-badge :variant="$position->is_enabled ? 'success' : 'secondary'" size="sm">
                    {{ $position->is_enabled ? 'Enabled' : 'Disabled' }}
                  </x-badge>
                </div>
                <p class="text-sm text-gray-600 mb-2">{{ $position->position_name }}</p>
                <div class="space-y-1 text-sm">
                  <p><span class="text-gray-500">Branch:</span> {{ $position->branch->branch_name ?? 'N/A' }}</p>
                  <p><span class="text-gray-500">Device:</span> {{ $position->device->device_name ?? 'N/A' }}</p>
                  <p><span class="text-gray-500">Quality:</span> {{ ucfirst($position->quality) }}</p>
                  @if ($position->auto_switch)
                    <p><span class="text-gray-500">Auto-switch:</span> {{ $position->switch_interval }}s</p>
                  @endif
                </div>
              </div>
            @endforeach
          </div>
        </x-card>
      </div>
    </div>

    <div class="mt-6">
      <x-button variant="secondary" :href="route('cctv-layouts.index')">
        ← Back to Layouts
      </x-button>
    </div>
  </div>

  <x-confirm-modal id="confirm-delete" title="Delete Layout" message="Delete this CCTV layout?" />
  <script>
    function confirmDelete(id) {
      window.dispatchEvent(new CustomEvent('open-modal-confirm-delete'));
    }
    window.addEventListener('confirm-confirm-delete', function() {
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = `/cctv-layouts/{{ $layout->id }}`;
      form.innerHTML = `@csrf @method('DELETE')`;
      document.body.appendChild(form);
      form.submit();
    });
  </script>
@endsection
