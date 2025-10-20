@extends('layouts.app')

@section('title', 'Create HPS Elektronik')
@section('page-title', 'Create HPS Elektronik')

@section('content')
  <x-card>
    <div class="p-6">
      <form method="POST" action="{{ route('hps-elektronik.store') }}" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Wilayah -->
          <x-select 
            name="kdwilayah" 
            label="Wilayah" 
            :options="$filterOptions['wilayah']" 
            placeholder="Select Wilayah"
            required />

          <!-- Jenis Barang -->
          <x-select 
            name="jenis_barang" 
            label="Jenis Barang" 
            :options="$filterOptions['jenis_barang']" 
            placeholder="Select Jenis Barang"
            required />

          <!-- Merek -->
          <x-select 
            name="merek" 
            label="Merek" 
            :options="$filterOptions['merek']" 
            placeholder="Select Merek"
            required />

          <!-- Barang -->
          <x-input 
            name="barang" 
            label="Barang" 
            type="text"
            placeholder="Enter barang name"
            required />

          <!-- Tahun -->
          <x-select 
            name="tahun" 
            label="Tahun" 
            :options="$filterOptions['tahun']" 
            placeholder="Select Tahun"
            required />

          <!-- Harga -->
          <x-input 
            name="harga" 
            label="Harga (Rp)" 
            type="number"
            placeholder="Enter harga"
            min="0"
            step="0.01"
            required />

          <!-- Grade -->
          <x-select 
            name="grade" 
            label="Grade" 
            :options="$filterOptions['grade']" 
            placeholder="Select Grade" />

          <!-- Active Status -->
          <div class="flex items-center">
            <x-checkbox 
              name="active" 
              label="Active" 
              :checked="true" />
          </div>
        </div>

        <!-- Kondisi -->
        <x-textarea 
          name="kondisi" 
          label="Kondisi" 
          placeholder="Enter kondisi description"
          :rows="3" />

        <!-- Buttons -->
        <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
          <x-button variant="secondary" :href="route('hps-elektronik.index')">
            <x-icon name="x" class="w-4 h-4 mr-2" />
            Cancel
          </x-button>
          <x-button variant="primary" type="submit">
            <x-icon name="plus" class="w-4 h-4 mr-2" />
            Create HPS Elektronik
          </x-button>
        </div>
      </form>
    </div>
  </x-card>
@endsection