{{-- Riwayat sewa garasi milik penyewa: semua informasi sewa, bukti bayar, dan perpanjangan dalam satu tabel. --}}
@extends('layouts.app')

@use('Illuminate\Support\Facades\Storage')

@section('title', 'Riwayat Sewa')

@section('content')
    <x-page-header title="Riwayat sewa" description="Semua garasi yang pernah dan sedang Anda sewa, terbaru di atas. Simpan halaman ini sebagai bukti sewa.">
        <x-button :href="route('tenant.rentals.create')">Sewa Garasi</x-button>
    </x-page-header>

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
            <th scope="col">Tipe dan status</th>
            <th scope="col" class="text-right">Total biaya</th>
            <th scope="col">Bukti bayar</th>
            <th scope="col">Perpanjangan</th>
        </x-slot:head>
        @foreach ($rentals as $rental)
            <tr>
                <td>
                    <span class="font-mono font-medium">{{ $rental->garage->id }}</span>
                    <span class="block text-sm text-muted">{{ $rental->garage->type->label() }}</span>
                </td>
                <td>
                    {{ $rental->vehicle->brand_model }}
                    <span class="block font-mono text-sm text-muted">{{ $rental->vehicle->license_plate ?? $rental->vehicle->type->label() }}</span>
                </td>
                <td class="whitespace-nowrap">
                    <x-date :value="$rental->start_date" />
                    <span class="block text-sm text-muted">sampai <x-date :value="$rental->end_date" /></span>
                    <span class="num block text-sm text-muted">{{ $rental->days_count }} hari awal</span>
                </td>
                <td>
                    <div class="grid justify-items-start gap-1">
                        <x-badge :status="$rental->rental_type" />
                        <x-badge :status="$rental->status" />
                    </div>
                </td>
                <td class="text-right"><x-money :amount="$rental->total_cost" /></td>
                <td>
                    <a href="{{ Storage::disk('public')->url($rental->payment_proof) }}" target="_blank" rel="noopener" class="text-link whitespace-nowrap">Lihat bukti</a>
                </td>
                <td>
                    @forelse ($rental->extensions as $extension)
                        <div class="grid gap-0.5 border-b border-rule py-1 text-sm last:border-b-0">
                            <span class="whitespace-nowrap">Sampai <x-date :value="$extension->new_end_date" /></span>
                            <span><x-money :amount="$extension->remaining_cost" /></span>
                            <a href="{{ Storage::disk('public')->url($extension->payment_proof) }}" target="_blank" rel="noopener" class="text-link whitespace-nowrap">Bukti bayar perpanjangan</a>
                        </div>
                    @empty
                        <span class="text-sm text-muted">Belum pernah</span>
                    @endforelse
                </td>
            </tr>
        @endforeach
    </x-table>

    {{ $rentals->links() }}
@endsection
