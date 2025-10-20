@extends('layouts.app')

@section('title', 'Branch Event Setting Details')
@section('page-title', 'Branch Event Setting Details')

@section('content')
  <div class="max-w-4xl">
    <!-- Header -->
    <div class="mb-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Event Setting Details</h1>
          <p class="text-sm text-gray-500 mt-1">{{ $eventSetting->branch->branch_name ?? 'N/A' }} -
            {{ $eventSetting->device->device_name ?? 'N/A' }}</p>
        </div>
        <div class="flex items-center space-x-3">
          <x-badge :variant="$eventSetting->is_active ? 'success' : 'danger'">
            {{ $eventSetting->is_active ? 'Active' : 'Inactive' }}
          </x-badge>
          <x-button variant="primary" size="sm" :href="route('branch-event-settings.edit', $eventSetting)">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit Setting
          </x-button>
        </div>
      </div>
    </div>

    <!-- Basic Information -->
    <x-card title="Basic Information" class="mb-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
          <div class="flex items-center">
            <div class="flex-shrink-0 h-8 w-8">
              <div
                class="h-8 w-8 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-gray-900">{{ $eventSetting->branch->branch_name ?? 'N/A' }}</p>
              <p class="text-sm text-gray-500">{{ $eventSetting->branch->branch_code ?? 'N/A' }}</p>
            </div>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Device</label>
          <div class="flex items-center">
            <div class="flex-shrink-0 h-8 w-8">
              <div
                class="h-8 w-8 rounded-full bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
              </div>
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-gray-900">{{ $eventSetting->device->device_name ?? 'N/A' }}</p>
              <p class="text-sm text-gray-500">{{ $eventSetting->device_id }}</p>
            </div>
          </div>
        </div>
      </div>
    </x-card>

    <!-- Status & Settings -->
    <x-card title="Status & Settings" class="mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="text-center">
          <div
            class="inline-flex items-center justify-center w-12 h-12 rounded-full {{ $eventSetting->is_active ? 'bg-green-100' : 'bg-red-100' }} mb-3">
            <svg class="w-6 h-6 {{ $eventSetting->is_active ? 'text-green-600' : 'text-red-600' }}" fill="none"
              stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <h3 class="text-sm font-medium text-gray-900">Active Status</h3>
          <p class="text-sm text-gray-500">{{ $eventSetting->is_active ? 'Enabled' : 'Disabled' }}</p>
        </div>

        <div class="text-center">
          <div
            class="inline-flex items-center justify-center w-12 h-12 rounded-full {{ $eventSetting->send_image ? 'bg-blue-100' : 'bg-gray-100' }} mb-3">
            <svg class="w-6 h-6 {{ $eventSetting->send_image ? 'text-blue-600' : 'text-gray-400' }}" fill="none"
              stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>
          <h3 class="text-sm font-medium text-gray-900">Send Image</h3>
          <p class="text-sm text-gray-500">{{ $eventSetting->send_image ? 'Enabled' : 'Disabled' }}</p>
        </div>

        <div class="text-center">
          <div
            class="inline-flex items-center justify-center w-12 h-12 rounded-full {{ $eventSetting->send_message ? 'bg-purple-100' : 'bg-gray-100' }} mb-3">
            <svg class="w-6 h-6 {{ $eventSetting->send_message ? 'text-purple-600' : 'text-gray-400' }}" fill="none"
              stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
          </div>
          <h3 class="text-sm font-medium text-gray-900">Send Message</h3>
          <p class="text-sm text-gray-500">{{ $eventSetting->send_message ? 'Enabled' : 'Disabled' }}</p>
        </div>
      </div>
    </x-card>

    <!-- WhatsApp Configuration -->
    @if ($eventSetting->whatsapp_enabled)
      <x-card title="WhatsApp Configuration" class="mb-6">
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">WhatsApp Numbers</label>
            <div class="bg-gray-50 rounded-lg p-3">
              @if (is_array($eventSetting->whatsapp_numbers) && count($eventSetting->whatsapp_numbers) > 0)
                <div class="flex flex-wrap gap-2">
                  @foreach ($eventSetting->whatsapp_numbers as $number)
                    <span
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                      <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                      </svg>
                      {{ $number }}
                    </span>
                  @endforeach
                </div>
              @else
                <p class="text-sm text-gray-500">No WhatsApp numbers configured</p>
              @endif
            </div>
          </div>
        </div>
      </x-card>
    @endif


    <!-- Action Buttons -->
    <div class="flex items-center justify-between pt-6">
      <x-button variant="secondary" :href="route('branch-event-settings.index')">
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Back to Settings
      </x-button>

      <div class="flex items-center space-x-3">

        <x-button variant="primary" :href="route('branch-event-settings.edit', $eventSetting)">
          <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
          </svg>
          Edit Setting
        </x-button>
      </div>
    </div>

  </div>
@endsection
