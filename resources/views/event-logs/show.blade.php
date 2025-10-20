@extends('layouts.app')

@section('title', 'Event Details')

@section('content')
  <div class="max-w-5xl mx-auto">
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-900">Event #{{ $eventLog->id }}</h1>
      <p class="mt-2 text-gray-600">{{ ucfirst($eventLog->event_type) }} Event</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2">
        <x-card title="Event Information">
          <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6">
            <div>
              <dt class="text-sm font-medium text-gray-500">Event Type</dt>
              <dd class="mt-1">
                <span
                  class="px-2 py-1 text-xs font-semibold rounded
                                @if ($eventLog->event_type === 'detection') bg-green-100 text-green-800
                                @elseif($eventLog->event_type === 'alert') bg-red-100 text-red-800
                                @elseif($eventLog->event_type === 'motion') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800 @endif">
                  {{ ucfirst($eventLog->event_type) }}
                </span>
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Timestamp</dt>
              <dd class="mt-1 text-sm text-gray-900">
                {{ \Carbon\Carbon::parse($eventLog->event_timestamp)->format('M d, Y H:i:s') }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Branch</dt>
              <dd class="mt-1 text-sm text-gray-900">
                <a href="{{ route('company-branches.show', $eventLog->branch) }}"
                  class="text-blue-600 hover:text-blue-800">
                  {{ $eventLog->branch->branch_name }}
                </a>
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Device</dt>
              <dd class="mt-1 text-sm text-gray-900">
                <a href="{{ route('device-masters.show', $eventLog->device_id) }}"
                  class="text-blue-600 hover:text-blue-800">
                  {{ $eventLog->device->device_name ?? $eventLog->device_id }}
                </a>
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Re-ID (Person)</dt>
              <dd class="mt-1 text-sm text-gray-900 font-mono">
                @if ($eventLog->re_id)
                  <a href="{{ route('re-id-masters.show', ['reId' => $eventLog->re_id, 'date' => now()->toDateString()]) }}"
                    class="text-blue-600 hover:text-blue-800">
                    {{ Str::limit($eventLog->re_id, 30) }}
                  </a>
                @else
                  N/A
                @endif
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Detected Count</dt>
              <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $eventLog->detected_count }}</dd>
            </div>
            @if ($eventLog->image_path)
              <div class="md:col-span-2">
                <dt class="text-sm font-medium text-gray-500">Detection Image</dt>
                <dd class="mt-2">
                  <img src="{{ asset('storage/' . $eventLog->image_path) }}" alt="Detection"
                    class="max-w-md rounded-lg border border-gray-200">
                </dd>
              </div>
            @endif
            @if ($eventLog->event_data)
              <div class="md:col-span-2">
                <dt class="text-sm font-medium text-gray-500">Event Data (JSON)</dt>
                <dd class="mt-1">
                  <pre class="bg-gray-100 p-3 rounded text-xs overflow-x-auto">{{ json_encode($eventLog->event_data, JSON_PRETTY_PRINT) }}</pre>
                </dd>
              </div>
            @endif
          </dl>
        </x-card>
      </div>

      <div>
        <x-card title="Notification Status">
          <div class="space-y-4">
            <div class="flex justify-between items-center">
              <span class="text-gray-600">Image Sent</span>
              @if ($eventLog->image_sent)
                <svg class="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" />
                </svg>
              @else
                <svg class="w-6 h-6 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" />
                </svg>
              @endif
            </div>
            <div class="flex justify-between items-center">
              <span class="text-gray-600">Message Sent</span>
              @if ($eventLog->message_sent)
                <svg class="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" />
                </svg>
              @else
                <svg class="w-6 h-6 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" />
                </svg>
              @endif
            </div>
            <div class="flex justify-between items-center">
              <span class="text-gray-600">WhatsApp Sent</span>
              @if ($eventLog->notification_sent)
                <svg class="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" />
                </svg>
              @else
                <svg class="w-6 h-6 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" />
                </svg>
              @endif
            </div>
          </div>
        </x-card>
      </div>
    </div>

    <div class="mt-6">
      <a href="{{ route('event-logs.index') }}" class="text-blue-600 hover:text-blue-800">← Back to Events</a>
    </div>
  </div>
@endsection
