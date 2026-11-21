{{--
    Form ringkas tambah kendaraan di halaman Sewa Garasi.
    Plat disembunyikan untuk sepeda, kapasitas baterai hanya untuk mobil listrik (diatur resources/js/app.js).
    Setelah disimpan, penyewa kembali ke form sewa dengan kendaraan baru terpilih.
--}}
@props(['variant' => 'secondary'])

@use('App\Enums\VehicleType')

@php
    $selectedType = old('type');
@endphp

<form method="POST" action="{{ route('tenant.vehicles.store') }}" {{ $attributes->class('grid gap-x-6 gap-y-2 @lg:grid-cols-2') }}>
    @csrf

    <x-select name="type" label="Tipe kendaraan" placeholder="Pilih tipe kendaraan" :selected="$selectedType" required>
        @foreach (VehicleType::cases() as $vehicleType)
            <option value="{{ $vehicleType->value }}" @selected($selectedType === $vehicleType->value)>{{ $vehicleType->label() }}</option>
        @endforeach
    </x-select>

    <x-input name="brand_model" label="Merek dan model" placeholder="Toyota Avanza" required />

    <div data-depends-on="type" data-hide-values="bicycle" @if ($selectedType === 'bicycle') hidden @endif>
        <x-input name="license_plate" label="Nomor plat" placeholder="L 1234 AB" autocomplete="off" help="Sepeda tidak memerlukan nomor plat." required :disabled="$selectedType === 'bicycle'" />
    </div>

    <div data-depends-on="type" data-show-values="electricCar" @if ($selectedType !== 'electricCar') hidden @endif>
        <x-input name="battery_kwh" type="number" label="Kapasitas baterai penuh (kWh)" inputmode="decimal" min="0.01" step="0.01" placeholder="60.5" help="Lihat spesifikasi pabrikan mobil listrik." required :disabled="$selectedType !== 'electricCar'" />
    </div>

    <div class="@lg:col-span-2">
        <x-button :variant="$variant">Simpan kendaraan</x-button>
    </div>
</form>
