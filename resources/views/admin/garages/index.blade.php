{{-- Daftar garasi: tombol buat dan filter tipe (dengan jumlah per tipe) di kiri, tabel garasi di kanan. --}}
@extends('layouts.app')

@use('App\Enums\GarageType')

@section('title', 'Garasi')

@section('content')
    <x-page-header title="Garasi" description="Harga bulanan selalu 25 kali harga harian." />

    <x-split-layout aside-label="Filter dan aksi garasi">
        <x-slot:aside>
            <x-button :href="route('admin.garages.create')" class="w-full">Buat garasi</x-button>

            <nav aria-label="Filter tipe garasi" class="grid gap-2">
                <p class="text-sm font-medium text-ink-2">Tipe garasi</p>
                <ul class="tab-stack grid">
                    @foreach ([null, ...GarageType::cases()] as $filterType)
                        @php
                            $filterValue = $filterType?->value;
                            $filterTotal = $filterType
                                ? ($counts[$filterValue]['total'] ?? 0)
                                : collect($counts)->sum('total');
                            $filterUsed = $filterType
                                ? ($counts[$filterValue]['used'] ?? 0)
                                : collect($counts)->sum('used');
                        @endphp
                        <li>
                            <a
                                href="{{ route('admin.garages.index', $filterValue ? ['type' => $filterValue] : []) }}"
                                class="tab py-2"
                                @if (($type ?? null) === $filterValue) aria-current="page" @endif
                            >
                                <span>{{ $filterType?->label() ?? 'Semua garasi' }}</span>
                                <span class="num text-right text-sm text-muted">{{ $filterTotal }} <span class="sr-only">garasi,</span>&middot; {{ $filterUsed }} terpakai</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        </x-slot:aside>

        <x-table
            :empty="$garages->isEmpty()"
            empty-title="Belum ada garasi untuk tipe ini"
            empty-text="Garasi dibuat berkelompok, kodenya berurutan otomatis mulai dari 001."
            caption="Daftar garasi"
        >
            <x-slot:action>
                <x-button variant="secondary" :href="route('admin.garages.create')">Buat garasi</x-button>
            </x-slot:action>
            <x-slot:head>
                <th scope="col">Kode</th>
                <th scope="col">Tipe</th>
                <th scope="col" class="text-right">Harga harian</th>
                <th scope="col" class="text-right">Harga bulanan</th>
                <th scope="col" class="text-right">Harga per kWh</th>
                <th scope="col">Status</th>
            </x-slot:head>
            @foreach ($garages as $garage)
                <tr>
                    <td class="font-mono font-medium">{{ $garage->id }}</td>
                    <td>{{ $garage->type->label() }}</td>
                    <td class="text-right"><x-money :amount="$garage->daily_rate" /></td>
                    <td class="text-right"><x-money :amount="$garage->monthlyPrice()" /></td>
                    <td class="text-right">
                        @if ($garage->kwh_rate)
                            <x-money :amount="$garage->kwh_rate" />
                        @else
                            -
                        @endif
                    </td>
                    <td><x-badge :status="$garage->status" /></td>
                </tr>
            @endforeach
        </x-table>

        {{ $garages->links() }}
    </x-split-layout>
@endsection
