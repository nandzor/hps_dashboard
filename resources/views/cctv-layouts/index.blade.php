@extends('layouts.app')

@section('title', 'CCTV Layouts')

@section('content')
  <div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">CCTV Layouts</h1>
        <p class="mt-2 text-gray-600">Manage CCTV grid layouts (4, 6, 8 windows)</p>
      </div>
      <x-button variant="primary" :href="route('cctv-layouts.create')">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Create Layout
      </x-button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-3 gap-6 mb-6">
      <x-stat-card title="Total Layouts" :value="$statistics['total_layouts']" icon="chart-bar" color="blue" />
      <x-stat-card title="Active Layouts" :value="$statistics['active_layouts']" icon="eye" color="green" />
      <x-stat-card title="Default Layout" :value="$statistics['by_type']['4-window'] ?? 0" icon="building" color="purple" />
    </div>

    <!-- Layouts Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      @forelse($layouts->items() as $layout)
        <x-card>
          <div class="flex justify-between items-start mb-4">
            <div>
              <h3 class="text-lg font-semibold text-gray-900">{{ $layout->layout_name }}</h3>
              <p class="text-sm text-gray-500">{{ ucfirst($layout->layout_type) }}</p>
            </div>
            @if ($layout->is_default)
              <x-badge variant="info" size="sm">Default</x-badge>
            @endif
          </div>

          <div class="space-y-2 mb-4">
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Total Positions:</span>
              <span class="font-semibold">{{ $layout->total_positions }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Status:</span>
              <x-badge :variant="$layout->is_active ? 'success' : 'danger'" size="sm">
                {{ $layout->is_active ? 'Active' : 'Inactive' }}
              </x-badge>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Created by:</span>
              <span class="font-semibold">{{ $layout->creator->name ?? 'System' }}</span>
            </div>
          </div>

          <div class="flex space-x-2">
            <x-button variant="primary" :href="route('cctv-layouts.show', $layout)" size="sm" class="flex-1">
              View
            </x-button>
            <x-button variant="warning" :href="route('cctv-layouts.edit', $layout)" size="sm" class="flex-1">
              Edit
            </x-button>
          </div>
        </x-card>
      @empty
        <div class="col-span-3">
          <x-card>
            <div class="text-center py-8">
              <p class="text-gray-400">No layouts found. <a href="{{ route('cctv-layouts.create') }}"
                  class="text-blue-600 hover:text-blue-800">Create one now</a></p>
            </div>
          </x-card>
        </div>
      @endforelse
    </div>

    @if ($layouts->hasPages())
      <div class="mt-6">{{ $layouts->links() }}</div>
    @endif
  </div>
@endsection
