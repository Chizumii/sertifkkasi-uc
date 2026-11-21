<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\GarageStatus;
use App\Enums\RentalStatus;
use App\Enums\RentalType;
use App\Http\Controllers\Controller;
use App\Models\Garage;
use App\Models\Rental;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Sewa garasi oleh penyewa.
 *
 * Alurnya 2 tahap:
 * 1. create(): penyewa memilih kendaraan, tanggal mulai, dan jumlah hari, lalu melihat garasi yang kosong.
 * 2. store(): penyewa memilih garasi, mengunggah bukti bayar, lalu sewa disimpan.
 */
class RentalController extends Controller
{
    /**
     * Riwayat sewa milik penyewa.
     */
    public function index(Request $request): View
    {
        $rentals = Rental::query()
            ->ownedBy($request->user())
            ->with(['garage', 'vehicle'])
            ->latest('start_date')
            ->latest('id')
            ->paginate(10);

        return view('tenant.rentals.index', ['rentals' => $rentals]);
    }

    /**
     * Tahap 1: form sewa. Bila data periode sudah diisi, tampilkan garasi yang kosong beserta biayanya.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $selectedVehicle = $request->filled('vehicle_id') ? $user->vehicles()->find($request->integer('vehicle_id')) : null;
        $startDate = $this->startDateFrom($request) ?? today();

        $data = [
            'vehicles' => $user->vehicles()->orderBy('brand_model')->get(),
            'selectedVehicle' => $selectedVehicle,
            'startDate' => $startDate->toDateString(),
            'days' => null,
            'daysInMonth' => $startDate->daysInMonth,
            'endDate' => null,
            'rentalType' => null,
            'garages' => collect(),
        ];

        // Halaman baru dibuka (belum ada tanggal/jumlah hari): tampilkan form kosong.
        if (! $request->hasAny(['start_date', 'days', 'rental_type'])) {
            return view('tenant.rentals.create', $data);
        }

        $this->fillRentalType($request);

        $validator = Validator::make($request->all(), $this->periodRules($request), $this->messages($request), $this->attributeNames());
        if ($validator->fails()) {
            return redirect()->route('tenant.rentals.create')->withErrors($validator)->withInput();
        }

        $days = $request->integer('days');
        $rentalType = RentalType::from($request->input('rental_type'));
        $endDate = $startDate->copy()->addDays($days);

        if ($selectedVehicle->isBusy($startDate, $endDate)) {
            return redirect()->route('tenant.rentals.create')
                ->withErrors(['vehicle_id' => "{$selectedVehicle->brand_model} sudah berada di garasi lain pada periode ini."])
                ->withInput();
        }

        // Garasi yang cocok dengan tipe kendaraan dan tidak sedang disewa pada periode ini.
        $garages = Garage::query()
            ->whereIn('type', $selectedVehicle->type->allowedGarageTypes())
            ->whereDoesntHave('rentals', fn ($query) => $query->overlapping($startDate, $endDate))
            ->orderBy('id')
            ->get();

        foreach ($garages as $garage) {
            $garage->rental_cost = $garage->rentalCost($days, $rentalType);
        }

        $data['days'] = $days;
        $data['endDate'] = $endDate->toDateString();
        $data['rentalType'] = $rentalType->value;
        $data['garages'] = $garages;

        return view('tenant.rentals.create', $data);
    }

    /**
     * Tahap 2: simpan sewa baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->fillRentalType($request);

        $vehicle = $request->user()->vehicles()->find($request->integer('vehicle_id'));
        $isElectricCar = $vehicle !== null && $vehicle->type->hasBattery();

        $rules = $this->periodRules($request);
        $rules['garage_id'] = ['required', 'string', 'size:4', 'exists:garages,id'];
        $rules['payment_proof'] = ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'];
        $rules['current_battery_kwh'] = $isElectricCar
            ? ['required', 'numeric', 'min:0', 'max:'.$vehicle->battery_kwh]
            : ['exclude'];

        $messages = $this->messages($request);
        if ($isElectricCar) {
            $messages['current_battery_kwh.max'] = "Sisa baterai tidak boleh melebihi kapasitas penuh {$vehicle->battery_kwh} kWh.";
        }

        $request->validate($rules, $messages, $this->attributeNames());

        $garage = Garage::find($request->input('garage_id'));
        $days = $request->integer('days');
        $rentalType = RentalType::from($request->input('rental_type'));
        $startDate = $this->startDateFrom($request);
        $endDate = $startDate->copy()->addDays($days);

        // Cek garasi dan kendaraan sebelum menyimpan.
        if (! in_array($garage->type, $vehicle->type->allowedGarageTypes(), true)) {
            return back()->withInput()->withErrors(['garage_id' => "Garasi {$garage->id} tidak bisa dipakai untuk {$vehicle->type->label()}."]);
        }

        if (! $garage->isFree($startDate, $endDate)) {
            return back()->withInput()->withErrors(['garage_id' => "Garasi {$garage->id} sudah disewa pada periode ini. Pilih garasi lain."]);
        }

        if ($vehicle->isBusy($startDate, $endDate)) {
            return back()->withInput()->withErrors(['vehicle_id' => "{$vehicle->brand_model} sudah berada di garasi lain pada periode ini."]);
        }

        // Total = biaya sewa garasi + biaya isi listrik (khusus mobil listrik di garasi listrik).
        $remainingBattery = $request->filled('current_battery_kwh') ? $request->float('current_battery_kwh') : null;
        $totalCost = $garage->rentalCost($days, $rentalType) + $vehicle->chargingCost($garage, $remainingBattery);

        $rental = Rental::create([
            'garage_code' => $garage->id,
            'vehicle_id' => $vehicle->id,
            'rental_type' => $rentalType,
            'days_count' => $days,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'total_cost' => $totalCost,
            'payment_proof' => $request->file('payment_proof')->store('payment-proofs', 'public'),
            'status' => RentalStatus::forPeriod($startDate, $endDate, today()),
        ]);

        // Sewa yang mulai hari ini langsung membuat garasi terpakai.
        if ($startDate->isToday()) {
            $garage->update(['status' => GarageStatus::Used]);
        }

        return redirect()
            ->route('tenant.rentals.show', $rental)
            ->with('success', "Sewa garasi {$garage->id} berhasil disimpan.");
    }

    /**
     * Detail sewa. Penyewa hanya boleh melihat sewa miliknya sendiri.
     */
    public function show(Request $request, Rental $rental): View
    {
        abort_if($rental->vehicle->tenant_id !== $request->user()->id, 403);

        return view('tenant.rentals.show', ['rental' => $rental->load(['garage', 'vehicle', 'extensions'])]);
    }

    /**
     * Melengkapi tipe sewa dan jumlah hari sebelum validasi.
     * - Bulanan: jumlah hari = jumlah hari di bulan tanggal mulai.
     * - Tanpa tipe sewa: ditebak dari jumlah hari (sebulan penuh = bulanan).
     */
    private function fillRentalType(Request $request): void
    {
        $startDate = $this->startDateFrom($request);
        $rentalType = $request->input('rental_type');

        if ($rentalType === null && $request->filled('days') && $startDate !== null) {
            $rentalType = $request->integer('days') === $startDate->daysInMonth ? 'monthly' : 'daily';
        }

        if ($rentalType === 'monthly' && $startDate !== null) {
            $request->merge(['days' => $startDate->daysInMonth]);
        }

        if ($rentalType !== null) {
            $request->merge(['rental_type' => $rentalType]);
        }
    }

    /**
     * Aturan validasi kendaraan dan periode (dipakai di tahap 1 dan tahap 2).
     *
     * @return array<string, mixed>
     */
    private function periodRules(Request $request): array
    {
        $daysInMonth = ($this->startDateFrom($request) ?? today())->daysInMonth;

        $daysRules = ['required', 'integer', 'min:1'];
        if ($request->input('rental_type') === 'daily') {
            $daysRules[] = 'max:'.($daysInMonth - 1);
        }

        return [
            'vehicle_id' => ['required', 'integer', Rule::exists('vehicles', 'id')->where('tenant_id', $request->user()->id)],
            'start_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'rental_type' => ['required', 'in:daily,monthly'],
            'days' => $daysRules,
        ];
    }

    /**
     * Tanggal mulai dari input, atau null bila kosong / formatnya salah.
     */
    private function startDateFrom(Request $request): ?Carbon
    {
        $input = (string) $request->input('start_date');

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $input)) {
            return null;
        }

        return Carbon::createFromFormat('Y-m-d', $input)->startOfDay();
    }

    /**
     * Pesan error validasi dalam bahasa Indonesia.
     *
     * @return array<string, string>
     */
    private function messages(Request $request): array
    {
        $daysInMonth = ($this->startDateFrom($request) ?? today())->daysInMonth;

        return [
            'required' => ':Attribute wajib diisi.',
            'integer' => ':Attribute harus bilangan bulat.',
            'numeric' => ':Attribute harus berupa angka.',
            'vehicle_id.exists' => 'Kendaraan tidak ditemukan di daftar Anda.',
            'start_date.date_format' => 'Format tanggal mulai tidak valid.',
            'start_date.after_or_equal' => 'Tanggal mulai tidak boleh sebelum hari ini.',
            'rental_type.required' => 'Pilih tipe sewa harian atau bulanan.',
            'rental_type.in' => 'Pilih tipe sewa harian atau bulanan.',
            'days.min' => 'Jumlah hari minimal 1.',
            'days.max' => 'Sewa harian maksimal :max hari. Pilih sewa bulanan untuk '.$daysInMonth.' hari.',
            'garage_id.exists' => 'Garasi yang dipilih tidak ditemukan.',
            'current_battery_kwh.min' => 'Sisa baterai tidak boleh kurang dari 0 kWh.',
            'payment_proof.image' => 'Bukti pelunasan harus berupa gambar.',
            'payment_proof.mimes' => 'Bukti pelunasan harus berformat JPG atau PNG.',
            'payment_proof.max' => 'Ukuran bukti pelunasan maksimal 2 MB.',
            'payment_proof.uploaded' => 'Bukti pelunasan gagal diunggah. Pastikan ukurannya maksimal 2 MB.',
        ];
    }

    /**
     * Nama field dalam bahasa Indonesia (dipakai di :Attribute).
     *
     * @return array<string, string>
     */
    private function attributeNames(): array
    {
        return [
            'vehicle_id' => 'kendaraan',
            'start_date' => 'tanggal mulai',
            'rental_type' => 'tipe sewa',
            'days' => 'jumlah hari',
            'garage_id' => 'garasi',
            'current_battery_kwh' => 'sisa baterai',
            'payment_proof' => 'bukti pelunasan',
        ];
    }
}
