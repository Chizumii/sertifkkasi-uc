<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\VehicleType;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

// Controller ini buat penyewa nambah kendaraan dari form di halaman Sewa Garasi.
class VehicleController extends Controller
{
    // Function ini buat nyimpen kendaraan baru, lalu balik ke form sewa dengan kendaraan itu langsung terpilih.
    // - Nomor plat wajib, kecuali sepeda. Plat dirapikan jadi huruf besar dengan satu spasi.
    // - Kapasitas baterai hanya untuk mobil listrik.
    public function store(Request $request): RedirectResponse
    {
        // Rapikan plat: "  l   9999  zz " menjadi "L 9999 ZZ".
        $plate = strtoupper(trim(preg_replace('/\s+/', ' ', (string) $request->input('license_plate'))));
        $request->merge(['license_plate' => $plate === '' ? null : $plate]);

        $validated = $request->validate([
            'type' => ['required', Rule::enum(VehicleType::class)],
            'license_plate' => ['exclude_if:type,bicycle', 'required', 'string', 'max:15', 'unique:vehicles,license_plate'],
            'brand_model' => ['required', 'string', 'max:255'],
            'battery_kwh' => ['exclude_unless:type,electricCar', 'required', 'numeric', 'gt:0', 'max:999999'],
        ], [
            'required' => ':Attribute wajib diisi.',
            'type.enum' => 'Pilih tipe kendaraan yang tersedia.',
            'license_plate.unique' => 'Nomor plat ini sudah terdaftar.',
            'max' => ':Attribute terlalu panjang.',
            'battery_kwh.max' => 'Kapasitas baterai terlalu besar.',
            'numeric' => ':Attribute harus berupa angka.',
            'gt' => ':Attribute harus lebih dari 0.',
        ], [
            'type' => 'tipe kendaraan',
            'license_plate' => 'nomor plat',
            'brand_model' => 'merek dan model',
            'battery_kwh' => 'kapasitas baterai',
        ]);

        $vehicle = $request->user()->vehicles()->create([
            'type' => $validated['type'],
            'license_plate' => $validated['license_plate'] ?? null,
            'brand_model' => $validated['brand_model'],
            'battery_kwh' => $validated['battery_kwh'] ?? null,
        ]);

        return redirect()
            ->route('tenant.rentals.create', ['vehicle_id' => $vehicle->id])
            ->with('success', "{$vehicle->brand_model} ditambahkan. Lanjutkan dengan memilih periode sewa.");
    }
}
