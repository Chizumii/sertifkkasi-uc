{{-- Dashboard admin: ringkasan angka hari ini dan pintasan ke pekerjaan utama. --}}
@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    <x-page-header title="Dashboard admin" description="Keadaan garasi dan pendaftaran pada hari ini." />

    <dl class="grid grid-cols-2 lg:grid-cols-4">
        <div class="grid gap-1 border-t border-rule py-4 pr-4">
            <dt class="text-sm text-muted">Pendaftar menunggu</dt>
            <dd class="num text-3xl font-semibold">{{ $pendingUsersCount }}</dd>
        </div>
        <div class="grid gap-1 border-t border-l border-rule py-4 pl-4 lg:pr-4">
            <dt class="text-sm text-muted">Sewa aktif hari ini</dt>
            <dd class="num text-3xl font-semibold">{{ $activeRentalsCount }}</dd>
        </div>
        <div class="grid gap-1 border-t border-rule py-4 pr-4 lg:border-l lg:pl-4">
            <dt class="text-sm text-muted">Garasi tersedia</dt>
            <dd class="num text-3xl font-semibold">{{ $availableGaragesCount }}</dd>
        </div>
        <div class="grid gap-1 border-t border-l border-rule py-4 pl-4">
            <dt class="text-sm text-muted">Garasi terpakai</dt>
            <dd class="num text-3xl font-semibold">{{ $usedGaragesCount }}</dd>
        </div>
    </dl>

    <section aria-labelledby="shortcuts" class="grid gap-4">
        <h2 id="shortcuts" class="text-xl">Pekerjaan admin</h2>
        <ul class="grid border-b border-rule">
            <li class="flex flex-wrap items-center justify-between gap-3 border-t border-rule py-4">
                <div class="grid gap-0.5">
                    <p class="font-medium">Verifikasi pendaftar baru</p>
                    <p class="text-sm text-muted">Cocokkan NIK dengan KTP, lalu terima atau tolak.</p>
                </div>
                <x-button :href="route('admin.users.index', ['status' => 'pending'])">Periksa {{ $pendingUsersCount }} pendaftar</x-button>
            </li>
            <li class="flex flex-wrap items-center justify-between gap-3 border-t border-rule py-4">
                <div class="grid gap-0.5">
                    <p class="font-medium">Perpanjang sewa</p>
                    <p class="text-sm text-muted">Ubah tanggal selesai dan catat bukti bayar tambahan.</p>
                </div>
                <x-button variant="secondary" :href="route('admin.rentals.index')">Lihat sewa aktif</x-button>
            </li>
            <li class="flex flex-wrap items-center justify-between gap-3 border-t border-rule py-4">
                <div class="grid gap-0.5">
                    <p class="font-medium">Tambah garasi</p>
                    <p class="text-sm text-muted">Buat sekaligus banyak garasi sepeda, mobil, atau mobil listrik.</p>
                </div>
                <x-button variant="secondary" :href="route('admin.garages.create')">Buat garasi</x-button>
            </li>
            <li class="flex flex-wrap items-center justify-between gap-3 border-t border-rule py-4">
                <div class="grid gap-0.5">
                    <p class="font-medium">Laporan sewa</p>
                    <p class="text-sm text-muted">Pendapatan per bulan, per hari, per tipe garasi, atau per penyewa.</p>
                </div>
                <x-button variant="secondary" :href="route('admin.reports.index')">Buka laporan</x-button>
            </li>
        </ul>
    </section>
@endsection
