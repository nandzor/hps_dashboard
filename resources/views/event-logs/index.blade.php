@extends('layouts.app')

@section('title', 'Event Logs')
@section('page-title', 'Event Logs Management')

@section('content')
  <x-card>
    <!-- Header -->
    <div class="p-6 border-b border-gray-200">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
        <div class="flex-1 max-w-md">
          <form method="GET" action="{{ route('event-logs.index') }}" class="flex">
            <x-input name="search" :value="$search ?? ''" placeholder="Search events..." class="rounded-r-none border-r-0" />
            @if (request()->has('per_page'))
              <input type="hidden" name="per_page" value="{{ request()->get('per_page') }}">
            @endif
            <button type="submit"
              class="px-6 py-2 bg-gray-600 text-white rounded-r-lg hover:bg-gray-700 transition-colors">
              Search
            </button>
          </form>
        </div>

        <div class="flex items-center space-x-4">
          <!-- Per Page Selector -->
          <div class="flex items-center space-x-2">
            <x-per-page-selector :options="$perPageOptions ?? [10, 25, 50, 100]" :current="$perPage ?? 10" :url="route('event-logs.index')" type="server" />
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
      <form method="GET">
        <div class="flex flex-col lg:flex-row lg:items-end gap-4">
          <!-- Filter Fields -->
          <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-event-type-select name="event_type" label="Event Type" :value="request('event_type')" placeholder="All Types" />

            <x-company-branch-select name="branch_id" label="Branch" :value="request('branch_id')" placeholder="All Branches" />
          </div>

          <!-- Action Buttons -->
          <div class="flex items-center gap-2">
            <x-button type="submit" variant="primary" size="md">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
              </svg>
              Filter
            </x-button>

            @if ($events->total() > 0)
              <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                  <button type="button"
                    class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500 transition-all">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Export
                    <svg class="w-4 h-4 ml-2" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd"
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                    </svg>
                  </button>
                </x-slot>

                <x-dropdown-link :href="route(
                    'event-logs.export',
                    array_merge(request()->only(['event_type', 'branch_id']), [
                        'format' => 'excel',
                    ]),
                )" variant="success">
                  <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                  </svg>
                  Export to Excel
                </x-dropdown-link>

                <x-dropdown-link :href="route(
                    'event-logs.export',
                    array_merge(request()->only(['event_type', 'branch_id']), [
                        'format' => 'pdf',
                    ]),
                )" variant="danger">
                  <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                  </svg>
                  Export to PDF
                </x-dropdown-link>
              </x-dropdown>
            @endif
          </div>
        </div>
      </form>
    </div>

    <!-- Table -->
    <x-table :headers="['Event Type', 'Branch', 'Device', 'Re-ID', 'Time', 'Notifications', 'Actions']">
      @forelse($events as $event)
        <tr class="hover:bg-blue-50 transition-colors">
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="flex items-center">
              <div class="flex-shrink-0 h-10 w-10">
                <div
                  class="h-10 w-10 rounded-full bg-gradient-to-br from-red-400 to-red-600 flex items-center justify-center shadow-md">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <x-badge :variant="$event->event_type === 'detection' ? 'success' : ($event->event_type === 'alert' ? 'danger' : ($event->event_type === 'motion' ? 'warning' : 'gray'))">
                  {{ ucfirst($event->event_type) }}
                </x-badge>
              </div>
            </div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
            {{ $event->branch->branch_name ?? 'N/A' }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
            {{ $event->device->device_name ?? 'N/A' }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
            {{ $event->re_id ? Str::limit($event->re_id, 20) : 'N/A' }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            {{ \Carbon\Carbon::parse($event->event_timestamp)->format('M d, H:i:s') }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-center">
            @if ($event->notification_sent)
              <x-badge variant="success">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd"
                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                </svg>
                Sent
              </x-badge>
            @else
              <x-badge variant="gray">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" />
                </svg>
                Pending
              </x-badge>
            @endif
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
            <x-action-dropdown>
              <x-dropdown-link :href="route('event-logs.show', $event)">
                👁️ View Details
              </x-dropdown-link>
            </x-action-dropdown>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="7" class="px-6 py-12 text-center">
            <div class="flex flex-col items-center justify-center">
              <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
              </svg>
              <p class="text-gray-500 text-lg font-medium">No events found</p>
              <p class="text-gray-400 text-sm mt-1">Try adjusting your search criteria</p>
            </div>
          </td>
        </tr>
      @endforelse
    </x-table>

    <!-- Pagination Info & Controls -->
    <div class="px-6 py-4 border-t border-gray-200">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
        <!-- Pagination Info -->
        <div class="text-sm text-gray-700">
          Showing
          <span class="font-medium">{{ $events->firstItem() ?? 0 }}</span>
          to
          <span class="font-medium">{{ $events->lastItem() ?? 0 }}</span>
          of
          <span class="font-medium">{{ $events->total() }}</span>
          results
          @if (request()->has('search'))
            for "<span class="font-medium text-blue-600">{{ request()->get('search') }}</span>"
          @endif
        </div>

        <!-- Pagination Controls -->
        @if ($events->hasPages())
          <x-pagination :paginator="$events" />
        @endif
      </div>
    </div>
  </x-card>
@endsection
