<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rental;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

/**
 * Sewa untuk admin: daftar sewa aktif hari ini, detail, dan perpanjangan.
 */
class RentalController extends Controller
{
    /**
     * Sewa yang berjalan hari ini.
     */
    public function index(): View
    {
        $rentals = Rental::query()
            ->activeOn(today())
            ->with(['garage', 'vehicle.tenant'])
            ->orderBy('end_date')
            ->paginate(15);

        return view('admin.rentals.index', ['rentals' => $rentals]);
    }

    /**
     * Detail sewa beserta data penyewa dan semua perpanjangan.
     */
    public function show(Rental $rental): View
    {
        return view('admin.rentals.show', ['rental' => $rental->load(['garage', 'vehicle.tenant', 'extensions.admin'])]);
    }

    /**
     * Form perpanjangan. Bila tanggal selesai baru sudah diisi, tampilkan rincian biayanya.
     */
    public function extend(Request $request, Rental $rental): View|RedirectResponse
    {
        if (! $rental->isActiveToday()) {
            return redirect()->route('admin.rentals.show', $rental)
                ->with('error', 'Hanya sewa yang aktif hari ini yang bisa diperpanjang.');
        }

        $preview = null;

        if ($request->filled('new_end_date')) {
            $validator = Validator::make($request->all(), [
                'new_end_date' => ['date_format:Y-m-d', 'after:'.$rental->end_date->toDateString()],
            ], $this->messages($rental));
            if ($validator->fails()) {
                return redirect()->route('admin.rentals.extend', $rental)->withErrors($validator)->withInput();
            }

            $newEndDate = Carbon::createFromFormat('Y-m-d', $request->input('new_end_date'))->startOfDay();

            $error = $this->checkExtension($rental, $newEndDate);
            if ($error !== null) {
                return redirect()->route('admin.rentals.extend', $rental)->withErrors(['new_end_date' => $error])->withInput();
            }

            $preview = $rental->extensionPreview($newEndDate);
        }

        return view('admin.rentals.extend', [
            'rental' => $rental->load(['garage', 'vehicle', 'extensions']),
            'minNewEndDate' => $rental->end_date->copy()->addDay()->toDateString(),
            'preview' => $preview,
        ]);
    }

    /**
     * Simpan perpanjangan: catat di rental_extensions, lalu ubah tanggal selesai sewa.
     */
    public function storeExtension(Request $request, Rental $rental): RedirectResponse
    {
        $request->validate([
            'new_end_date' => ['required', 'date_format:Y-m-d', 'after:'.$rental->end_date->toDateString()],
            'payment_proof' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ], $this->messages($rental), [
            'new_end_date' => 'tanggal selesai baru',
            'payment_proof' => 'bukti bayar',
        ]);

        if (! $rental->isActiveToday()) {
            return back()->withInput()->withErrors(['new_end_date' => 'Hanya sewa yang aktif hari ini yang bisa diperpanjang.']);
        }

        $newEndDate = Carbon::createFromFormat('Y-m-d', $request->input('new_end_date'))->startOfDay();

        $error = $this->checkExtension($rental, $newEndDate);
        if ($error !== null) {
            return back()->withInput()->withErrors(['new_end_date' => $error]);
        }

        $preview = $rental->extensionPreview($newEndDate);

        $rental->extensions()->create([
            'admin_id' => $request->user()->id,
            'previous_end_date' => $rental->end_date->toDateString(),
            'new_end_date' => $newEndDate->toDateString(),
            'remaining_cost' => $preview['additional_cost'],
            'payment_proof' => $request->file('payment_proof')->store('payment-proofs', 'public'),
        ]);

        $rental->update(['end_date' => $newEndDate->toDateString()]);

        return redirect()
            ->route('admin.rentals.show', $rental)
            ->with('success', 'Sewa diperpanjang sampai '.$newEndDate->translatedFormat('j F Y').'.');
    }

    /**
     * Cek periode tambahan (tanggal selesai lama sampai tanggal selesai baru).
     * Mengembalikan pesan error, atau null bila aman diperpanjang.
     */
    private function checkExtension(Rental $rental, Carbon $newEndDate): ?string
    {
        $garage = $rental->garage;
        $vehicle = $rental->vehicle;

        if (! $garage->isFree($rental->end_date, $newEndDate, $rental->id)) {
            return "Garasi {$garage->id} sudah disewa penyewa lain sebelum ".$newEndDate->translatedFormat('j F Y').'. Pilih tanggal yang lebih awal.';
        }

        if ($vehicle->isBusy($rental->end_date, $newEndDate)) {
            return "{$vehicle->brand_model} sudah dijadwalkan di garasi lain pada periode tambahan ini.";
        }

        return null;
    }

    /**
     * Pesan error validasi dalam bahasa Indonesia.
     *
     * @return array<string, string>
     */
    private function messages(Rental $rental): array
    {
        return [
            'required' => ':Attribute wajib diisi.',
            'new_end_date.date_format' => 'Format tanggal selesai baru tidak valid.',
            'new_end_date.after' => 'Tanggal selesai baru harus setelah '.$rental->end_date->translatedFormat('j F Y').'.',
            'payment_proof.image' => 'Bukti bayar harus berupa gambar.',
            'payment_proof.mimes' => 'Bukti bayar harus berformat JPG atau PNG.',
            'payment_proof.max' => 'Ukuran bukti bayar maksimal 2 MB.',
            'payment_proof.uploaded' => 'Bukti bayar gagal diunggah. Pastikan ukurannya maksimal 2 MB.',
        ];
    }
}
