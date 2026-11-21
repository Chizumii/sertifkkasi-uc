{{-- Daftar sewa yang aktif hari ini, titik masuk untuk perpanjangan. --}}
@extends('layouts.app')

@section('title', 'Sewa Aktif')

@section('content')
    <x-page-header title="Sewa aktif hari ini" description="Pilih sewa yang ingin diperpanjang. Sisa hari dihitung sampai tanggal selesai." />

    <x-table
        :empty="$rentals->isEmpty()"
        empty-title="Tidak ada sewa yang aktif hari ini"
        empty-text="Sewa yang sedang berjalan muncul di sini dan bisa diperpanjang tanpa batas hari."
        caption="Sewa aktif hari ini"
    >
        <x-slot:head>
            <th scope="col">Garasi</th>
            <th scope="col">Penyewa</th>
            <th scope="col">Kendaraan</th>
            <th scope="col">Periode</th>
            <th scope="col" class="text-right">Sisa hari</th>
            <th scope="col"><span class="sr-only">Aksi</span></th>
        </x-slot:head>
        @foreach ($rentals as $rental)
            <tr>
                <td class="font-mono font-medium">{{ $rental->garage->id }}</td>
                <td>
                    {{ $rental->vehicle->tenant->full_name }}
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
                <td class="num text-right whitespace-nowrap">{{ (int) today()->diffInDays($rental->end_date) }} hari</td>
                <td>
                    <div class="flex justify-end gap-2">
                        <x-button variant="secondary" :href="route('admin.rentals.show', $rental)">Detail</x-button>
                        <x-button variant="secondary" :href="route('admin.rentals.extend', $rental)">Perpanjang</x-button>
                    </div>
                </td>
            </tr>
        @endforeach
    </x-table>

    {{ $rentals->links() }}
@endsection
