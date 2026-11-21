{{-- Detail satu sewa: tiket garasi (kode, kendaraan, cap lunas), data periode, rincian biaya, dan bukti pelunasan. --}}
@props(['rental'])

@use('Illuminate\Support\Facades\Storage')

<div {{ $attributes->class('grid gap-8 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start') }}>
    <div class="grid gap-8">
        <section aria-labelledby="rental-period-{{ $rental->id }}" class="grid gap-3">
            <h2 id="rental-period-{{ $rental->id }}" class="text-lg">Periode dan kendaraan</h2>
            <dl class="grid gap-x-6 sm:grid-cols-2">
                <div class="grid gap-0.5 border-t border-rule py-3">
                    <dt class="text-sm text-muted">Tipe sewa</dt>
                    <dd><x-badge :status="$rental->rental_type" /></dd>
                </div>
                <div class="grid gap-0.5 border-t border-rule py-3">
                    <dt class="text-sm text-muted">Lama sewa awal</dt>
                    <dd class="num">{{ $rental->rental_type === \App\Enums\RentalType::Monthly ? '1 bulan ('.$rental->days_count.' hari)' : $rental->days_count.' hari' }}</dd>
                </div>
                <div class="grid gap-0.5 border-t border-rule py-3">
                    <dt class="text-sm text-muted">Tanggal mulai</dt>
                    <dd><x-date :value="$rental->start_date" /></dd>
                </div>
                <div class="grid gap-0.5 border-t border-rule py-3">
                    <dt class="text-sm text-muted">Tanggal selesai</dt>
                    <dd><x-date :value="$rental->end_date" /></dd>
                </div>
                <div class="grid gap-0.5 border-t border-rule py-3">
                    <dt class="text-sm text-muted">Kendaraan</dt>
                    <dd>{{ $rental->vehicle->brand_model }} <span class="text-muted">({{ $rental->vehicle->type->label() }})</span></dd>
                </div>
                <div class="grid gap-0.5 border-t border-rule py-3">
                    <dt class="text-sm text-muted">Nomor plat</dt>
                    <dd class="font-mono">{{ $rental->vehicle->license_plate ?? '-' }}</dd>
                </div>
                @if ($rental->vehicle->battery_kwh)
                    <div class="grid gap-0.5 border-t border-rule py-3">
                        <dt class="text-sm text-muted">Kapasitas baterai penuh</dt>
                        <dd class="num">{{ $rental->vehicle->battery_kwh }}&nbsp;kWh</dd>
                    </div>
                @endif
                <div class="grid gap-0.5 border-t border-rule py-3">
                    <dt class="text-sm text-muted">Status sewa</dt>
                    <dd><x-badge :status="$rental->status" /></dd>
                </div>
            </dl>
        </section>

        <section aria-labelledby="rental-cost-{{ $rental->id }}" class="grid gap-3">
            <h2 id="rental-cost-{{ $rental->id }}" class="text-lg">Biaya</h2>
            <dl class="grid">
                <div class="flex justify-between gap-4 border-t border-ink-2 py-3 text-lg font-medium">
                    <dt>
                        Total biaya sewa awal
                        @if ($rental->garage->type === \App\Enums\GarageType::Electric && $rental->vehicle->battery_kwh)
                            <span class="block text-sm font-normal text-muted">Termasuk isi listrik sampai baterai penuh.</span>
                        @endif
                    </dt>
                    <dd><x-money :amount="$rental->total_cost" /></dd>
                </div>
            </dl>
        </section>
    </div>

    <aside class="grid gap-6" aria-label="Tiket garasi">
        <div class="ticket">
            <div class="grid gap-3 p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm text-muted">{{ $rental->garage->type->label() }}</p>
                        <p class="font-mono text-3xl font-medium">{{ $rental->garage->id }}</p>
                    </div>
                    <span class="stamp">Lunas</span>
                </div>
                <p>{{ $rental->vehicle->brand_model }}<span class="block font-mono text-sm text-muted">{{ $rental->vehicle->license_plate ?? 'Tanpa plat' }}</span></p>
            </div>
            <div class="ticket-stub flex justify-between gap-3 px-5 py-3 text-sm">
                <span><x-date :value="$rental->start_date" format="j M Y" /></span>
                <span class="text-muted">sampai</span>
                <span><x-date :value="$rental->end_date" format="j M Y" /></span>
            </div>
        </div>

        <figure class="grid gap-2">
            <figcaption class="text-sm text-muted">Bukti pelunasan</figcaption>
            <a href="{{ Storage::disk('public')->url($rental->payment_proof) }}" target="_blank" rel="noopener" class="block w-fit rounded-input">
                <img src="{{ Storage::disk('public')->url($rental->payment_proof) }}" alt="Bukti pelunasan sewa garasi {{ $rental->garage->id }}" width="320" height="240" loading="lazy" class="h-auto max-h-72 w-auto max-w-full rounded-input object-contain shadow-card">
            </a>
        </figure>
    </aside>
</div>
