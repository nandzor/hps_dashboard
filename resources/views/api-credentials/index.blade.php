@extends('layouts.app')

@section('title', 'API Credentials')
@section('page-title', 'API Credentials Management')

@section('content')
  <div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">API Credentials</h1>
        <p class="mt-2 text-gray-600">Manage API keys and secrets for external integrations</p>
      </div>
      <x-button variant="primary" :href="route('api-credentials.create')">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Create Credential
      </x-button>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
      <x-stat-card title="Total Credentials" :value="$statistics['total']" icon="key" color="blue" />
      <x-stat-card title="Active" :value="$statistics['active']" icon="check-circle" color="green" />
      <x-stat-card title="Inactive" :value="$statistics['inactive']" icon="x-circle" color="red" />
      <x-stat-card title="Global Access" :value="$statistics['global_access']" icon="globe" color="purple" />
    </div>

    <!-- Search & Filter -->
    <x-card class="mb-6">
      <form method="GET" class="flex flex-wrap gap-4">
        <div class="flex-1 min-w-[300px]">
          <x-input name="search" :value="$search" placeholder="Search by name or API key..." label="Search" />
        </div>
        <div class="flex items-end gap-2">
          <x-button type="submit" variant="primary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            Search
          </x-button>
          @if ($search)
            <x-button variant="secondary" :href="route('api-credentials.index')">
              Clear
            </x-button>
          @endif
        </div>
      </form>
    </x-card>

    <!-- Credentials Table -->
    <x-card :padding="false">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Credential Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">API Key</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Scope</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Expires</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            @forelse($credentials->items() as $credential)
              <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-gray-900">{{ $credential->credential_name }}</div>
                  <div class="text-xs text-gray-500">Created {{ $credential->created_at->diffForHumans() }}</div>
                </td>
                <td class="px-6 py-4">
                  <code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $credential->masked_api_key }}</code>
                </td>
                <td class="px-6 py-4 text-sm text-center">
                  <x-badge variant="info" size="sm">Global Access</x-badge>
                </td>
                <td class="px-6 py-4 text-sm text-center">
                  @if ($credential->expires_at)
                    <span class="{{ $credential->isExpired() ? 'text-red-600' : 'text-gray-600' }}">
                      {{ $credential->expires_at->format('Y-m-d') }}
                    </span>
                  @else
                    <span class="text-gray-400">Never</span>
                  @endif
                </td>
                <td class="px-6 py-4 text-center">
                  <x-badge :variant="$credential->status === 'active' ? 'success' : ($credential->status === 'expired' ? 'danger' : 'secondary')" size="sm">
                    {{ ucfirst($credential->status) }}
                  </x-badge>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <x-action-dropdown>
                    <x-dropdown-link :href="route('api-credentials.show', $credential)">
                      👁️ View Details
                    </x-dropdown-link>

                    <x-dropdown-link :href="route('api-credentials.test', $credential)">
                      🧪 Test API
                    </x-dropdown-link>

                    <x-dropdown-divider />

                    <x-dropdown-link :href="route('api-credentials.edit', $credential)">
                      ✏️ Edit Credential
                    </x-dropdown-link>

                    <x-dropdown-divider />

                    <x-dropdown-button type="button" onclick="confirmDelete({{ $credential->id }})" variant="danger">
                      🗑️ Delete Credential
                    </x-dropdown-button>

                    <form id="delete-form-{{ $credential->id }}"
                      action="{{ route('api-credentials.destroy', $credential) }}" method="POST" class="hidden">
                      @csrf
                      @method('DELETE')
                    </form>
                  </x-action-dropdown>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                  <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                  </svg>
                  <p>No API credentials found</p>
                  @if ($search)
                    <p class="text-sm mt-1">Try adjusting your search</p>
                  @else
                    <p class="text-sm mt-1">
                      <a href="{{ route('api-credentials.create') }}" class="text-blue-600 hover:text-blue-800">Create
                        your first credential</a>
                    </p>
                  @endif
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if ($credentials->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
          {{ $credentials->links() }}
        </div>
      @endif
    </x-card>
  </div>

  <script>
    function confirmDelete(credentialId) {
      if (confirm(
          'Are you sure you want to delete this API credential?\n\nThis action cannot be undone and will immediately revoke API access.'
        )) {
        document.getElementById('delete-form-' + credentialId).submit();
      }
    }
  </script>
@endsection
