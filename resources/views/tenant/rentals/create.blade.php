{{--
    Form sewa garasi baru, dua tahap dalam satu halaman. Kolom kiri: kendaraan dan periode; kolom kanan: garasi dan pembayaran.
    Tahap A (GET): pilih kendaraan, tanggal mulai, dan jumlah hari, lalu cek garasi tersedia.
    Tahap B (POST): pilih garasi, isi sisa baterai (mobil listrik), unggah bukti pelunasan.
    Biaya sewa tiap garasi ($garage->rental_cost) dihitung controller; JS hanya menambah pratinjau biaya cas.
    Total final dihitung ulang oleh sistem saat disimpan.
--}}
@extends('layouts.app')

@use('App\Enums\RentalType')

@section('title', 'Sewa Garasi')

@section('content')
    @php
        $selectedRentalType = old('rental_type', $rentalType ?? RentalType::Daily->value);
        $hasVehicleErrors = $errors->hasAny(['type', 'license_plate', 'brand_model', 'battery_kwh']);
    @endphp

    <x-page-header title="Sewa Garasi" description="Pilih kendaraan dan periode dulu, lalu pilih garasi yang masih kosong." />

    <x-form-errors />

    @if ($vehicles->isEmpty())
        <x-split-layout aside-label="Daftarkan kendaraan" aside-width="24rem">
            <x-slot:aside>
                <section aria-labelledby="step-vehicle" class="grid gap-4">
                    <h2 id="step-vehicle" class="text-xl">1. Daftarkan kendaraan</h2>
                    <x-card title="Data kendaraan" description="Garasi dipilih sesuai tipe kendaraan. Setelah disimpan, Anda langsung bisa memilih periode sewa.">
                        <x-vehicle-form variant="primary" />
                    </x-card>
                </section>
            </x-slot:aside>

            <x-table
                :empty="true"
                empty-title="Garasi tersedia muncul di sini"
                empty-text="Daftarkan kendaraan dulu. Setelah itu Anda bisa memilih periode sewa dan melihat garasi yang masih kosong."
            />
        </x-split-layout>
    @else
        <x-split-layout aside-label="Kendaraan dan periode" aside-width="24rem">
            <x-slot:aside>
                <section aria-labelledby="step-period" class="grid gap-4">
                    <h2 id="step-period" class="text-xl">1. Kendaraan dan periode</h2>

                    <details class="group rounded-card border border-rule bg-paper-2" @if ($hasVehicleErrors) open @endif>
                        <summary class="flex min-h-11 list-none items-center gap-2 px-4 font-medium text-ink underline decoration-rule underline-offset-4 [&::-webkit-details-marker]:hidden">
                            <span aria-hidden="true" class="font-mono transition-transform duration-200 group-open:rotate-45">+</span>
                            Tambah kendaraan baru
                        </summary>
                        <div class="border-t border-rule p-4">
                            <x-vehicle-form />
                        </div>
                    </details>

                    <x-card>
                        <form method="GET" action="{{ route('tenant.rentals.create') }}" class="grid gap-y-2" data-end-date-form>
                            <x-select name="vehicle_id" label="Kendaraan" placeholder="Pilih kendaraan" :selected="$selectedVehicle?->id" required>
                                @foreach ($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}" @selected((string) old('vehicle_id', $selectedVehicle?->id) === (string) $vehicle->id)>
                                        {{ $vehicle->brand_model }} ({{ $vehicle->type->label() }}{{ $vehicle->license_plate ? ', '.$vehicle->license_plate : '' }})
                                    </option>
                                @endforeach
                            </x-select>

                            <fieldset class="grid gap-1.5" aria-describedby="rental_type-help">
                                <legend class="mb-1.5 text-sm font-medium text-ink-2">Tipe sewa</legend>
                                <div class="grid grid-cols-2 gap-3">
                                    @foreach (RentalType::cases() as $typeOption)
                                        <div class="relative">
                                            <input
                                                type="radio"
                                                id="rental_type-{{ $typeOption->value }}"
                                                name="rental_type"
                                                value="{{ $typeOption->value }}"
                                                class="key-input"
                                                required
                                                @checked($selectedRentalType === $typeOption->value)
                                            >
                                            <label for="rental_type-{{ $typeOption->value }}" class="key grid gap-0.5">
                                                <span class="font-medium">{{ $typeOption->label() }}</span>
                                                <span class="text-sm text-ink-2">
                                                    {{ $typeOption === RentalType::Daily ? 'Bayar per hari' : '1 bulan penuh, 25x harga harian' }}
                                                </span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <p id="rental_type-help" class="min-h-lh text-sm {{ $errors->has('rental_type') ? 'text-error' : 'text-muted' }}">
                                    @error('rental_type')
                                        {{ $message }}
                                    @else
                                        Sewa bulanan lebih hemat untuk 26 hari atau lebih.
                                    @enderror
                                </p>
                            </fieldset>

                            <x-input name="start_date" type="date" label="Tanggal mulai" :value="$startDate" :min="now()->toDateString()" help="Paling cepat hari ini." required data-start-date />

                            <div data-depends-on="rental_type" data-show-values="daily" @if ($selectedRentalType !== 'daily') hidden @endif>
                                <x-input
                                    name="days"
                                    type="number"
                                    label="Jumlah hari"
                                    :value="$selectedRentalType === 'daily' ? $days : null"
                                    min="1"
                                    :max="$daysInMonth - 1"
                                    inputmode="numeric"
                                    :help="'Sewa harian 1 sampai '.($daysInMonth - 1).' hari.'"
                                    required
                                    :disabled="$selectedRentalType !== 'daily'"
                                    data-days
                                />
                            </div>

                            <div class="grid gap-1.5" data-depends-on="rental_type" data-show-values="monthly" @if ($selectedRentalType !== 'monthly') hidden @endif>
                                <p class="text-sm font-medium text-ink-2">Lama sewa</p>
                                <p class="field num" data-monthly-days>{{ $daysInMonth }} hari (1 bulan penuh)</p>
                                <p class="min-h-lh text-sm text-muted">Mengikuti jumlah hari di bulan tanggal mulai.</p>
                            </div>

                            <div class="grid gap-1.5">
                                <label for="end_date_preview" class="text-sm font-medium text-ink-2">Tanggal selesai (otomatis)</label>
                                <input id="end_date_preview" type="date" value="{{ $endDate }}" class="field" readonly aria-describedby="end_date_preview-help" data-end-date>
                                <p id="end_date_preview-help" class="min-h-lh text-sm text-muted">Tanggal garasi harus dikosongkan.</p>
                            </div>

                            <x-button variant="secondary" class="w-full">Cek garasi tersedia</x-button>
                        </form>
                    </x-card>
                </section>
            </x-slot:aside>

                @if ($endDate)
                    <dl class="grid grid-cols-2 @xl:grid-cols-3">
                        <div class="grid gap-1 border-t border-rule py-3 pr-4">
                            <dt class="text-sm text-muted">Tanggal mulai</dt>
                            <dd><x-date :value="$startDate" /></dd>
                        </div>
                        <div class="grid gap-1 border-t border-l border-rule px-4 py-3">
                            <dt class="text-sm text-muted">Tanggal selesai</dt>
                            <dd><x-date :value="$endDate" /></dd>
                        </div>
                        <div class="col-span-2 grid gap-1 border-t border-rule py-3 @xl:col-span-1 @xl:border-l @xl:pl-4">
                            <dt class="text-sm text-muted">Tipe sewa</dt>
                            <dd>
                                @if ($rentalType)
                                    <x-badge :status="RentalType::from($rentalType)" />
                                @else
                                    -
                                @endif
                            </dd>
                        </div>
                    </dl>
                @endif

            @if ($endDate && $selectedVehicle)
                <section aria-labelledby="step-garage" class="grid gap-4">
                    <h2 id="step-garage" class="text-xl">2. Garasi dan pembayaran</h2>

                    @if ($garages->isEmpty())
                        <x-table
                            :empty="true"
                            empty-title="Tidak ada garasi tersedia untuk periode ini"
                            empty-text="Semua garasi yang cocok untuk {{ strtolower($selectedVehicle->type->label()) }} sudah terisi. Coba geser tanggal mulai atau kurangi jumlah hari."
                        />
                    @else
                        <form
                            method="POST"
                            action="{{ route('tenant.rentals.store') }}"
                            enctype="multipart/form-data"
                            class="grid gap-8"
                            data-rental-preview
                            data-battery-capacity="{{ $selectedVehicle->battery_kwh ?? 0 }}"
                            data-electric-vehicle="{{ $selectedVehicle->type->hasBattery() ? '1' : '0' }}"
                        >
                            @csrf
                            <input type="hidden" name="vehicle_id" value="{{ $selectedVehicle->id }}">
                            <input type="hidden" name="start_date" value="{{ $startDate }}">
                            <input type="hidden" name="rental_type" value="{{ $rentalType }}">
                            <input type="hidden" name="days" value="{{ $days }}">

                            <fieldset class="grid gap-3" aria-describedby="garage_id-help">
                                <legend class="mb-3 text-sm font-medium text-ink-2">Garasi yang tersedia ({{ $garages->count() }})</legend>
                                <ul class="grid grid-cols-2 gap-3 @2xl:grid-cols-3 @4xl:grid-cols-4">
                                    @foreach ($garages as $garage)
                                        <li class="relative">
                                            <input
                                                type="radio"
                                                id="garage-{{ $garage->id }}"
                                                name="garage_id"
                                                value="{{ $garage->id }}"
                                                class="key-input"
                                                required
                                                @checked((string) old('garage_id') === (string) $garage->id)
                                                data-rental-cost="{{ $garage->rental_cost }}"
                                                data-kwh-price="{{ $garage->kwh_rate ?? 0 }}"
                                                data-electric-garage="{{ $garage->type->value === 'electric' ? '1' : '0' }}"
                                            >
                                            <label for="garage-{{ $garage->id }}" class="key tint-{{ $garage->type->value }} grid gap-1">
                                                <span class="font-mono text-2xl font-medium">{{ $garage->id }}</span>
                                                <span class="text-sm text-ink-2">{{ $garage->type->label() }}</span>
                                                <span class="mt-1 text-sm"><x-money :amount="$garage->daily_rate" /> per hari</span>
                                                <span class="text-sm"><x-money :amount="$garage->monthlyPrice()" /> per bulan</span>
                                                @if ($garage->kwh_rate)
                                                    <span class="text-sm">Listrik <x-money :amount="$garage->kwh_rate" /> per kWh</span>
                                                @endif
                                                <span class="mt-1 border-t border-rule pt-1 text-sm font-medium">Biaya sewa <x-money :amount="$garage->rental_cost" /></span>
                                            </label>
                                        </li>
                                    @endforeach
                                </ul>
                                <p id="garage_id-help" class="min-h-lh text-sm {{ $errors->has('garage_id') ? 'text-error' : 'text-muted' }}">
                                    @error('garage_id')
                                        {{ $message }}
                                    @else
                                        Hanya garasi yang cocok dengan {{ strtolower($selectedVehicle->type->label()) }} dan kosong pada periode ini yang ditampilkan.
                                    @enderror
                                </p>
                            </fieldset>

                            <div class="grid gap-8 @2xl:grid-cols-[minmax(0,1fr)_18rem] @2xl:items-start">
                                <div class="grid gap-2">
                                    @if ($selectedVehicle->type->hasBattery())
                                        <x-input
                                            name="current_battery_kwh"
                                            type="number"
                                            label="Sisa baterai saat ini (kWh)"
                                            min="0"
                                            :max="$selectedVehicle->battery_kwh"
                                            step="0.01"
                                            inputmode="decimal"
                                            :help="'Kapasitas penuh '.$selectedVehicle->battery_kwh.' kWh. Di garasi mobil listrik, baterai diisi sampai penuh.'"
                                            required
                                            data-current-battery
                                        />
                                    @endif

                                    <x-file-input name="payment_proof" label="Bukti pelunasan" required />
                                </div>

                                <section aria-labelledby="cost-preview" class="rounded-card bg-paper-3 p-5" aria-live="polite">
                                    <h3 id="cost-preview" class="mb-3 text-lg">Perkiraan biaya</h3>
                                    <dl class="grid">
                                        <div class="flex justify-between gap-4 border-t border-rule py-2">
                                            <dt>Biaya sewa</dt>
                                            <dd class="num" data-preview-rental>-</dd>
                                        </div>
                                        <div class="grid gap-1 border-t border-rule py-2" data-preview-charging-row hidden>
                                            <div class="flex justify-between gap-4">
                                                <dt>Listrik yang dicas</dt>
                                                <dd class="num" data-preview-kwh>-</dd>
                                            </div>
                                            <div class="flex justify-between gap-4">
                                                <dt>Biaya cas</dt>
                                                <dd class="num" data-preview-charging>-</dd>
                                            </div>
                                        </div>
                                        <div class="flex justify-between gap-4 border-t border-ink-2 py-2 text-lg font-medium">
                                            <dt>Total</dt>
                                            <dd class="num" data-preview-total>-</dd>
                                        </div>
                                    </dl>
                                    <p class="mt-3 text-sm text-ink-2">Ini pratinjau. Total final dihitung ulang oleh sistem saat disimpan.</p>
                                </section>
                            </div>

                            <div>
                                <x-button>Sewa sekarang</x-button>
                            </div>
                        </form>
                    @endif
                </section>
            @else
                <x-table
                    :empty="true"
                    empty-title="Garasi tersedia muncul di sini"
                    empty-text="Pilih kendaraan, tipe sewa, tanggal mulai, dan jumlah hari, lalu tekan Cek garasi tersedia."
                />
            @endif
        </x-split-layout>
    @endif
@endsection
