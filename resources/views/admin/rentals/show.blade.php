{{-- Detail sewa untuk admin: data penyewa, tiket garasi, rincian biaya, dan semua perpanjangan. --}}
@extends('layouts.app')

@section('title', 'Sewa '.$rental->garage->id)

@section('content')
    <x-page-header :title="'Sewa Garasi '.$rental->garage->id" :description="'Disewa oleh '.$rental->vehicle->tenant->full_name.'.'">
        <x-button variant="secondary" :href="route('admin.rentals.index')">Kembali</x-button>
        <x-button :href="route('admin.rentals.extend', $rental)">Perpanjang sewa</x-button>
    </x-page-header>

    <section aria-labelledby="tenant-data" class="grid gap-3">
        <h2 id="tenant-data" class="text-lg">Penyewa</h2>
        <dl class="grid gap-x-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="grid gap-0.5 border-t border-rule py-3">
                <dt class="text-sm text-muted">Nama</dt>
                <dd>{{ $rental->vehicle->tenant->full_name }}</dd>
            </div>
            <div class="grid gap-0.5 border-t border-rule py-3">
                <dt class="text-sm text-muted">NIK</dt>
                <dd class="font-mono">{{ $rental->vehicle->tenant->nik }}</dd>
            </div>
            <div class="grid gap-0.5 border-t border-rule py-3">
                <dt class="text-sm text-muted">Telepon</dt>
                <dd class="num">{{ $rental->vehicle->tenant->phone_number }}</dd>
            </div>
            <div class="grid gap-0.5 border-t border-rule py-3">
                <dt class="text-sm text-muted">Email</dt>
                <dd class="break-all">{{ $rental->vehicle->tenant->email }}</dd>
            </div>
        </dl>
    </section>

    <x-rental-details :rental="$rental" />

    <x-extension-list :extensions="$rental->extensions" />
@endsection
