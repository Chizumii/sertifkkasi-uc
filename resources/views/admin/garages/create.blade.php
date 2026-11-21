{{--
    Form pembuatan garasi massal per tipe. Kode dibuat otomatis berurutan (S/C/E + 3 digit, maksimal 999 per tipe).
    Pratinjau rentang kode dihitung JS di resources/js/app.js dari data-next-code dan data-remaining.
--}}
@extends('layouts.app')

@section('title', 'Buat Garasi')

@section('content')
    @php
        $selectedType = old('type');
    @endphp

    <x-page-header title="Buat garasi" description="Garasi dibuat berkelompok dengan kode berurutan sesuai tipe." />

    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('admin.garages.store') }}" class="grid gap-6" data-garage-batch>
            @csrf
            <x-form-errors />

            <fieldset class="grid gap-2" aria-describedby="type-help">
                <legend class="mb-2 text-sm font-medium text-ink-2">Tipe garasi</legend>
                <div class="grid gap-3 sm:grid-cols-3">
                    @foreach ($garageTypes as $garageType)
                        <div class="relative">
                            <input
                                type="radio"
                                id="type-{{ $garageType->value }}"
                                name="type"
                                value="{{ $garageType->value }}"
                                class="key-input"
                                required
                                @checked($selectedType === $garageType->value)
                                data-next-code="{{ $nextCodes[$garageType->value] ?? '' }}"
                                data-remaining="{{ $remaining[$garageType->value] ?? 0 }}"
                                @disabled(($remaining[$garageType->value] ?? 0) < 1)
                            >
                            <label for="type-{{ $garageType->value }}" class="key tint-{{ $garageType->value }} grid gap-1">
                                <span class="font-medium">{{ $garageType->label() }}</span>
                                <span class="text-sm text-ink-2">Kode berikutnya <span class="font-mono">{{ $nextCodes[$garageType->value] ?? '-' }}</span></span>
                                <span class="num text-sm text-ink-2">Sisa kuota {{ $remaining[$garageType->value] ?? 0 }}</span>
                            </label>
                        </div>
                    @endforeach
                </div>
                <p id="type-help" class="min-h-lh text-sm {{ $errors->has('type') ? 'text-error' : 'text-muted' }}">
                    @error('type')
                        {{ $message }}
                    @else
                        Setiap tipe menampung maksimal 999 garasi.
                    @enderror
                </p>
            </fieldset>

            <div class="grid gap-x-6 gap-y-2 sm:grid-cols-2">
                <x-input name="quantity" type="number" label="Jumlah garasi" min="1" max="999" inputmode="numeric" placeholder="10" required data-garage-count />

                <x-input name="daily_rate" type="number" label="Harga sewa harian (Rp)" min="0" step="500" inputmode="numeric" placeholder="50000" help="Harga bulanan otomatis 25 kali harga harian." required />

                <div data-depends-on="type" data-show-values="electric" @if ($selectedType !== 'electric') hidden @endif>
                    <x-input name="kwh_rate" type="number" label="Harga isi listrik per kWh (Rp)" min="0" step="1" inputmode="numeric" placeholder="2500" help="Wajib untuk garasi mobil listrik." required :disabled="$selectedType !== 'electric'" />
                </div>
            </div>

            <div class="rounded-input bg-paper-3 px-4 py-3" aria-live="polite">
                <p data-garage-range>Pilih tipe dan isi jumlah untuk melihat kode yang akan dibuat.</p>
                <p class="text-error" data-garage-quota-warning hidden></p>
            </div>

            <div class="flex flex-wrap gap-3">
                <x-button>Buat garasi</x-button>
                <x-button variant="secondary" :href="route('admin.garages.index')">Batal</x-button>
            </div>
        </form>
    </x-card>
@endsection
