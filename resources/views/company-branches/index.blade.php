@extends('layouts.app')

@section('title', 'Company Branches')
@section('page-title', 'Company Branches Management')

@section('content')
  <x-card>
    <!-- Header -->
    <div class="p-6 border-b border-gray-200">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
        <div class="flex-1 max-w-md">
          <form method="GET" action="{{ route('company-branches.index') }}" class="flex">
            <x-input name="search" :value="$search ?? ''" placeholder="Search branches..." class="rounded-r-none border-r-0" />
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
            <x-per-page-selector :options="$perPageOptions ?? [10, 25, 50, 100]" :current="$perPage ?? 10" :url="route('company-branches.index')" type="server" />
          </div>

          <!-- Add Branch Button -->
          <x-button variant="primary" size="sm" :href="route('company-branches.create')">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Branch
          </x-button>
        </div>
      </div>
    </div>

    <!-- Table -->
    <x-table :headers="['Branch Code', 'Branch Name', 'City', 'Group', 'Status', 'Actions']">
      @forelse($companyBranches as $branch)
        <tr class="hover:bg-blue-50 transition-colors">
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="flex items-center">
              <div class="flex-shrink-0 h-10 w-10">
                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center shadow-md">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <div class="text-sm font-medium text-gray-900">{{ $branch->branch_code }}</div>
              </div>
            </div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-900">{{ $branch->branch_name }}</div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-900">{{ $branch->city_name }}</div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            {{ $branch->group->group_name ?? 'N/A' }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <x-badge :variant="$branch->status === 'active' ? 'success' : 'danger'">
              {{ ucfirst($branch->status) }}
            </x-badge>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
            <x-action-dropdown>
              <x-dropdown-link :href="route('company-branches.show', $branch)">
                👁️ View Details
              </x-dropdown-link>

              <x-dropdown-link :href="route('company-branches.edit', $branch)">
                ✏️ Edit Branch
              </x-dropdown-link>

              <x-dropdown-divider />

              <x-dropdown-button type="button" onclick="confirmDelete({{ $branch->id }})" variant="danger">
                🗑️ Delete Branch
              </x-dropdown-button>

              <form id="delete-form-{{ $branch->id }}" action="{{ route('company-branches.destroy', $branch->id) }}" method="POST"
                class="hidden">
                @csrf
                @method('DELETE')
              </form>
            </x-action-dropdown>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="px-6 py-12 text-center">
            <div class="flex flex-col items-center justify-center">
              <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
              <p class="text-gray-500 text-lg font-medium">No branches found</p>
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
          <span class="font-medium">{{ $companyBranches->firstItem() ?? 0 }}</span>
          to
          <span class="font-medium">{{ $companyBranches->lastItem() ?? 0 }}</span>
          of
          <span class="font-medium">{{ $companyBranches->total() }}</span>
          results
          @if (request()->has('search'))
            for "<span class="font-medium text-blue-600">{{ request()->get('search') }}</span>"
          @endif
        </div>

        <!-- Pagination Controls -->
        @if ($companyBranches->hasPages())
          <x-pagination :paginator="$companyBranches" />
        @endif
      </div>
    </div>
  </x-card>

  <!-- Delete Confirmation Modal -->
  <x-confirm-modal id="confirm-delete" title="Confirm Delete"
    message="This action cannot be undone. The branch will be permanently deleted." confirmText="Delete Branch"
    cancelText="Cancel" icon="warning" confirmAction="handleDeleteConfirm(data)" />
@endsection

@push('scripts')
  <script>
    // Store branchId for deletion
    let pendingDeleteBranchId = null;

    function confirmDelete(branchId) {
      pendingDeleteBranchId = branchId;
      // Dispatch event to open modal with branchId
      window.dispatchEvent(new CustomEvent('open-modal-confirm-delete', {
        detail: {
          branchId: branchId
        }
      }));
    }

    function handleDeleteConfirm(data) {
      const branchId = data?.branchId || pendingDeleteBranchId;
      if (branchId) {
        const form = document.getElementById('delete-form-' + branchId);
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






