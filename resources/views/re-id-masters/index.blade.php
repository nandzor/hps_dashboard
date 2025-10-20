@extends('layouts.app')

@section('title', 'Person Tracking (Re-ID)')
@section('page-title', 'Person Tracking (Re-ID) Management')

@section('content')
  <!-- Statistics Cards -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <x-stat-card title="Total Records" :value="$statistics['total_records']" icon="users" color="blue" />
    <x-stat-card title="Active Tracking" :value="$statistics['active_records']" icon="eye" color="green" />
    <x-stat-card title="Unique Persons" :value="$statistics['unique_persons']" icon="users" color="purple" />
    <x-stat-card title="Total Detections" :value="$statistics['total_detections']" icon="chart-bar" color="orange" />
  </div>

  <x-card>
    <!-- Header -->
    <div class="p-6 border-b border-gray-200">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
        <div class="flex-1 max-w-md">
          <form method="GET" action="{{ route('re-id-masters.index') }}" class="flex">
            <x-input name="search" :value="$search ?? ''" placeholder="Search by Re-ID or name..."
              class="rounded-r-none border-r-0" />
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
            <x-per-page-selector :options="$perPageOptions ?? [10, 25, 50, 100]" :current="$perPage ?? 10" :url="route('re-id-masters.index')" type="server" />
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
      <form method="GET">
        <div class="flex flex-col lg:flex-row lg:items-end gap-4">
          <!-- Filter Fields -->
          <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-company-branch-select name="branch_id" label="Branch" :value="request('branch_id')"
              placeholder="All Branches" />

            <x-input type="date" name="date_from" :value="request('date_from')" label="From Date" />

            <x-input type="date" name="date_to" :value="request('date_to')" label="To Date" />
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

            @if ($persons->total() > 0)
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
                    're-id-masters.export',
                    array_merge(request()->only(['branch_id', 'date_from', 'date_to']), [
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
                    're-id-masters.export',
                    array_merge(request()->only(['branch_id', 'date_from', 'date_to']), [
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
    <x-table :headers="['Re-ID', 'Person Name', 'Detection Date', 'Branches', 'Detections', 'Status', 'Actions']">
      @forelse($persons as $person)
        <tr class="hover:bg-blue-50 transition-colors">
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="flex items-center">
              <div class="flex-shrink-0 h-10 w-10">
                <div
                  class="h-10 w-10 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center shadow-md">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <div class="text-sm font-mono text-gray-900">{{ Str::limit($person->re_id, 30) }}</div>
              </div>
            </div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-900">{{ $person->person_name ?: 'Unknown' }}</div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            {{ \Carbon\Carbon::parse($person->detection_date)->format('M d, Y') }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-center">
            <x-badge variant="primary">
              {{ $person->total_detection_branch_count }}
            </x-badge>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-center">
            <x-badge variant="purple">
              {{ $person->total_actual_count }}
            </x-badge>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <x-badge :variant="$person->status === 'active' ? 'success' : 'danger'">
              {{ ucfirst($person->status) }}
            </x-badge>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
            <x-action-dropdown>
              <x-dropdown-link :href="route('re-id-masters.show', ['reId' => $person->re_id, 'date' => $person->detection_date])">
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
                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              <p class="text-gray-500 text-lg font-medium">No persons detected yet</p>
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
          <span class="font-medium">{{ $persons->firstItem() ?? 0 }}</span>
          to
          <span class="font-medium">{{ $persons->lastItem() ?? 0 }}</span>
          of
          <span class="font-medium">{{ $persons->total() }}</span>
          results
          @if (request()->has('search'))
            for "<span class="font-medium text-blue-600">{{ request()->get('search') }}</span>"
          @endif
        </div>

        <!-- Pagination Controls -->
        @if ($persons->hasPages())
          <x-pagination :paginator="$persons" />
        @endif
      </div>
    </div>
  </x-card>
@endsection
