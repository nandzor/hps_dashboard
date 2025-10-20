@extends('layouts.app')

@section('title', 'Edit Branch Event Setting')
@section('page-title', 'Edit Branch Event Setting')

@section('content')
  <div class="max-w-4xl">
    <x-card title="Event Setting Configuration">
      <div class="mb-6">
        <div class="flex items-center justify-between">
          <p class="text-sm text-gray-500">Configure event settings for
            {{ $branchEventSetting->branch->branch_name ?? 'N/A' }} -
            {{ $branchEventSetting->device->device_name ?? 'N/A' }}</p>
          <x-badge :variant="$branchEventSetting->is_active ? 'success' : 'danger'">
            {{ $branchEventSetting->is_active ? 'Active' : 'Inactive' }}
          </x-badge>
        </div>
      </div>

      <form method="POST" action="{{ route('branch-event-settings.update', $branchEventSetting) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Basic Information (Readonly) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
            <div class="flex items-center p-3 bg-gray-50 rounded-lg border">
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
                <p class="text-sm font-medium text-gray-900">{{ $branchEventSetting->branch->branch_name ?? 'N/A' }}</p>
                <p class="text-sm text-gray-500">{{ $branchEventSetting->branch->branch_code ?? 'N/A' }}</p>
              </div>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Device</label>
            <div class="flex items-center p-3 bg-gray-50 rounded-lg border">
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
                <p class="text-sm font-medium text-gray-900">{{ $branchEventSetting->device->device_name ?? 'N/A' }}</p>
                <p class="text-sm text-gray-500">{{ $branchEventSetting->device_id }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Status Settings -->
        <div class="border-t border-gray-200 pt-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Status & Activation</h3>
          <div class="space-y-4">
            <x-checkbox name="is_active" label="Active Setting" :checked="$branchEventSetting->is_active"
              hint="Enable or disable this event setting" />
          </div>
        </div>

        <!-- Notification Settings -->
        <div class="border-t border-gray-200 pt-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Notification Settings</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
              <x-checkbox name="send_image" label="Send Image" :checked="$branchEventSetting->send_image"
                hint="Include captured image in notifications" />

              <x-checkbox name="send_message" label="Send Message" :checked="$branchEventSetting->send_message"
                hint="Send text message notifications" />
            </div>

            <div class="space-y-4">
              <x-checkbox name="whatsapp_enabled" label="WhatsApp Enabled" :checked="$branchEventSetting->whatsapp_enabled"
                hint="Enable WhatsApp notifications" />
            </div>
          </div>
        </div>

        <!-- WhatsApp Configuration -->
        @if ($branchEventSetting->whatsapp_enabled)
          <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">WhatsApp Configuration</h3>
            <div class="space-y-4">
              <x-input name="whatsapp_numbers" label="WhatsApp Numbers" :value="is_array($branchEventSetting->whatsapp_numbers)
                  ? implode(', ', $branchEventSetting->whatsapp_numbers)
                  : $branchEventSetting->whatsapp_numbers"
                placeholder="+6281234567890, +6281234567891" hint="Enter WhatsApp numbers separated by commas" />
            </div>
          </div>
        @endif


        <!-- Action Buttons -->
        <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
          <x-button variant="secondary" :href="route('branch-event-settings.show', $branchEventSetting)">
            Cancel
          </x-button>
          <x-button type="submit" variant="primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Update Setting
          </x-button>
        </div>
      </form>
    </x-card>
  </div>
@endsection

@push('scripts')
  <script>
    // Show/hide WhatsApp configuration based on checkbox
    document.addEventListener('DOMContentLoaded', function() {
      const whatsappCheckbox = document.querySelector('input[name="whatsapp_enabled"]');
      const whatsappSection = document.querySelector(
        '.border-t.border-gray-200.pt-6:has(input[name="whatsapp_numbers"])');

      function toggleWhatsAppSection() {
        if (whatsappCheckbox.checked) {
          if (whatsappSection) {
            whatsappSection.style.display = 'block';
          } else {
            // Create WhatsApp configuration section if it doesn't exist
            const messageSection = document.querySelector('div:has(textarea[name="message_template"])').closest(
              '.border-t');
            const whatsappConfig = document.createElement('div');
            whatsappConfig.className = 'border-t border-gray-200 pt-6';
            whatsappConfig.innerHTML = `
            <h3 class="text-lg font-medium text-gray-900 mb-4">WhatsApp Configuration</h3>
            <div class="space-y-4">
              <x-input name="whatsapp_numbers" label="WhatsApp Numbers" 
                placeholder="+6281234567890, +6281234567891" 
                hint="Enter WhatsApp numbers separated by commas" />
            </div>
          `;
            messageSection.parentNode.insertBefore(whatsappConfig, messageSection);
          }
        } else {
          if (whatsappSection) {
            whatsappSection.style.display = 'none';
          }
        }
      }

      whatsappCheckbox.addEventListener('change', toggleWhatsAppSection);
      toggleWhatsAppSection(); // Initial call
    });
  </script>
@endpush
