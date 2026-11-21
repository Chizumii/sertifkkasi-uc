{{-- Detail satu sewa milik penyewa: tiket garasi, rincian biaya, bukti bayar, dan riwayat perpanjangan. --}}
@extends('layouts.app')

@section('title', 'Sewa '.$rental->garage->id)

@section('content')
    <x-page-header :title="'Sewa garasi '.$rental->garage->id" description="Simpan halaman ini sebagai bukti sewa Anda.">
        <x-button variant="secondary" :href="route('tenant.rentals.index')">Kembali ke riwayat</x-button>
    </x-page-header>

    <x-rental-details :rental="$rental" />

    <x-extension-list :extensions="$rental->extensions" />
@endsection
