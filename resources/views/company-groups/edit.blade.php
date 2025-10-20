@extends('layouts.app')

@section('title', 'Edit Company Group')
@section('page-title', 'Edit Company Group')

@section('content')
  <div class="max-w-3xl">
    <x-card title="Update Group Information">
      <div class="mb-6">
        <div class="flex items-center justify-between">
          <p class="text-sm text-gray-500">Modify the group details below</p>
          <x-badge :variant="$companyGroup->status === 'active' ? 'success' : 'danger'">
            {{ ucfirst($companyGroup->status) }}
          </x-badge>
        </div>
      </div>

      <form method="POST" action="{{ route('company-groups.update', $companyGroup) }}" class="space-y-5">
        @csrf
        @method('PUT')

        <x-input name="province_code" label="Province Code" :value="$companyGroup->province_code" placeholder="e.g., JB (Jawa Barat)" required
          hint="Short code identifier for the province (no spaces allowed)" onkeypress="return event.charCode != 32" />

        <x-input name="province_name" label="Province Name" :value="$companyGroup->province_name" placeholder="e.g., Jawa Barat" required
          hint="Full name of the province" />

        <x-input name="group_name" label="Group Name" :value="$companyGroup->group_name" placeholder="e.g., PT. Company Name" required
          hint="Company group name" />

        <x-textarea name="address" label="Address" :value="$companyGroup->address" placeholder="Full address of the company group"
          rows="3" hint="Complete address information" />

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <x-input type="tel" name="phone" label="Phone" :value="$companyGroup->phone" placeholder="e.g., +62812345678"
            hint="Contact phone number" />
          <x-input type="email" name="email" label="Email" :value="$companyGroup->email" placeholder="e.g., contact@company.com"
            hint="Contact email address" />
        </div>

        <x-status-select name="status" label="Status" :value="$companyGroup->status" required hint="Group status" :showAllOption="false" />

        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
          <x-button variant="secondary" :href="route('company-groups.show', $companyGroup)">
            Cancel
          </x-button>
          <x-button type="submit" variant="primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Update Group
          </x-button>
        </div>
      </form>
    </x-card>
  </div>
@endsection
