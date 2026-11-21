{{--
    Laporan sewa garasi: per bulan dalam satu tahun, per hari dalam satu bulan, per tipe garasi, atau per penyewa.
    Mode tipe dan penyewa memakai tahun dengan bulan opsional.
    Susunan: tab pengelompokan, filter periode, navigasi periode, ringkasan angka, lalu tabel.
--}}
@extends('layouts.app')

@use('Illuminate\Support\Carbon')

@section('title', 'Laporan Sewa')

@section('content')
    @php
        $modes = [
            'month' => ['label' => 'Per bulan', 'column' => 'Bulan', 'peak' => 'Bulan tertinggi'],
            'day' => ['label' => 'Per hari', 'column' => 'Tanggal', 'peak' => 'Hari tertinggi'],
            'type' => ['label' => 'Per tipe garasi', 'column' => 'Tipe garasi', 'peak' => 'Tipe teratas'],
            'tenant' => ['label' => 'Per penyewa', 'column' => 'Penyewa', 'peak' => 'Penyewa teratas'],
        ];
        $monthOptions = collect(range(1, 12))->mapWithKeys(
            fn (int $monthNumber) => [$monthNumber => Carbon::create(2000, $monthNumber, 1)->locale('id')->translatedFormat('F')]
        )->all();

        $defaultDayMonth = $month ?? ($year === today()->year ? today()->month : 1);
        $modeUrl = fn (string $modeKey) => route('admin.reports.index', array_filter([
            'mode' => $modeKey,
            'year' => $year,
            'month' => match ($modeKey) {
                'month' => null,
                'day' => $defaultDayMonth,
                default => $month,
            },
        ]));

        if ($month === null) {
            $periodLabel = "Tahun {$year}";
            $previousPeriod = ['year' => $year - 1];
            $nextPeriod = ['year' => $year + 1];
        } else {
            $periodStart = Carbon::create($year, $month, 1)->locale('id');
            $periodLabel = $periodStart->translatedFormat('F Y');
            $previousPeriod = ['year' => $periodStart->copy()->subMonth()->year, 'month' => $periodStart->copy()->subMonth()->month];
            $nextPeriod = ['year' => $periodStart->copy()->addMonth()->year, 'month' => $periodStart->copy()->addMonth()->month];
        }

        $hasData = $grandTotal['rental_count'] > 0 || $grandTotal['total'] > 0;
        $maxRowTotal = (float) $rows->max('total');
        $peakRow = $maxRowTotal > 0 ? $rows->firstWhere('total', $maxRowTotal) : null;
    @endphp

    <x-page-header title="Laporan sewa" description="Pendapatan dari sewa baru dan perpanjangan, dikelompokkan sesuai pilihan." />

    <x-split-layout aside-label="Pengaturan laporan">
        <x-slot:aside>
            <nav aria-label="Pengelompokan laporan" class="grid gap-2">
                <p class="text-sm font-medium text-ink-2">Kelompokkan</p>
                <ul class="tab-stack grid">
                    @foreach ($modes as $modeKey => $modeConfig)
                        <li>
                            <a href="{{ $modeUrl($modeKey) }}" class="tab" @if ($mode === $modeKey) aria-current="page" @endif>{{ $modeConfig['label'] }}</a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <x-card>
                <form method="GET" action="{{ route('admin.reports.index') }}" class="grid gap-2">
                    <input type="hidden" name="mode" value="{{ $mode }}">

                    <x-input name="year" type="number" label="Tahun" :value="$year" min="2000" max="2100" inputmode="numeric" required />

                    @if ($mode !== 'month')
                        <x-select
                            name="month"
                            label="Bulan"
                            :options="$monthOptions"
                            :selected="$month"
                            :placeholder="$mode === 'day' ? null : 'Semua bulan'"
                            :required="$mode === 'day'"
                            :help="$mode === 'day' ? 'Wajib untuk laporan per hari.' : null"
                        />
                    @endif

                    <x-button variant="secondary" class="w-full">Tampilkan</x-button>
                </form>
            </x-card>

            <nav aria-label="Pindah periode" class="grid grid-cols-2">
                <a href="{{ route('admin.reports.index', ['mode' => $mode, ...$previousPeriod]) }}" class="tab justify-center text-sm whitespace-normal" rel="prev">
                    <span aria-hidden="true">&larr;</span>&nbsp;Sebelumnya
                </a>
                <a href="{{ route('admin.reports.index', ['mode' => $mode, ...$nextPeriod]) }}" class="tab justify-center text-sm whitespace-normal" rel="next">
                    Berikutnya&nbsp;<span aria-hidden="true">&rarr;</span>
                </a>
            </nav>
        </x-slot:aside>

        <section aria-labelledby="report-period" class="grid gap-6">
            <header class="grid gap-0.5">
                <p class="text-sm text-muted">{{ $modes[$mode]['label'] }}</p>
                <h2 id="report-period" class="text-xl sm:text-2xl">{{ $periodLabel }}</h2>
            </header>

            @if ($hasData)
                <dl class="grid border-2 border-ink bg-surface shadow-card @lg:grid-cols-2 @4xl:grid-cols-4">
                    <div class="grid content-start gap-1 border-b border-rule p-4 @lg:border-r @4xl:border-b-0">
                        <dt class="text-sm text-muted">Total pendapatan</dt>
                        <dd class="text-2xl font-semibold text-accent-deep"><x-money :amount="$grandTotal['total']" /></dd>
                        @if ($peakRow)
                            <dd class="text-sm text-muted">
                                {{ $modes[$mode]['peak'] }}: <span class="font-medium text-ink">{{ $peakRow['label'] }}</span>
                            </dd>
                        @endif
                    </div>
                    <div class="grid content-start gap-1 border-b border-rule p-4 @4xl:border-r @4xl:border-b-0">
                        <dt class="text-sm text-muted">Jumlah sewa</dt>
                        <dd class="num text-2xl font-semibold">{{ $grandTotal['rental_count'] }}</dd>
                    </div>
                    <div class="grid content-start gap-1 border-b border-rule p-4 @lg:border-r @lg:border-b-0">
                        <dt class="text-sm text-muted">Pendapatan sewa</dt>
                        <dd class="text-2xl font-semibold"><x-money :amount="$grandTotal['rental_revenue']" /></dd>
                    </div>
                    <div class="grid content-start gap-1 p-4">
                        <dt class="text-sm text-muted">Pendapatan perpanjangan</dt>
                        <dd class="text-2xl font-semibold"><x-money :amount="$grandTotal['extension_revenue']" /></dd>
                    </div>
                </dl>
            @endif

            <x-table
                :empty="! $hasData"
                empty-title="Tidak ada sewa pada periode ini"
                empty-text="Coba pilih tahun atau bulan lain. Laporan hanya menghitung sewa yang sudah tersimpan."
                :caption="'Laporan sewa '.strtolower($modes[$mode]['label']).', '.$periodLabel"
            >
                <x-slot:head>
                    <th scope="col">{{ $modes[$mode]['column'] }}</th>
                    <th scope="col" class="text-right">Jumlah sewa</th>
                    <th scope="col" class="text-right">Pendapatan sewa</th>
                    <th scope="col" class="text-right">Pendapatan perpanjangan</th>
                    <th scope="col" class="text-right">Total</th>
                </x-slot:head>
                @foreach ($rows as $row)
                    @php
                        $isEmptyRow = $row['rental_count'] === 0 && $row['total'] <= 0;
                    @endphp
                    <tr @class(['text-muted' => $isEmptyRow])>
                        <td @class(['font-medium' => ! $isEmptyRow, 'whitespace-nowrap'])>{{ $row['label'] }}</td>
                        <td class="num text-right">{{ $row['rental_count'] }}</td>
                        <td class="text-right"><x-money :amount="$row['rental_revenue']" /></td>
                        <td class="text-right"><x-money :amount="$row['extension_revenue']" /></td>
                        <td @class(['text-right', 'font-semibold' => ! $isEmptyRow])><x-money :amount="$row['total']" /></td>
                    </tr>
                @endforeach
                <x-slot:foot>
                    <td>Total</td>
                    <td class="num text-right">{{ $grandTotal['rental_count'] }}</td>
                    <td class="text-right"><x-money :amount="$grandTotal['rental_revenue']" /></td>
                    <td class="text-right"><x-money :amount="$grandTotal['extension_revenue']" /></td>
                    <td class="text-right"><x-money :amount="$grandTotal['total']" /></td>
                </x-slot:foot>
            </x-table>
        </section>
    </x-split-layout>
@endsection
