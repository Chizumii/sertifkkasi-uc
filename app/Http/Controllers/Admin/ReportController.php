<?php

namespace App\Http\Controllers\Admin;

use App\Enums\GarageType;
use App\Http\Controllers\Controller;
use App\Models\Rental;
use App\Models\RentalExtension;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

/**
 * Laporan pendapatan untuk admin.
 *
 * Pendapatan sewa = total_cost sewa (dihitung di tanggal mulai sewa).
 * Pendapatan perpanjangan = remaining_cost perpanjangan (dihitung di tanggal perpanjangan dibuat).
 *
 * Mode laporan:
 * - month : per bulan dalam satu tahun (12 baris)
 * - day   : per tanggal dalam satu bulan
 * - type  : per tipe garasi
 * - tenant: per penyewa
 */
class ReportController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $mode = $request->input('mode', 'month');
        $year = $request->input('year', today()->year);

        $validator = Validator::make(['mode' => $mode, 'year' => $year, 'month' => $request->input('month')], [
            'mode' => ['required', 'in:month,day,type,tenant'],
            'year' => ['required', 'integer', 'between:2000,2100'],
            'month' => ['nullable', 'required_if:mode,day', 'integer', 'between:1,12'],
        ], [
            'mode.in' => 'Pilihan pengelompokan laporan tidak dikenal.',
            'year.between' => 'Tahun harus antara 2000 dan 2100.',
            'month.required_if' => 'Pilih bulan untuk laporan per hari.',
            'month.between' => 'Bulan harus 1 sampai 12.',
            'integer' => ':Attribute harus bilangan bulat.',
        ], [
            'mode' => 'pengelompokan',
            'year' => 'tahun',
            'month' => 'bulan',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.reports.index')->withErrors($validator);
        }

        $year = (int) $year;
        $month = ($mode !== 'month' && $request->filled('month')) ? $request->integer('month') : null;

        // Rentang tanggal laporan: satu bulan bila bulan dipilih, selain itu satu tahun.
        if ($month !== null) {
            $from = Carbon::create($year, $month, 1);
            $to = $from->copy()->endOfMonth();
        } else {
            $from = Carbon::create($year, 1, 1);
            $to = $from->copy()->endOfYear();
        }

        $rentals = Rental::query()
            ->whereBetween('start_date', [$from->copy()->startOfDay(), $to->copy()->startOfDay()])
            ->with(['garage', 'vehicle.tenant'])
            ->get();

        $extensions = RentalExtension::query()
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->with(['rental.garage', 'rental.vehicle.tenant'])
            ->get();

        $rows = [];

        if ($mode === 'month') {
            for ($m = 1; $m <= 12; $m++) {
                $rows[] = $this->makeRow(
                    Carbon::create(2000, $m, 1)->translatedFormat('F'),
                    $rentals->filter(fn ($rental) => $rental->start_date->month === $m),
                    $extensions->filter(fn ($extension) => $extension->created_at->month === $m),
                );
            }
        }

        if ($mode === 'day') {
            for ($d = 1; $d <= $from->daysInMonth; $d++) {
                $rows[] = $this->makeRow(
                    $from->copy()->setDay($d)->translatedFormat('j F Y'),
                    $rentals->filter(fn ($rental) => $rental->start_date->day === $d),
                    $extensions->filter(fn ($extension) => $extension->created_at->day === $d),
                );
            }
        }

        if ($mode === 'type') {
            foreach (GarageType::cases() as $type) {
                $rows[] = $this->makeRow(
                    $type->label(),
                    $rentals->filter(fn ($rental) => $rental->garage->type === $type),
                    $extensions->filter(fn ($extension) => $extension->rental->garage->type === $type),
                );
            }
        }

        if ($mode === 'tenant') {
            // Kumpulkan nama penyewa yang punya transaksi, lalu urutkan berdasarkan nama.
            $tenantNames = [];
            foreach ($rentals as $rental) {
                $tenantNames[$rental->vehicle->tenant_id] = $rental->vehicle->tenant->full_name;
            }
            foreach ($extensions as $extension) {
                $tenantNames[$extension->rental->vehicle->tenant_id] = $extension->rental->vehicle->tenant->full_name;
            }
            asort($tenantNames);

            foreach ($tenantNames as $tenantId => $name) {
                $rows[] = $this->makeRow(
                    $name,
                    $rentals->filter(fn ($rental) => $rental->vehicle->tenant_id === $tenantId),
                    $extensions->filter(fn ($extension) => $extension->rental->vehicle->tenant_id === $tenantId),
                );
            }
        }

        $rows = collect($rows);

        return view('admin.reports.index', [
            'mode' => $mode,
            'year' => $year,
            'month' => $month,
            'rows' => $rows,
            'grandTotal' => [
                'rental_count' => (int) $rows->sum('rental_count'),
                'rental_revenue' => (int) $rows->sum('rental_revenue'),
                'extension_revenue' => (int) $rows->sum('extension_revenue'),
                'total' => (int) $rows->sum('total'),
            ],
        ]);
    }

    /**
     * Satu baris laporan: jumlah sewa, pendapatan sewa, pendapatan perpanjangan, dan totalnya.
     *
     * @param  Collection<int, Rental>  $rentals
     * @param  Collection<int, RentalExtension>  $extensions
     * @return array{label: string, rental_count: int, rental_revenue: int, extension_revenue: int, total: int}
     */
    private function makeRow(string $label, Collection $rentals, Collection $extensions): array
    {
        $rentalRevenue = (int) $rentals->sum('total_cost');
        $extensionRevenue = (int) $extensions->sum('remaining_cost');

        return [
            'label' => $label,
            'rental_count' => $rentals->count(),
            'rental_revenue' => $rentalRevenue,
            'extension_revenue' => $extensionRevenue,
            'total' => $rentalRevenue + $extensionRevenue,
        ];
    }
}
