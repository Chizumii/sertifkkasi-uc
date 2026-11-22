{{-- Tabel sewa yang berjalan hari ini dan yang akan datang beserta bukti bayar. Semua sewa di tabel ini bisa diperpanjang. --}}
@extends('layouts.app')

@use('Illuminate\Support\Facades\Storage')
@use('App\Enums\RentalStatus')

@section('title', 'Sewa Aktif')

@section('content')
    <x-page-header title="Sewa aktif dan terjadwal" description="Sewa yang sedang berjalan dan yang akan datang. Pilih sewa yang ingin diperpanjang." />

    <x-table
        :empty="$rentals->isEmpty()"
        empty-title="Tidak ada sewa yang aktif atau terjadwal"
        empty-text="Sewa yang sedang berjalan dan yang akan datang muncul di sini."
        caption="Sewa aktif dan terjadwal"
    >
        <x-slot:head>
            <th scope="col">Garasi</th>
            <th scope="col">Penyewa</th>
            <th scope="col">Kendaraan</th>
            <th scope="col">Periode</th>
            <th scope="col">Status</th>
            <th scope="col" class="text-right">Sisa hari</th>
            <th scope="col" class="text-right">Total biaya</th>
            <th scope="col">Bukti bayar</th>
            <th scope="col"><span class="sr-only">Aksi</span></th>
        </x-slot:head>
        @foreach ($rentals as $rental)
            <tr>
                <td class="font-mono font-medium">{{ $rental->garage->id }}</td>
                <td>
                    {{ $rental->vehicle->tenant->full_name }}
                    <span class="block font-mono text-sm text-muted">{{ $rental->vehicle->tenant->nik }}</span>
                    <span class="block num text-sm text-muted">{{ $rental->vehicle->tenant->phone_number }}</span>
                </td>
                <td>
                    {{ $rental->vehicle->brand_model }}
                    <span class="block font-mono text-sm text-muted">{{ $rental->vehicle->license_plate ?? $rental->vehicle->type->label() }}</span>
                </td>
                <td class="whitespace-nowrap">
                    <x-date :value="$rental->start_date" />
                    <span class="block text-sm text-muted">sampai <x-date :value="$rental->end_date" /></span>
                </td>
                <td><x-badge :status="RentalStatus::forPeriod($rental->start_date, $rental->end_date, today())" /></td>
                <td class="num text-right whitespace-nowrap">
                    @if ($rental->isActiveToday())
                        {{ (int) today()->diffInDays($rental->end_date) }} hari
                    @else
                        <span class="text-muted">mulai {{ (int) today()->diffInDays($rental->start_date) }} hari lagi</span>
                    @endif
                </td>
                <td class="text-right"><x-money :amount="$rental->total_cost" /></td>
                <td class="text-sm whitespace-nowrap">
                    <a href="{{ Storage::disk('public')->url($rental->payment_proof) }}" target="_blank" rel="noopener" class="text-link">Sewa</a>
                    @foreach ($rental->extensions as $extension)
                        <a href="{{ Storage::disk('public')->url($extension->payment_proof) }}" target="_blank" rel="noopener" class="text-link block">Perpanjangan {{ $loop->iteration }}</a>
                    @endforeach
                </td>
                <td>
                    <x-button variant="secondary" :href="route('admin.rentals.extend', $rental)">Perpanjang</x-button>
                </td>
            </tr>
        @endforeach
    </x-table>

    {{ $rentals->links() }}
@endsection
