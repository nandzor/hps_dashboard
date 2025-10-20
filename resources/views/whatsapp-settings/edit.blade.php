@extends('layouts.app')

@section('title', 'Edit WhatsApp Settings')
@section('page-title', 'Edit WhatsApp Settings')

@section('content')
  <div class="max-w-4xl">
    <x-card title="Edit WhatsApp Settings">
      <div class="mb-6">
        <p class="text-sm text-gray-500">Update WhatsApp phone numbers and message template for notifications</p>
      </div>

      <form method="POST" action="{{ route('whatsapp-settings.update', $whatsappSettings) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Basic Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <x-input name="name" label="Settings Name" placeholder="e.g., Default, Emergency, Admin" :value="old('name', $whatsappSettings->name)"
            required hint="Unique name for this WhatsApp settings" />

          <x-input name="description" label="Description" placeholder="Brief description of this settings"
            :value="old('description', $whatsappSettings->description)" hint="Optional description for this WhatsApp settings" />
        </div>

        <!-- Phone Numbers -->
        <div>
          <x-textarea name="phone_numbers" label="Phone Numbers" rows="3"
            placeholder="081234567890, 081234567891, 081234567892" :value="old('phone_numbers', $whatsappSettings->getPhoneNumbersString())" required
            hint="Enter phone numbers separated by commas (e.g., 081234567890, 081234567891)" />
        </div>

        <!-- Message Template -->
        <div>
          <x-textarea name="message_template" label="Message Template" rows="6"
            placeholder="Enter your message template here..." :value="old('message_template', $whatsappSettings->message_template)" required
            hint="Use variables like {branch_name}, {device_name}, {detection_time} in your template" />

          <!-- Template Variables Help -->
          <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-6 mt-4">
            <div class="flex items-center mb-4">
              <div class="flex-shrink-0 h-8 w-8">
                <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                  <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
              </div>
              <div class="ml-3">
                <h4 class="text-sm font-semibold text-blue-900">Available Template Variables</h4>
                <p class="text-xs text-blue-600">Click on any variable to copy it to your template</p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <!-- Detection Info -->
              <div class="space-y-2">
                <h5 class="text-xs font-medium text-blue-800 uppercase tracking-wide">Detection Information</h5>
                <div class="space-y-1">
                  <div
                    class="flex items-center justify-between p-2 bg-white rounded border border-blue-100 hover:bg-blue-50 transition-colors cursor-pointer"
                    onclick="insertVariable('{detection_time}')">
                    <div>
                      <code class="text-sm font-mono text-blue-700 bg-blue-100 px-2 py-1 rounded">{detection_time}</code>
                      <span class="text-xs text-gray-600 ml-2">Detection timestamp</span>
                    </div>
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                  </div>
                  <div
                    class="flex items-center justify-between p-2 bg-white rounded border border-blue-100 hover:bg-blue-50 transition-colors cursor-pointer"
                    onclick="insertVariable('{person_count}')">
                    <div>
                      <code class="text-sm font-mono text-blue-700 bg-blue-100 px-2 py-1 rounded">{person_count}</code>
                      <span class="text-xs text-gray-600 ml-2">Number of people detected</span>
                    </div>
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                  </div>
                  <div
                    class="flex items-center justify-between p-2 bg-white rounded border border-blue-100 hover:bg-blue-50 transition-colors cursor-pointer"
                    onclick="insertVariable('{confidence}')">
                    <div>
                      <code class="text-sm font-mono text-blue-700 bg-blue-100 px-2 py-1 rounded">{confidence}</code>
                      <span class="text-xs text-gray-600 ml-2">Detection confidence</span>
                    </div>
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                  </div>
                </div>
              </div>

              <!-- Location Info -->
              <div class="space-y-2">
                <h5 class="text-xs font-medium text-blue-800 uppercase tracking-wide">Location Information</h5>
                <div class="space-y-1">
                  <div
                    class="flex items-center justify-between p-2 bg-white rounded border border-blue-100 hover:bg-blue-50 transition-colors cursor-pointer"
                    onclick="insertVariable('{branch_name}')">
                    <div>
                      <code class="text-sm font-mono text-blue-700 bg-blue-100 px-2 py-1 rounded">{branch_name}</code>
                      <span class="text-xs text-gray-600 ml-2">Branch name</span>
                    </div>
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                  </div>
                  <div
                    class="flex items-center justify-between p-2 bg-white rounded border border-blue-100 hover:bg-blue-50 transition-colors cursor-pointer"
                    onclick="insertVariable('{device_name}')">
                    <div>
                      <code class="text-sm font-mono text-blue-700 bg-blue-100 px-2 py-1 rounded">{device_name}</code>
                      <span class="text-xs text-gray-600 ml-2">Device name</span>
                    </div>
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                  </div>
                  <div
                    class="flex items-center justify-between p-2 bg-white rounded border border-blue-100 hover:bg-blue-50 transition-colors cursor-pointer"
                    onclick="insertVariable('{location}')">
                    <div>
                      <code class="text-sm font-mono text-blue-700 bg-blue-100 px-2 py-1 rounded">{location}</code>
                      <span class="text-xs text-gray-600 ml-2">Detection location</span>
                    </div>
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Settings Options -->
        <div class="border-t border-gray-200 pt-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Settings Options</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
              <x-checkbox name="is_active" label="Active Settings" :checked="old('is_active', $whatsappSettings->is_active)"
                hint="Enable or disable this WhatsApp settings" />
            </div>

            <div class="space-y-4">
              <x-checkbox name="is_default" label="Set as Default" :checked="old('is_default', $whatsappSettings->is_default)"
                hint="Make this the default WhatsApp settings (will override current default)" />
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
          <x-button variant="secondary" :href="route('whatsapp-settings.show', $whatsappSettings)">
            Cancel
          </x-button>
          <x-button type="submit" variant="primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Update Settings
          </x-button>
        </div>
      </form>
    </x-card>
  </div>
@endsection

@push('scripts')
  <script>
    function insertVariable(variable) {
      const textarea = document.querySelector('textarea[name="message_template"]');
      if (textarea) {
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;
        const before = text.substring(0, start);
        const after = text.substring(end, text.length);

        textarea.value = before + variable + after;
        textarea.focus();
        textarea.setSelectionRange(start + variable.length, start + variable.length);

        // Trigger input event to update any live preview
        textarea.dispatchEvent(new Event('input', {
          bubbles: true
        }));
      }
    }

    // Make function globally available
    window.insertVariable = insertVariable;
  </script>
@endpush
