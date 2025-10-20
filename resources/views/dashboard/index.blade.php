@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
  <div class="max-w-7xl mx-auto">
    <!-- Welcome Banner -->
    <div class="mb-8">
      <div
        class="relative overflow-hidden bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-700 rounded-2xl shadow-2xl">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full -mr-32 -mt-32"></div>
        <div class="absolute bottom-0 left-0 w-40 h-40 bg-white opacity-5 rounded-full -ml-20 -mb-20"></div>

        <div class="relative p-8">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-3xl font-bold text-white mb-2">Welcome back, {{ auth()->user()->name }}! 👋</h1>
              <p class="text-blue-100">Here's what's happening with your CCTV system today.</p>
            </div>
            <div class="hidden md:block">
              <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-xl p-4">
                <p class="text-100 text-sm">{{ now()->format('l, F j, Y') }}</p>
                <p class="text text-2xl font-bold">{{ now()->format('H:i') }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <x-stat-card title="Total Groups" :value="$totalGroups" color="blue" :icon="'<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4\'/>'" />

      <x-stat-card title="Total Branches" :value="$totalBranches" color="green" :icon="'<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4\'/>'" />

      <x-stat-card title="Total Devices" :value="$totalDevices" color="purple" :icon="'<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z\'/>'" />

      <x-stat-card title="Today's Detections" :value="$todayDetections" color="orange" :icon="'<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M15 12a3 3 0 11-6 0 3 3 0 016 0z\'/><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z\'/>'"
        :trend="$todayDetectionTrend" :trendUp="$todayDetectionTrendUp" />
    </div>

    <!-- Re-ID Statistics & Detection Trend -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <x-card title="Person Detection Stats (Today)">
        <div class="space-y-4">
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Total Records:</span>
            <span class="text-2xl font-bold text-gray-900">{{ $reIdStats['total_records'] }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Active:</span>
            <span class="text-xl font-semibold text-green-600">{{ $reIdStats['active_records'] }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Inactive:</span>
            <span class="text-xl font-semibold text-red-600">{{ $reIdStats['inactive_records'] }}</span>
          </div>
          <div class="flex justify-between items-center pt-4 border-t border-gray-200">
            <span class="text-gray-600">Unique Persons:</span>
            <span class="text-xl font-semibold text-blue-600">{{ $reIdStats['unique_persons'] }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Total Detections:</span>
            <span class="text-xl font-semibold text-purple-600">{{ $reIdStats['total_detections'] }}</span>
          </div>
        </div>
      </x-card>

      <x-detection-trend-chart :data="$detectionTrend" title="Detection Trend (Last 7 Days)" />
    </div>

    <!-- Recent Detections & Events -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Recent Detections -->
      <x-card title="Recent Detections" :padding="false">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Re-ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Device</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              @forelse($recentDetections as $detection)
                <tr class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">
                      {{ Str::limit($detection->reIdMaster->re_id ?? 'N/A', 20) }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ $detection->branch->branch_name ?? 'N/A' }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ $detection->device->device_name ?? 'N/A' }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $detection->detection_timestamp->diffForHumans() }}
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="px-6 py-8 text-center text-gray-400">
                    No recent detections
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if ($hasRecentDetections)
          <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
            <a href="{{ route('re-id-masters.index') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
              View all detections →
            </a>
          </div>
        @endif
      </x-card>

      <!-- Recent Events -->
      <x-card title="Recent Events" :padding="false">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Device</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              @forelse($recentEvents as $event)
                <tr class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <x-badge :variant="$event->event_type === 'detection' ? 'success' : ($event->event_type === 'alert' ? 'danger' : ($event->event_type === 'motion' ? 'warning' : 'gray'))">
                      {{ ucfirst($event->event_type) }}
                    </x-badge>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ $event->branch->branch_name ?? 'N/A' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ $event->device->device_name ?? 'N/A' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $event->event_timestamp->diffForHumans() }}
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="px-6 py-8 text-center text-gray-400">
                    No recent events
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if ($hasRecentEvents)
          <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
            <a href="{{ route('event-logs.index') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
              View all events →
            </a>
          </div>
        @endif
      </x-card>
    </div>

    <!-- Quick Actions -->
    <div class="mt-8">
      <x-card title="Quick Actions">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <a href="{{ route('company-groups.create') }}"
            class="flex flex-col items-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors">
            <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            <span class="text-sm font-medium text-gray-700">Add Group</span>
          </a>

          <a href="{{ route('company-branches.create') }}"
            class="flex flex-col items-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-green-500 hover:bg-green-50 transition-colors">
            <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            <span class="text-sm font-medium text-gray-700">Add Branch</span>
          </a>

          <a href="{{ route('device-masters.create') }}"
            class="flex flex-col items-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition-colors">
            <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span class="text-sm font-medium text-gray-700">Add Device</span>
          </a>

          <a href="{{ route('cctv-layouts.create') }}"
            class="flex flex-col items-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-orange-500 hover:bg-orange-50 transition-colors">
            <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
            </svg>
            <span class="text-sm font-medium text-gray-700">Add Layout</span>
          </a>
        </div>
      </x-card>
    </div>
  </div>
@endsection
