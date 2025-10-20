@extends('layouts.app')

@section('title', 'API Credential Details')
@section('page-title', 'API Credential Details')

@section('content')
  <div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">{{ $apiCredential->credential_name }}</h1>
        <p class="mt-2 text-gray-600">API Credential Details</p>
      </div>
      <div class="flex space-x-3">
        <x-button variant="success" :href="route('api-credentials.test', $apiCredential)">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
          </svg>
          Test API
        </x-button>
        <x-button variant="warning" :href="route('api-credentials.edit', $apiCredential)">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
          </svg>
          Edit
        </x-button>
        <form method="POST" action="{{ route('api-credentials.destroy', $apiCredential) }}" class="inline"
          onsubmit="return confirm('Are you sure you want to delete this API credential?')">
          @csrf
          @method('DELETE')
          <x-button type="submit" variant="danger">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Delete
          </x-button>
        </form>
      </div>
    </div>

    <!-- Success Message (show API secret once) -->
    @if (session('success'))
      <div class="mb-6 p-6 bg-green-50 border-2 border-green-500 rounded-lg">
        <div class="flex">
          <svg class="w-6 h-6 text-green-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div class="flex-1">
            <p class="text-sm font-semibold text-green-900">{{ session('success') }}</p>
            @if (session('api_secret'))
              <div class="mt-4 p-4 bg-white border border-green-300 rounded">
                <p class="text-xs font-semibold text-gray-700 mb-2">API Secret (Save this now!):</p>
                <code
                  class="block text-sm bg-gray-900 text-green-400 p-3 rounded font-mono break-all">{{ session('api_secret') }}</code>
                <button onclick="copyToClipboard('{{ session('api_secret') }}')"
                  class="mt-2 text-xs text-blue-600 hover:text-blue-800">
                  📋 Click to copy
                </button>
              </div>
            @endif
          </div>
        </div>
      </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Information -->
      <div class="lg:col-span-2 space-y-6">
        <x-card title="Credential Information">
          <dl class="grid grid-cols-1 gap-4">
            <div>
              <dt class="text-sm font-medium text-gray-500">Credential Name</dt>
              <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $apiCredential->credential_name }}</dd>
            </div>

            <div>
              <dt class="text-sm font-medium text-gray-500">API Key</dt>
              <dd class="mt-1">
                <div class="flex items-center space-x-2">
                  <code
                    class="flex-1 text-xs bg-gray-900 text-green-400 p-3 rounded font-mono break-all">{{ $apiCredential->masked_api_key }}</code>
                  <button onclick="copyToClipboard('{{ $apiCredential->api_key }}')"
                    class="text-blue-600 hover:text-blue-800 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                  </button>
                </div>
                <p class="mt-1 text-xs text-gray-500">Click copy button to get full API key</p>
              </dd>
            </div>

            <div>
              <dt class="text-sm font-medium text-gray-500">API Secret</dt>
              <dd class="mt-1 text-xs text-gray-500">
                <span class="bg-gray-100 px-3 py-2 rounded">••••••••••••••••••••••••••••••••</span>
                <p class="mt-1 text-xs">Secret is hidden for security. Only shown once after creation.</p>
              </dd>
            </div>

            <div>
              <dt class="text-sm font-medium text-gray-500">Status</dt>
              <dd class="mt-1">
                <x-badge :variant="$apiCredential->status === 'active' ? 'success' : ($apiCredential->status === 'expired' ? 'danger' : 'secondary')" size="sm">
                  {{ ucfirst($apiCredential->status) }}
                </x-badge>
              </dd>
            </div>

            <div>
              <dt class="text-sm font-medium text-gray-500">Expires At</dt>
              <dd class="mt-1">
                @if ($apiCredential->expires_at)
                  <span
                    class="text-sm {{ $apiCredential->isExpired() ? 'text-red-600 font-semibold' : 'text-gray-900' }}">
                    {{ $apiCredential->expires_at->format('F d, Y H:i') }}
                    <span class="text-xs text-gray-500">({{ $apiCredential->expires_at->diffForHumans() }})</span>
                  </span>
                @else
                  <span class="text-sm text-green-600 font-semibold">Never expires</span>
                @endif
              </dd>
            </div>

            <div>
              <dt class="text-sm font-medium text-gray-500">Last Used</dt>
              <dd class="mt-1 text-sm text-gray-900">
                {{ $apiCredential->last_used_at ? $apiCredential->last_used_at->diffForHumans() : 'Never' }}
              </dd>
            </div>
          </dl>
        </x-card>

        <!-- Access Scope -->
        <x-card title="Access Scope">
          <div class="flex items-start">
            <svg class="w-6 h-6 text-blue-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
              viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
              <p class="text-sm font-semibold text-gray-900">Global Access</p>
              <p class="text-sm text-gray-600 mt-1">Full access to all branches and devices</p>
              <div class="mt-3 flex flex-wrap gap-2">
                <x-badge variant="info">All Branches</x-badge>
                <x-badge variant="info">All Devices</x-badge>
                <x-badge variant="success">Full Permissions</x-badge>
              </div>
            </div>
          </div>
        </x-card>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <x-card title="Metadata">
          <dl class="space-y-3">
            <div>
              <dt class="text-xs font-medium text-gray-500">Created By</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ $apiCredential->creator->name ?? 'System' }}</dd>
            </div>
            <div>
              <dt class="text-xs font-medium text-gray-500">Created At</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ $apiCredential->created_at->format('M d, Y H:i') }}</dd>
            </div>
            <div>
              <dt class="text-xs font-medium text-gray-500">Updated At</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ $apiCredential->updated_at->format('M d, Y H:i') }}</dd>
            </div>
          </dl>
        </x-card>

        <x-card title="Quick Actions">
          <div class="space-y-2">
            <x-button variant="secondary" :href="route('api-credentials.edit', $apiCredential)" class="w-full">
              Edit Credential
            </x-button>
            <form method="POST" action="{{ route('api-credentials.destroy', $apiCredential) }}"
              onsubmit="return confirm('Delete this API credential?')">
              @csrf
              @method('DELETE')
              <x-button type="submit" variant="danger" class="w-full">
                Revoke & Delete
              </x-button>
            </form>
          </div>
        </x-card>
      </div>
    </div>

    <div class="mt-6">
      <x-button variant="secondary" :href="route('api-credentials.index')">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Back to API Credentials
      </x-button>
    </div>
  </div>

  <script>
    function copyToClipboard(text) {
      navigator.clipboard.writeText(text).then(() => {
        // Show notification
        const notification = document.createElement('div');
        notification.className =
          'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-opacity duration-300';
        notification.textContent = '✓ Copied to clipboard!';
        document.body.appendChild(notification);

        setTimeout(() => {
          notification.style.opacity = '0';
          setTimeout(() => notification.remove(), 300);
        }, 2000);
      }).catch(err => {
        alert('Failed to copy');
      });
    }
  </script>
@endsection
