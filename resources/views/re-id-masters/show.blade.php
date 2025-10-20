@extends('layouts.app')

@section('title', 'Person Details')

@section('content')
  <div class="max-w-7xl mx-auto">
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-900">{{ $person->person_name ?: 'Unknown Person' }}</h1>
      <p class="mt-2 text-gray-600 font-mono">Re-ID: {{ $person->re_id }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2">
        <x-card title="Person Information">
          <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6">
            <div>
              <dt class="text-sm font-medium text-gray-500">Re-ID</dt>
              <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $person->re_id }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Person Name</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ $person->person_name ?: 'Unknown' }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Detection Date</dt>
              <dd class="mt-1 text-sm text-gray-900">
                {{ \Carbon\Carbon::parse($person->detection_date)->format('l, F d, Y') }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Detection Time</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($person->detection_time)->format('H:i:s') }}
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">First Detected</dt>
              <dd class="mt-1 text-sm text-gray-900">
                {{ $person->first_detected_at ? \Carbon\Carbon::parse($person->first_detected_at)->format('H:i:s') : 'N/A' }}
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Last Detected</dt>
              <dd class="mt-1 text-sm text-gray-900">
                {{ $person->last_detected_at ? \Carbon\Carbon::parse($person->last_detected_at)->format('H:i:s') : 'N/A' }}
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Status</dt>
              <dd class="mt-1">
                <span
                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $person->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                  {{ ucfirst($person->status) }}
                </span>
              </dd>
            </div>
            @if ($person->appearance_features)
              <div class="md:col-span-2">
                <dt class="text-sm font-medium text-gray-500">Appearance Features</dt>
                <dd class="mt-1 text-sm text-gray-900">
                  <pre class="bg-gray-100 p-3 rounded text-xs">{{ json_encode($person->appearance_features, JSON_PRETTY_PRINT) }}</pre>
                </dd>
              </div>
            @endif
          </dl>
        </x-card>

        <!-- Detection History -->
        <div class="mt-6">
          <x-card title="Detection History ({{ $date }})" :padding="false">
            <x-client-pagination 
              :items="$person->branchDetections->map(function($detection) {
                return [
                  'id' => $detection->id,
                  'detection_timestamp' => $detection->detection_timestamp,
                  'branch_name' => $detection->branch->branch_name ?? null,
                  'device_name' => $detection->device->device_name ?? null,
                  'detection_data' => $detection->detection_data
                ];
              })"
              :per-page-options="[5, 10, 20, 50]"
              :default-per-page="10"
              :max-visible-pages="5"
              item-name="detections"
              empty-message="No detections found for this date"
              storage-key="detection_history_per_page"
            >
              <x-detection-history-table 
                :detections="$person->branchDetections"
                :show-json-data="true"
                :show-branch-name="true"
                :show-device-name="true"
                :show-timestamp="true"
              />
            </x-client-pagination>
          </x-card>
        </div>
      </div>

      <div>
        <x-card title="Detection Statistics">
          <div class="space-y-4">
            <div class="flex justify-between items-center pb-4 border-b">
              <span class="text-gray-600">Branches Detected</span>
              <span class="text-2xl font-bold text-blue-600">{{ $person->total_detection_branch_count }}</span>
            </div>
            <div class="flex justify-between items-center pb-4 border-b">
              <span class="text-gray-600">Total Actual Count</span>
              <span class="text-xl font-semibold text-purple-600">{{ $person->total_actual_count }}</span>
            </div>
          </div>
        </x-card>

        <!-- Branch Detection Counts -->
        <x-card title="Branch Detection Summary" class="mt-6">
          @if($branchDetectionCounts->count() > 0)
            <div class="space-y-3">
              @foreach($branchDetectionCounts as $branch)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                  <div class="flex-1">
                    <div class="font-medium text-gray-900">{{ $branch->branch_name }}</div>
                    <div class="text-sm text-gray-500">{{ $branch->branch_code }}</div>
                  </div>
                  <div class="flex items-center space-x-4 text-sm">
                    <div class="text-center">
                      <div class="text-xs text-gray-500">Total Count</div>
                      <x-badge color="blue" size="sm">{{ $branch->total_detected_count }}</x-badge>
                    </div>
                    <div class="text-center">
                      <div class="text-xs text-gray-500">First</div>
                      <div class="text-gray-600">{{ \Carbon\Carbon::parse($branch->first_detection)->format('H:i') }}</div>
                    </div>
                    <div class="text-center">
                      <div class="text-xs text-gray-500">Last</div>
                      <div class="text-gray-600">{{ \Carbon\Carbon::parse($branch->last_detection)->format('H:i') }}</div>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          @else
            <div class="text-center py-8 text-gray-500">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
              <p class="mt-2 text-sm">No branch detections found for this date</p>
            </div>
          @endif
        </x-card>
      </div>
    </div>

    <!-- All Detection Dates -->
    @if ($hasMultipleDetections)
      <div class="mt-8">
        <x-card title="All Detection Dates">
          <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach ($allDetections as $det)
              <a href="{{ route('re-id-masters.show', ['reId' => $det->re_id, 'date' => $det->detection_date]) }}"
                class="p-4 border-2 rounded-lg text-center {{ $det->detection_date === $date ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300' }}">
                <p class="text-sm font-medium text-gray-900">
                  {{ \Carbon\Carbon::parse($det->detection_date)->format('M d, Y') }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $det->total_actual_count }} detections</p>
              </a>
            @endforeach
          </div>
        </x-card>
      </div>
    @endif

    <div class="mt-6">
      <a href="{{ route('re-id-masters.index') }}" class="text-blue-600 hover:text-blue-800">← Back to Person
        Tracking</a>
    </div>
  </div>
@endsection

@push('scripts')
<script src="{{ asset('js/client-pagination.js') }}"></script>
@endpush
