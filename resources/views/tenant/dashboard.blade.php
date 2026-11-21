{{-- Dashboard penyewa: status verifikasi, ringkasan, dan tiket garasi yang sedang aktif. --}}
@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        $greeting = new \Illuminate\Support\HtmlString('Halo, <span class="text-accent-deep">'.e($user->full_name).'</span>');
    @endphp

    <x-page-header :title="$greeting" description="Garasi yang sedang Anda sewa ada di bawah ini.">
        @if ($user->isApproved())
            <x-button :href="route('tenant.rentals.create')">Sewa Garasi</x-button>
        @else
            <x-button type="button" disabled aria-describedby="rent-disabled-reason">Sewa Garasi</x-button>
        @endif
    </x-page-header>

    @unless ($user->isApproved())
        <x-alert type="info" id="rent-disabled-reason">
            Penyewaan belum bisa dilakukan karena akun Anda berstatus <strong>{{ strtolower($user->verification_status->label()) }}</strong>. Admin perlu menerima pendaftaran Anda terlebih dahulu.
        </x-alert>
    @endunless

    <dl class="grid grid-cols-2 sm:grid-cols-3">
        <div class="grid gap-1 border-t border-rule py-4 pr-4">
            <dt class="text-sm text-muted">Status akun</dt>
            <dd><x-badge :status="$user->verification_status" /></dd>
        </div>
        <div class="grid gap-1 border-t border-l border-rule px-4 py-4">
            <dt class="text-sm text-muted">Sewa aktif</dt>
            <dd class="num text-2xl font-semibold">{{ $activeRentals->count() }}</dd>
        </div>
        <div class="col-span-2 grid gap-1 border-t border-rule py-4 sm:col-span-1 sm:border-l sm:pl-4">
            <dt class="text-sm text-muted">Kendaraan terdaftar</dt>
            <dd class="num text-2xl font-semibold">{{ $vehicleCount }}</dd>
        </div>
    </dl>

    <section aria-labelledby="active-rentals" class="grid gap-4">
        <h2 id="active-rentals" class="text-xl">Garasi yang sedang disewa</h2>

        @if ($activeRentals->isEmpty())
            <x-table
                :empty="true"
                empty-title="Tidak ada garasi yang sedang Anda sewa"
                empty-text="Setelah sewa tersimpan, tiket garasinya muncul di sini lengkap dengan tanggal selesai."
            >
                @if ($user->isApproved())
                    <x-slot:action>
                        <x-button variant="secondary" :href="route('tenant.rentals.create')">Pilih garasi</x-button>
                    </x-slot:action>
                @endif
            </x-table>
        @else
            <ul class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($activeRentals as $rental)
                    <li class="ticket">
                        <a href="{{ route('tenant.rentals.show', $rental) }}" class="grid gap-3 p-5 no-underline">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm text-muted">{{ $rental->garage->type->label() }}</p>
                                    <p class="font-mono text-3xl font-medium">{{ $rental->garage->id }}</p>
                                </div>
                                <x-badge :status="$rental->rental_type" />
                            </div>
                            <p>{{ $rental->vehicle->brand_model }}<span class="block font-mono text-sm text-muted">{{ $rental->vehicle->license_plate ?? 'Tanpa plat' }}</span></p>
                        </a>
                        <div class="ticket-stub flex justify-between gap-3 px-5 py-3 text-sm">
                            <span class="text-muted">Selesai</span>
                            <x-date :value="$rental->end_date" />
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
@endsection
