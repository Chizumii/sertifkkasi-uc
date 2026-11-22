<?php

namespace App\Models;

use App\Enums\RentalStatus;
use App\Enums\RentalType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

// Model ini buat data sewa garasi (tabel rentals). Penyewanya diambil lewat kendaraan (vehicles.tenant_id).
// Tanggal selesai (end_date) itu hari garasi dikembalikan, jadi nggak ikut dihitung: sewa 3 hari mulai tanggal 1 selesai tanggal 4.
// total_cost sudah termasuk biaya isi listrik untuk mobil listrik di garasi listrik.
#[Fillable([
    'garage_code',
    'vehicle_id',
    'rental_type',
    'days_count',
    'start_date',
    'end_date',
    'total_cost',
    'payment_proof',
    'status',
])]
class Rental extends Model
{
    use HasFactory;

    // Tabel rentals nggak punya kolom created_at dan updated_at, jadi dimatikan.
    public $timestamps = false;

    // Function ini buat ngubah kolom database ke tipe yang pas (enum, tanggal, angka) waktu datanya dibaca.
    protected function casts(): array
    {
        return [
            'rental_type' => RentalType::class,
            'status' => RentalStatus::class,
            'days_count' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'total_cost' => 'integer',
        ];
    }

    // Function ini buat ngambil garasi yang disewa.
    public function garage(): BelongsTo
    {
        return $this->belongsTo(Garage::class, 'garage_code');
    }

    // Function ini buat ngambil kendaraan yang diparkir.
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    // Function ini buat ngambil semua riwayat perpanjangan sewa ini.
    public function extensions(): HasMany
    {
        return $this->hasMany(RentalExtension::class);
    }

    // Function ini buat ngecek sewa ini lagi berjalan hari ini atau nggak.
    public function isActiveToday(): bool
    {
        return $this->start_date->lte(today()) && $this->end_date->gt(today());
    }

    // Function ini buat ngecek sewa ini sudah selesai atau belum (tanggal selesainya sudah tiba atau lewat).
    public function isFinished(): bool
    {
        return $this->end_date->lte(today());
    }

    // Function ini buat ngitung rincian biaya perpanjangan dari tanggal selesai lama sampai $newEndDate.
    // - Kalau tambahannya kurang dari sebulan: dihitung harian (jumlah hari x harga harian).
    // - Kalau sebulan atau lebih: dihitung per bulan, sisa hari dibulatkan ke atas jadi sebulan (bulan x 25 x harga harian).
    public function extensionPreview(Carbon $newEndDate): array
    {
        $oldEndDate = $this->end_date->copy()->startOfDay();
        $newEndDate = $newEndDate->copy()->startOfDay();
        $dailyRate = $this->garage->daily_rate;
        $extensionDays = (int) $oldEndDate->diffInDays($newEndDate);

        if ($extensionDays < $oldEndDate->daysInMonth) {
            $type = RentalType::Daily;
            $months = null;
            $cost = $extensionDays * $dailyRate;
        } else {
            $type = RentalType::Monthly;
            $months = 0;
            $date = $oldEndDate->copy();
            while ($date->lt($newEndDate)) {
                $date->addDays($date->daysInMonth);
                $months++;
            }
            $cost = $months * Garage::MONTHLY_PRICE_MULTIPLIER * $dailyRate;
        }

        return [
            'old_end_date' => $oldEndDate->toDateString(),
            'new_end_date' => $newEndDate->toDateString(),
            'extension_days' => $extensionDays,
            'rental_type_label' => $type->label(),
            'months' => $months,
            'daily_rate' => $dailyRate,
            'additional_cost' => $cost,
        ];
    }

    // Function ini buat nyaring sewa supaya yang muncul cuma milik penyewa tertentu (lewat kendaraannya).
    #[Scope]
    protected function ownedBy(Builder $query, User $tenant): void
    {
        $query->whereHas('vehicle', fn (Builder $vehicles) => $vehicles->where('tenant_id', $tenant->id));
    }

    // Function ini buat nyari sewa yang tanggalnya bentrok dengan $start sampai sebelum $end.
    // Dua sewa dianggap bentrok kalau: mulai < selesai_lain DAN selesai > mulai_lain.
    #[Scope]
    protected function overlapping(Builder $query, mixed $start, mixed $end): void
    {
        $query->where('start_date', '<', Carbon::parse($end)->startOfDay())
            ->where('end_date', '>', Carbon::parse($start)->startOfDay());
    }

    // Function ini buat nyari sewa yang lagi berjalan di tanggal tertentu (tanggal mulai <= tanggal < tanggal selesai).
    #[Scope]
    protected function activeOn(Builder $query, mixed $date): void
    {
        $day = Carbon::parse($date)->startOfDay();

        $query->where('start_date', '<=', $day)
            ->where('end_date', '>', $day);
    }
}
