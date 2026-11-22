{{--
    Form perpanjangan sewa oleh admin. Kolom kiri: langkah form; kolom kanan: data sewa dan rincian biaya.
    Langkah 1 (GET "Hitung"): pilih tanggal selesai baru, sistem mengirim $preview berisi rincian biaya.
    Langkah 2 (POST): unggah bukti bayar biaya perpanjangan, hanya aktif bila $preview tersedia.
--}}
@extends('layouts.app')

@section('title', 'Perpanjang Sewa '.$rental->garage->id)

@section('content')
    <x-page-header :title="'Perpanjang sewa '.$rental->garage->id" description="Tidak ada batas hari. Biaya hanya untuk hari tambahan setelah tanggal selesai sekarang.">
        <x-button variant="secondary" :href="route('admin.rentals.index')">Kembali ke daftar sewa</x-button>
    </x-page-header>

    <x-form-errors />

    <x-split-layout aside-label="Form perpanjangan" aside-width="22rem">
        <x-slot:aside>
            <x-card title="1. Tanggal selesai baru" description="Pilih tanggal, lalu tekan Hitung.">
                <form method="GET" action="{{ route('admin.rentals.extend', $rental) }}" class="grid gap-2">
                    <x-input name="new_end_date" type="date" label="Tanggal selesai baru" :value="$preview['new_end_date'] ?? null" :min="$minNewEndDate" help="Paling cepat sehari setelah tanggal selesai sekarang." required />
                    <x-button variant="secondary" class="w-full">Hitung</x-button>
                </form>
            </x-card>

            <x-card title="2. Bukti bayar" :description="$preview ? null : 'Aktif setelah biaya perpanjangan dihitung.'">
                <form method="POST" action="{{ route('admin.rentals.extend.store', $rental) }}" enctype="multipart/form-data" class="grid gap-2">
                    @csrf
                    <input type="hidden" name="new_end_date" value="{{ $preview['new_end_date'] ?? '' }}">
                    <x-file-input name="payment_proof" label="Bukti bayar" required :disabled="! $preview" />
                    <x-button :disabled="! $preview" class="w-full">Simpan perpanjangan</x-button>
                </form>
            </x-card>
        </x-slot:aside>

        <section aria-labelledby="current-rental" class="grid gap-3">
            <h2 id="current-rental" class="text-lg">Sewa saat ini</h2>
            <dl class="grid gap-x-6 @lg:grid-cols-2">
                <div class="grid gap-0.5 border-t border-rule py-3">
                    <dt class="text-sm text-muted">Kendaraan</dt>
                    <dd>{{ $rental->vehicle->brand_model }} <span class="font-mono text-sm text-muted">{{ $rental->vehicle->license_plate }}</span></dd>
                </div>
                <div class="grid gap-0.5 border-t border-rule py-3">
                    <dt class="text-sm text-muted">Tanggal mulai</dt>
                    <dd><x-date :value="$rental->start_date" /></dd>
                </div>
                <div class="grid gap-0.5 border-t border-rule py-3">
                    <dt class="text-sm text-muted">Tanggal selesai sekarang</dt>
                    <dd><x-date :value="$rental->end_date" /></dd>
                </div>
                <div class="grid gap-0.5 border-t border-rule py-3">
                    <dt class="text-sm text-muted">Total biaya sekarang</dt>
                    <dd><x-money :amount="$rental->total_cost" /></dd>
                </div>
            </dl>
            @if ($rental->extensions->isNotEmpty())
                <p class="text-sm text-muted">Sudah diperpanjang {{ $rental->extensions->count() }} kali.</p>
            @endif
        </section>

        <section aria-labelledby="extension-cost" class="grid gap-3" aria-live="polite">
            <h2 id="extension-cost" class="text-lg">Biaya perpanjangan</h2>
            @if ($preview)
                <dl class="grid border-2 border-ink bg-surface p-5 shadow-card">
                    <div class="flex justify-between gap-4 py-2">
                        <dt>Periode tambahan</dt>
                        <dd class="text-right"><x-date :value="$preview['old_end_date']" /> sampai <x-date :value="$preview['new_end_date']" /></dd>
                    </div>
                    <div class="flex justify-between gap-4 border-t border-rule py-2">
                        <dt>Lama perpanjangan</dt>
                        <dd class="num">{{ $preview['extension_days'] }} hari, {{ strtolower($preview['rental_type_label']) }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-t border-rule py-2">
                        <dt>Perhitungan</dt>
                        <dd class="num text-right">
                            @if ($preview['months'] === null)
                                {{ $preview['extension_days'] }} hari &times; <x-money :amount="$preview['daily_rate']" />
                            @else
                                {{ $preview['months'] }} bulan &times; {{ \App\Models\Garage::MONTHLY_PRICE_MULTIPLIER }} &times; <x-money :amount="$preview['daily_rate']" />
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4 border-t border-ink-2 pt-3 text-lg font-medium">
                        <dt>Biaya perpanjangan</dt>
                        <dd class="text-accent-deep"><x-money :amount="$preview['additional_cost']" /></dd>
                    </div>
                </dl>
                <p class="text-sm text-muted">Dihitung dari tanggal selesai lama. Bila hari tambahan mencapai jumlah hari bulan tersebut, dihitung bulanan (25 &times; harga harian per bulan).</p>
            @else
                <p class="rounded-input bg-paper-2 px-4 py-3 text-ink-2">Rincian biaya muncul di sini setelah tanggal selesai baru dihitung.</p>
            @endif
        </section>
    </x-split-layout>
@endsection
