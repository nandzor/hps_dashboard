@extends('layouts.app')

@section('title', 'Edit Company Branch')
@section('page-title', 'Edit Company Branch')

@section('content')
  <div class="max-w-3xl">
    <x-card title="Update Branch Information">
      <div class="mb-6">
        <div class="flex items-center justify-between">
          <p class="text-sm text-gray-500">Modify the branch details below</p>
          <x-badge :variant="$companyBranch->status === 'active' ? 'success' : 'danger'">
            {{ ucfirst($companyBranch->status) }}
          </x-badge>
        </div>
      </div>

      <form method="POST" action="{{ route('company-branches.update', $companyBranch) }}" class="space-y-5">
        @csrf
        @method('PUT')

        <x-company-group-select name="group_id" :value="$companyBranch->group_id" label="Company Group" required
          hint="Select the company group this branch belongs to" />

        <x-input name="branch_code" label="Branch Code" :value="$companyBranch->branch_code" placeholder="e.g., JKT001" required
          hint="Unique code identifier for the branch (no spaces allowed)" onkeypress="return event.charCode != 32" />

        <x-input name="branch_name" label="Branch Name" :value="$companyBranch->branch_name" placeholder="e.g., Jakarta Central Branch"
          required hint="Descriptive name for the branch" />

        <x-input name="city_name" label="City Name" :value="$companyBranch->city_name" placeholder="e.g., Central Jakarta" required
          hint="City where the branch is located" />

        <x-textarea name="address" label="Address" :value="$companyBranch->address" placeholder="Full address of the branch"
          rows="3" hint="Complete address information" />

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <x-input type="tel" name="phone" label="Phone" :value="$companyBranch->phone" placeholder="+62812345678"
            hint="Contact phone number" />
          <x-input type="email" name="email" label="Email" :value="$companyBranch->email" placeholder="branch@company.com"
            hint="Contact email address" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <x-input type="number" name="latitude" label="Latitude" :value="$companyBranch->latitude" step="0.00000001"
            placeholder="-6.200000" hint="Geographic latitude coordinate" />
          <x-input type="number" name="longitude" label="Longitude" :value="$companyBranch->longitude" step="0.00000001"
            placeholder="106.816666" hint="Geographic longitude coordinate" />
        </div>

        <x-status-select name="status" label="Status" :value="$companyBranch->status" required hint="Branch status"
          :showAllOption="false" />

        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
          <x-button variant="secondary" :href="route('company-branches.show', $companyBranch)">
            Cancel
          </x-button>
          <x-button type="submit" variant="primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Update Branch
          </x-button>
        </div>
      </form>
    </x-card>
  </div>
@endsection
