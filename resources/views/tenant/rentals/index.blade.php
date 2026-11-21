{{-- Riwayat sewa garasi milik penyewa. --}}
@extends('layouts.app')

@section('title', 'Riwayat Sewa')

@section('content')
    <x-page-header title="Riwayat sewa" description="Semua garasi yang pernah dan sedang Anda sewa, terbaru di atas." />

    <x-split-layout aside-label="Aksi riwayat sewa" aside-width="16rem">
        <x-slot:aside>
            <x-button :href="route('tenant.rentals.create')" class="w-full">Sewa Garasi</x-button>
            <dl class="grid gap-1 border-2 border-ink bg-surface p-4 shadow-card">
                <dt class="text-sm text-muted">Jumlah sewa</dt>
                <dd class="num text-2xl font-semibold">{{ $rentals->total() }}</dd>
            </dl>
            <p class="text-sm text-muted">Buka detail sewa untuk melihat tiket garasi dan bukti pelunasan.</p>
        </x-slot:aside>

        <x-table
            :empty="$rentals->isEmpty()"
            empty-title="Belum ada riwayat sewa"
            empty-text="Sewa pertama Anda akan tercatat di sini beserta bukti pelunasannya."
            caption="Riwayat sewa garasi"
        >
            <x-slot:action>
                <x-button variant="secondary" :href="route('tenant.rentals.create')">Sewa Garasi</x-button>
            </x-slot:action>
            <x-slot:head>
                <th scope="col">Garasi</th>
                <th scope="col">Kendaraan</th>
                <th scope="col">Periode</th>
                <th scope="col">Tipe sewa</th>
                <th scope="col" class="text-right">Total biaya</th>
                <th scope="col"><span class="sr-only">Detail</span></th>
            </x-slot:head>
            @foreach ($rentals as $rental)
                <tr>
                    <td class="font-mono font-medium">{{ $rental->garage->id }}</td>
                    <td>
                        {{ $rental->vehicle->brand_model }}
                        <span class="block font-mono text-sm text-muted">{{ $rental->vehicle->license_plate ?? $rental->vehicle->type->label() }}</span>
                    </td>
                    <td class="whitespace-nowrap">
                        <x-date :value="$rental->start_date" />
                        <span class="block text-sm text-muted">sampai <x-date :value="$rental->end_date" /></span>
                    </td>
                    <td><x-badge :status="$rental->rental_type" /></td>
                    <td class="text-right"><x-money :amount="$rental->total_cost" /></td>
                    <td class="text-right"><a href="{{ route('tenant.rentals.show', $rental) }}" class="text-link whitespace-nowrap">Lihat detail</a></td>
                </tr>
            @endforeach
        </x-table>

        {{ $rentals->links() }}
    </x-split-layout>
@endsection
