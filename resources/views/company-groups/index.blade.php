@extends('layouts.app')

@section('title', 'Company Groups')
@section('page-title', 'Company Groups Management')

@section('content')
  <x-card>
    <!-- Header -->
    <div class="p-6 border-b border-gray-200">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
        <div class="flex-1 max-w-md">
          <form method="GET" action="{{ route('company-groups.index') }}" class="flex">
            <x-input name="search" :value="$search ?? ''" placeholder="Search groups..." class="rounded-r-none border-r-0" />
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
            <x-per-page-selector :options="$perPageOptions ?? [10, 25, 50, 100]" :current="$perPage ?? 10" :url="route('company-groups.index')" type="server" />
          </div>

          <!-- Add Group Button -->
          @if (auth()->user()->isAdmin())
            <x-button variant="primary" size="sm" :href="route('company-groups.create')">
              <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Add Group
            </x-button>
          @endif
        </div>
      </div>
    </div>

    <!-- Table -->
    <x-table :headers="['Province', 'Group Name', 'Contact', 'Status', 'Branches', 'Actions']">
      @forelse($companyGroups as $group)
        <tr class="hover:bg-blue-50 transition-colors">
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="flex items-center">
              <div class="flex-shrink-0 h-10 w-10">
                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center shadow-md">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <div class="text-sm font-medium text-gray-900">{{ $group->province_name }}</div>
                <div class="text-sm text-gray-500">{{ $group->province_code }}</div>
              </div>
            </div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-900">{{ $group->group_name }}</div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-900">{{ $group->phone }}</div>
            <div class="text-sm text-gray-500">{{ $group->email }}</div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <x-badge :variant="$group->status === 'active' ? 'success' : 'danger'">
              {{ ucfirst($group->status) }}
            </x-badge>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
            {{ $group->branches_count ?? 0 }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
            <x-action-dropdown>
              <x-dropdown-link :href="route('company-groups.show', $group)">
                👁️ View Details
              </x-dropdown-link>

              <x-dropdown-link :href="route('company-branches.create', ['group_id' => $group->id])">
                🏢 Add Branch
              </x-dropdown-link>

              @if (auth()->user()->isAdmin())
                <x-dropdown-link :href="route('company-groups.edit', $group)">
                  ✏️ Edit Group
                </x-dropdown-link>

                <x-dropdown-divider />

                <x-dropdown-button type="button" onclick="confirmDelete({{ $group->id }})" variant="danger">
                  🗑️ Delete Group
                </x-dropdown-button>

                <form id="delete-form-{{ $group->id }}" action="{{ route('company-groups.destroy', $group->id) }}" method="POST"
                  class="hidden">
                  @csrf
                  @method('DELETE')
                </form>
              @endif
            </x-action-dropdown>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="px-6 py-12 text-center">
            <div class="flex flex-col items-center justify-center">
              <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
              </svg>
              <p class="text-gray-500 text-lg font-medium">No groups found</p>
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
          <span class="font-medium">{{ $companyGroups->firstItem() ?? 0 }}</span>
          to
          <span class="font-medium">{{ $companyGroups->lastItem() ?? 0 }}</span>
          of
          <span class="font-medium">{{ $companyGroups->total() }}</span>
          results
          @if (request()->has('search'))
            for "<span class="font-medium text-blue-600">{{ request()->get('search') }}</span>"
          @endif
        </div>

        <!-- Pagination Controls -->
        @if ($companyGroups->hasPages())
          <x-pagination :paginator="$companyGroups" />
        @endif
      </div>
    </div>
  </x-card>

  <!-- Delete Confirmation Modal -->
  <x-confirm-modal id="confirm-delete" title="Confirm Delete"
    message="This action cannot be undone. The group will be permanently deleted." confirmText="Delete Group"
    cancelText="Cancel" icon="warning" confirmAction="handleDeleteConfirm(data)" />
@endsection

@push('scripts')
  <script>
    // Store groupId for deletion
    let pendingDeleteGroupId = null;

    function confirmDelete(groupId) {
      pendingDeleteGroupId = groupId;
      // Dispatch event to open modal with groupId
      window.dispatchEvent(new CustomEvent('open-modal-confirm-delete', {
        detail: {
          groupId: groupId
        }
      }));
    }

    function handleDeleteConfirm(data) {
      const groupId = data?.groupId || pendingDeleteGroupId;
      if (groupId) {
        const form = document.getElementById('delete-form-' + groupId);
        if (form) {
          form.submit();
        }
      }
    }

    // Make functions globally available
    window.confirmDelete = confirmDelete;
    window.handleDeleteConfirm = handleDeleteConfirm;
  </script>
@endpush
