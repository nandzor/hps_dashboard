@extends('layouts.app')

@section('title', 'WhatsApp Settings')
@section('page-title', 'WhatsApp Settings Management')

@section('content')
  <x-card>
    <!-- Header -->
    <div class="p-6 border-b border-gray-200">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">WhatsApp Settings</h1>
          <p class="text-sm text-gray-500 mt-1">Manage WhatsApp phone numbers and message templates</p>
        </div>

        <div class="flex items-center space-x-3">
          <x-button variant="primary" size="sm" :href="route('whatsapp-settings.create')">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add WhatsApp Settings
          </x-button>
        </div>
      </div>
    </div>

    <!-- Table -->
    <x-table :headers="['Name', 'Description', 'Phone Numbers', 'Template Preview', 'Status', 'Default', 'Actions']">
      @forelse($whatsappSettings as $setting)
        <tr class="hover:bg-blue-50 transition-colors">
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="flex items-center">
              <div class="flex-shrink-0 h-10 w-10">
                <div
                  class="h-10 w-10 rounded-full bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center shadow-md">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <div class="text-sm font-medium text-gray-900">{{ $setting->name }}</div>
              </div>
            </div>
          </td>
          <td class="px-6 py-4">
            <div class="text-sm text-gray-900">{{ $setting->description ?? 'No description' }}</div>
          </td>
          <td class="px-6 py-4">
            <div class="flex flex-wrap gap-1">
              @foreach ($setting->phone_numbers as $number)
                <span
                  class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                  <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  </svg>
                  {{ $number }}
                </span>
              @endforeach
            </div>
          </td>
          <td class="px-6 py-4">
            <div class="text-sm text-gray-900 max-w-xs truncate">
              {{ Str::limit($setting->message_template, 50) }}
            </div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <x-badge :variant="$setting->is_active ? 'success' : 'danger'">
              {{ $setting->is_active ? 'Active' : 'Inactive' }}
            </x-badge>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            @if ($setting->is_default)
              <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Default
              </span>
            @else
              <button onclick="setAsDefault({{ $setting->id }})"
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 hover:bg-yellow-100 hover:text-yellow-800 transition-colors">
                Set Default
              </button>
            @endif
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
            <x-action-dropdown>
              <x-dropdown-link :href="route('whatsapp-settings.show', $setting)">
                👁️ View Details
              </x-dropdown-link>

              <x-dropdown-link :href="route('whatsapp-settings.edit', $setting)">
                ✏️ Edit Settings
              </x-dropdown-link>

              @if (!$setting->is_default)
                <x-dropdown-divider />
                <x-dropdown-button type="button" onclick="confirmDelete({{ $setting->id }})" variant="danger">
                  🗑️ Delete Settings
                </x-dropdown-button>
              @endif

              <form id="delete-form-{{ $setting->id }}" action="{{ route('whatsapp-settings.destroy', $setting->id) }}"
                method="POST" class="hidden">
                @csrf
                @method('DELETE')
              </form>
            </x-action-dropdown>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="7" class="px-6 py-12 text-center">
            <div class="flex flex-col items-center justify-center">
              <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
              </svg>
              <p class="text-gray-500 text-lg font-medium">No WhatsApp settings found</p>
              <p class="text-gray-400 text-sm mt-1">Create your first WhatsApp settings</p>
            </div>
          </td>
        </tr>
      @endforelse
    </x-table>
  </x-card>

  <!-- Delete Confirmation Modal -->
  <x-confirm-modal id="confirm-delete" title="Confirm Delete"
    message="This action cannot be undone. The WhatsApp settings will be permanently deleted."
    confirmText="Delete Settings" cancelText="Cancel" icon="warning" confirmAction="handleDeleteConfirm(data)" />
@endsection

@push('scripts')
  <script>
    // Store settingId for deletion
    let pendingDeleteSettingId = null;

    function confirmDelete(settingId) {
      pendingDeleteSettingId = settingId;
      // Dispatch event to open modal with settingId
      window.dispatchEvent(new CustomEvent('open-modal-confirm-delete', {
        detail: {
          settingId: settingId
        }
      }));
    }

    function handleDeleteConfirm(data) {
      const settingId = data?.settingId || pendingDeleteSettingId;
      if (settingId) {
        const form = document.getElementById('delete-form-' + settingId);
        if (form) {
          form.submit();
        }
      }
    }

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

    // Make functions globally available
    window.confirmDelete = confirmDelete;
    window.handleDeleteConfirm = handleDeleteConfirm;
    window.setAsDefault = setAsDefault;
  </script>
@endpush
