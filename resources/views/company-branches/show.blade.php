@extends('layouts.app')

@section('title', 'Branch Details')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $branch->branch_name }}</h1>
            <p class="mt-2 text-gray-600">{{ $branch->city_name }}</p>
        </div>
        <div class="flex space-x-3">
            <x-button variant="warning" href="{{ route('company-branches.edit', $branch) }}">Edit</x-button>
            <x-button variant="danger" @click="confirmDelete({{ $branch->id }})">Delete</x-button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <x-card title="Branch Information">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Branch Code</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $branch->branch_code }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Company Group</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $branch->group->group_name ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Phone</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $branch->phone ?: 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $branch->email ?: 'N/A' }}</dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Address</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $branch->address ?: 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">GPS Coordinates</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if($branch->latitude && $branch->longitude)
                                {{ $branch->latitude }}, {{ $branch->longitude }}
                            @else
                                N/A
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                            <x-badge :variant="$branch->status === 'active' ? 'success' : 'danger'">
                                {{ ucfirst($branch->status) }}
                            </x-badge>
                        </dd>
                    </div>
                </dl>
            </x-card>
        </div>

        <div class="space-y-4">
            <x-stat-card
                title="Total Devices"
                :value="$deviceCounts['total']"
                color="blue"
                icon="<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'/>"
            />
            <x-stat-card
                title="Active Devices"
                :value="$deviceCounts['active']"
                color="green"
                icon="<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'/>"
            />
        </div>
    </div>

    <div class="mt-8">
        <x-card title="Devices" :padding="false">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Device ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Device Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($branch->devices as $device)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $device->device_id }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $device->device_name }}</td>
                            <td class="px-6 py-4">
                                <x-badge :variant="$device->device_type === 'cctv' ? 'primary' : ($device->device_type === 'node_ai' ? 'purple' : ($device->device_type === 'mikrotik' ? 'success' : 'gray'))">
                                    {{ ucfirst(str_replace('_', ' ', $device->device_type)) }}
                                </x-badge>
                            </td>
                            <td class="px-6 py-4">
                                <x-badge :variant="$device->status === 'active' ? 'success' : 'danger'">
                                    {{ ucfirst($device->status) }}
                                </x-badge>
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <a href="{{ route('device-masters.show', $device) }}" class="text-blue-600 hover:text-blue-900">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-400">No devices found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-card>
    </div>

    <div class="mt-6">
        <a href="{{ route('company-branches.index') }}" class="text-blue-600 hover:text-blue-800">← Back to Branches</a>
    </div>
</div>

<x-confirm-modal id="confirm-delete" title="Delete Branch" message="Delete this branch and all devices?" />
<script>
    function confirmDelete(id) {
        window.dispatchEvent(new CustomEvent('open-modal-confirm-delete'));
    }
    window.addEventListener('confirm-confirm-delete', function() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/company-branches/{{ $branch->id }}`;
        form.innerHTML = `@csrf @method('DELETE')`;
        document.body.appendChild(form);
        form.submit();
    });
</script>
@endsection

