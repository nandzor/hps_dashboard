@extends('layouts.app')

@section('title', 'WhatsApp Settings Details')
@section('page-title', 'WhatsApp Settings Details')

@section('content')
  <div class="max-w-4xl">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center space-x-4">
        <div class="flex-shrink-0 h-12 w-12">
          <div
            class="h-12 w-12 rounded-full bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center shadow-lg">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
            </svg>
          </div>
        </div>
        <div>
          <h1 class="text-2xl font-bold text-gray-900">{{ $whatsappSettings->name }}</h1>
          <p class="text-sm text-gray-500">{{ $whatsappSettings->description ?? 'No description' }}</p>
        </div>
      </div>

      <div class="flex items-center space-x-3">
        @if ($whatsappSettings->is_default)
          <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Default Settings
          </span>
        @endif

        <x-badge :variant="$whatsappSettings->is_active ? 'success' : 'danger'">
          {{ $whatsappSettings->is_active ? 'Active' : 'Inactive' }}
        </x-badge>

        <x-button variant="secondary" :href="route('whatsapp-settings.edit', $whatsappSettings)">
          <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
          </svg>
          Edit Settings
        </x-button>
      </div>
    </div>

    <!-- Settings Details -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Phone Numbers -->
      <x-card title="Phone Numbers">
        <div class="space-y-3">
          @forelse($whatsappSettings->phone_numbers as $number)
            <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg border border-green-200">
              <div class="flex items-center">
                <div class="flex-shrink-0 h-8 w-8">
                  <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                  </div>
                </div>
                <div class="ml-3">
                  <p class="text-sm font-medium text-gray-900">{{ $number }}</p>
                </div>
              </div>
              <span
                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                WhatsApp
              </span>
            </div>
          @empty
            <div class="text-center py-8">
              <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
              </svg>
              <p class="text-gray-500 text-sm">No phone numbers configured</p>
            </div>
          @endforelse
        </div>
      </x-card>

      <!-- Message Template -->
      <x-card title="Message Template">
        <div class="bg-gray-50 rounded-lg p-4 border">
          <pre class="text-sm text-gray-800 whitespace-pre-wrap font-mono">{{ $whatsappSettings->message_template }}</pre>
        </div>

        <!-- Template Preview -->
        <div class="mt-4">
          <h4 class="text-sm font-medium text-gray-700 mb-2">Template Preview:</h4>
          <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-4 border border-blue-200">
            <div class="flex items-start space-x-3">
              <div class="flex-shrink-0 h-6 w-6">
                <div class="h-6 w-6 rounded-full bg-blue-100 flex items-center justify-center">
                  <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                  </svg>
                </div>
              </div>
              <div class="flex-1">
                <p class="text-sm text-blue-800 font-medium mb-1">Sample Message:</p>
                <p class="text-sm text-blue-700 bg-white rounded p-3 border border-blue-100">
                  {{ $whatsappSettings->formatMessage([
                      'branch_name' => 'Sample Branch',
                      'device_name' => 'Camera 01',
                      'detection_time' => now()->format('Y-m-d H:i:s'),
                      'person_count' => '3',
                      'confidence' => '95%',
                      'location' => 'Main Entrance',
                  ]) }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Available Variables -->
        <div class="mt-4">
          <h4 class="text-sm font-medium text-gray-700 mb-2">Available Variables:</h4>
          <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
            <div class="flex items-center space-x-2 p-2 bg-gray-50 rounded border">
              <code class="text-xs font-mono text-gray-700 bg-gray-200 px-1 py-0.5 rounded">{branch_name}</code>
              <span class="text-xs text-gray-600">Branch name</span>
            </div>
            <div class="flex items-center space-x-2 p-2 bg-gray-50 rounded border">
              <code class="text-xs font-mono text-gray-700 bg-gray-200 px-1 py-0.5 rounded">{device_name}</code>
              <span class="text-xs text-gray-600">Device name</span>
            </div>
            <div class="flex items-center space-x-2 p-2 bg-gray-50 rounded border">
              <code class="text-xs font-mono text-gray-700 bg-gray-200 px-1 py-0.5 rounded">{detection_time}</code>
              <span class="text-xs text-gray-600">Detection time</span>
            </div>
            <div class="flex items-center space-x-2 p-2 bg-gray-50 rounded border">
              <code class="text-xs font-mono text-gray-700 bg-gray-200 px-1 py-0.5 rounded">{person_count}</code>
              <span class="text-xs text-gray-600">Person count</span>
            </div>
            <div class="flex items-center space-x-2 p-2 bg-gray-50 rounded border">
              <code class="text-xs font-mono text-gray-700 bg-gray-200 px-1 py-0.5 rounded">{confidence}</code>
              <span class="text-xs text-gray-600">Confidence</span>
            </div>
            <div class="flex items-center space-x-2 p-2 bg-gray-50 rounded border">
              <code class="text-xs font-mono text-gray-700 bg-gray-200 px-1 py-0.5 rounded">{location}</code>
              <span class="text-xs text-gray-600">Location</span>
            </div>
          </div>
        </div>
      </x-card>
    </div>

    <!-- Settings Information -->
    <x-card title="Settings Information">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <h4 class="text-sm font-medium text-gray-700 mb-2">Status</h4>
          <x-badge :variant="$whatsappSettings->is_active ? 'success' : 'danger'">
            {{ $whatsappSettings->is_active ? 'Active' : 'Inactive' }}
          </x-badge>
        </div>

        <div>
          <h4 class="text-sm font-medium text-gray-700 mb-2">Default Settings</h4>
          <x-badge :variant="$whatsappSettings->is_default ? 'warning' : 'secondary'">
            {{ $whatsappSettings->is_default ? 'Yes' : 'No' }}
          </x-badge>
        </div>

        <div>
          <h4 class="text-sm font-medium text-gray-700 mb-2">Created</h4>
          <p class="text-sm text-gray-900">{{ $whatsappSettings->created_at->format('M d, Y H:i') }}</p>
        </div>

        <div>
          <h4 class="text-sm font-medium text-gray-700 mb-2">Last Updated</h4>
          <p class="text-sm text-gray-900">{{ $whatsappSettings->updated_at->format('M d, Y H:i') }}</p>
        </div>
      </div>
    </x-card>

    <!-- Action Buttons -->
    <div class="flex items-center justify-between pt-6">
      <x-button variant="secondary" :href="route('whatsapp-settings.index')">
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Back to List
      </x-button>

      <div class="flex items-center space-x-3">
        @if (!$whatsappSettings->is_default)
          <x-button variant="warning" onclick="setAsDefault({{ $whatsappSettings->id }})">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Set as Default
          </x-button>
        @endif

        <x-button variant="primary" :href="route('whatsapp-settings.edit', $whatsappSettings)">
          <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
          </svg>
          Edit Settings
        </x-button>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    function setAsDefault(settingId) {
      if (confirm('Are you sure you want to set this as the default WhatsApp settings?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/whatsapp-settings/${settingId}/set-default`;

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
      }
    }

    window.setAsDefault = setAsDefault;
  </script>
@endpush
