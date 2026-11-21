<?php

namespace App\Http\Controllers\Admin;

use App\Enums\GarageStatus;
use App\Enums\GarageType;
use App\Http\Controllers\Controller;
use App\Models\Garage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Daftar garasi dan pembuatan banyak garasi sekaligus oleh admin.
 */
class GarageController extends Controller
{
    /**
     * Daftar garasi (bisa difilter per tipe) dan jumlah garasi per tipe.
     */
    public function index(Request $request): View
    {
        $type = GarageType::tryFrom((string) $request->query('type'));

        $garages = Garage::query()
            ->when($type, fn ($query) => $query->ofType($type))
            ->orderBy('type')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        // Contoh hasil: ['car' => ['total' => 10, 'used' => 2], ...]
        $counts = [];
        foreach (GarageType::cases() as $garageType) {
            $counts[$garageType->value] = [
                'total' => Garage::query()->ofType($garageType)->count(),
                'used' => Garage::query()->ofType($garageType)->where('status', GarageStatus::Used)->count(),
            ];
        }

        return view('admin.garages.index', [
            'garages' => $garages,
            'type' => $type?->value,
            'counts' => $counts,
        ]);
    }

    /**
     * Form buat garasi, beserta kode berikutnya dan sisa kuota tiap tipe.
     */
    public function create(): View
    {
        $nextCodes = [];
        $remaining = [];

        foreach (GarageType::cases() as $type) {
            $highest = Garage::highestNumber($type);
            $nextCodes[$type->value] = $highest < Garage::MAX_NUMBER ? Garage::makeCode($type, $highest + 1) : null;
            $remaining[$type->value] = Garage::MAX_NUMBER - $highest;
        }

        return view('admin.garages.create', [
            'garageTypes' => GarageType::cases(),
            'nextCodes' => $nextCodes,
            'remaining' => $remaining,
        ]);
    }

    /**
     * Buat garasi berurutan melanjutkan kode terakhir. Contoh: sudah ada E005, tambah 2 jadi E006 dan E007.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'type' => ['required', Rule::enum(GarageType::class)],
            'quantity' => ['required', 'integer', 'between:1,999'],
            'daily_rate' => ['required', 'integer', 'gt:0', 'max:999999999'],
            'kwh_rate' => ['exclude_unless:type,electric', 'required', 'integer', 'gt:0', 'max:999999999'],
        ], [
            'required' => ':Attribute wajib diisi.',
            'type.enum' => 'Pilih tipe garasi yang tersedia.',
            'integer' => ':Attribute harus bilangan bulat.',
            'quantity.between' => 'Jumlah garasi harus 1 sampai 999.',
            'gt' => ':Attribute harus lebih dari 0.',
            'max' => ':Attribute terlalu besar.',
        ], [
            'type' => 'tipe garasi',
            'quantity' => 'jumlah garasi',
            'daily_rate' => 'harga sewa harian',
            'kwh_rate' => 'harga isi listrik per kWh',
        ]);

        $type = GarageType::from($request->input('type'));
        $quantity = $request->integer('quantity');
        $highest = Garage::highestNumber($type);
        $remaining = Garage::MAX_NUMBER - $highest;

        if ($quantity > $remaining) {
            return back()->withInput()->withErrors([
                'quantity' => "{$type->label()} hanya bisa ditambah {$remaining} garasi lagi (maksimal ".Garage::MAX_NUMBER.').',
            ]);
        }

        $codes = [];
        for ($number = $highest + 1; $number <= $highest + $quantity; $number++) {
            $code = Garage::makeCode($type, $number);

            Garage::create([
                'id' => $code,
                'type' => $type,
                'daily_rate' => $request->integer('daily_rate'),
                'kwh_rate' => $type === GarageType::Electric ? $request->integer('kwh_rate') : null,
                'status' => GarageStatus::Available,
            ]);

            $codes[] = $code;
        }

        $range = count($codes) === 1 ? $codes[0] : $codes[0].' sampai '.end($codes);

        return redirect()
            ->route('admin.garages.index', ['type' => $type->value])
            ->with('success', "{$quantity} {$type->label()} dibuat: {$range}.");
    }
}
