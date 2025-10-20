@extends('layouts.app')

@section('title', 'Company Group Details')

@section('content')
  <div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">{{ $group->group_name }}</h1>
        <p class="mt-2 text-gray-600">{{ $group->province_name }}</p>
      </div>
      @if (auth()->user()->isAdmin())
        <div class="flex space-x-3">
          <x-button variant="warning" href="{{ route('company-groups.edit', $group) }}">
            Edit
          </x-button>
          <x-button variant="danger" @click="confirmDelete({{ $group->id }})">
            Delete
          </x-button>
        </div>
      @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Group Information -->
      <div class="lg:col-span-2">
        <x-card title="Group Information">
          <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6">
            <div>
              <dt class="text-sm font-medium text-gray-500">Province Code</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ $group->province_code }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Status</dt>
              <dd class="mt-1">
                <x-badge :variant="$group->status === 'active' ? 'success' : 'danger'">
                  {{ ucfirst($group->status) }}
                </x-badge>
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Phone</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ $group->phone ?: 'N/A' }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Email</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ $group->email ?: 'N/A' }}</dd>
            </div>
            <div class="md:col-span-2">
              <dt class="text-sm font-medium text-gray-500">Address</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ $group->address ?: 'N/A' }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Created</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ $group->created_at->format('M d, Y H:i') }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ $group->updated_at->format('M d, Y H:i') }}</dd>
            </div>
          </dl>
        </x-card>
      </div>

      <!-- Statistics -->
      <div class="space-y-4">
        <x-stat-card
          title="Total Branches"
          :value="$branchCounts['total']"
          color="blue"
          icon="<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'/>"
        />
        <x-stat-card
          title="Active Branches"
          :value="$branchCounts['active']"
          color="green"
          icon="<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'/>"
        />
        <x-stat-card
          title="Inactive Branches"
          :value="$branchCounts['inactive']"
          color="red"
          icon="<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'/>"
        />
      </div>
    </div>

    <!-- Company Branches -->
    <div class="mt-8">
      <x-card title="Company Branches" :padding="false">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch Code</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">City</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              @forelse($group->branches as $branch)
                <tr class="hover:bg-gray-50">
                  <td class="px-6 py-4 text-sm text-gray-900">{{ $branch->branch_code }}</td>
                  <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $branch->branch_name }}</td>
                  <td class="px-6 py-4 text-sm text-gray-900">{{ $branch->city_name }}</td>
                  <td class="px-6 py-4">
                    <x-badge :variant="$branch->status === 'active' ? 'success' : 'danger'">
                      {{ ucfirst($branch->status) }}
                    </x-badge>
                  </td>
                  <td class="px-6 py-4 text-right text-sm font-medium">
                    <a href="{{ route('company-branches.show', $branch) }}"
                      class="text-blue-600 hover:text-blue-900">View</a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                    No branches found. <a
                      href="{{ route('company-branches.create', ['group_id' => $group->id]) }}"
                      class="text-blue-600 hover:text-blue-800">Create one now</a>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </x-card>
    </div>

    <div class="mt-6">
      <a href="{{ route('company-groups.index') }}" class="text-blue-600 hover:text-blue-800">
        ← Back to Company Groups
      </a>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <x-confirm-modal id="confirm-delete" title="Delete Company Group"
    message="Are you sure you want to delete this company group? All associated branches and devices will also be deleted."
    confirmText="Delete" cancelText="Cancel" icon="warning" />

  <script>
    function confirmDelete(id) {
      window.dispatchEvent(new CustomEvent('open-modal-confirm-delete', {
        detail: {
          id: id
        }
      }));
    }

    window.addEventListener('confirm-confirm-delete', function() {
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = `/company-groups/{{ $group->id }}`;
      form.innerHTML = `
            @csrf
            @method('DELETE')
        `;
      document.body.appendChild(form);
      form.submit();
    });
  </script>
@endsection
