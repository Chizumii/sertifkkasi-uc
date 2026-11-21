<?php

namespace App\Models;

use App\Enums\RentalStatus;
use App\Enums\RentalType;
use Database\Factories\RentalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Transaksi sewa garasi. Penyewa diambil lewat kendaraan (vehicles.tenant_id).
 *
 * end_date tidak ikut dihitung: sewa 3 hari mulai tanggal 1 berakhir tanggal 4.
 * total_cost sudah termasuk biaya isi listrik untuk mobil listrik di garasi listrik.
 */
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
    /** @use HasFactory<RentalFactory> */
    use HasFactory;

    /**
     * Tabel rentals tidak punya kolom created_at/updated_at.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return array<string, string>
     */
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

    /**
     * Garasi yang disewa.
     *
     * @return BelongsTo<Garage, $this>
     */
    public function garage(): BelongsTo
    {
        return $this->belongsTo(Garage::class, 'garage_code');
    }

    /**
     * Kendaraan yang diparkir.
     *
     * @return BelongsTo<Vehicle, $this>
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Riwayat perpanjangan sewa.
     *
     * @return HasMany<RentalExtension, $this>
     */
    public function extensions(): HasMany
    {
        return $this->hasMany(RentalExtension::class);
    }

    /**
     * Cek apakah sewa sedang berjalan hari ini.
     */
    public function isActiveToday(): bool
    {
        return $this->start_date->lte(today()) && $this->end_date->gt(today());
    }

    /**
     * Rincian biaya perpanjangan dari tanggal selesai lama sampai $newEndDate.
     *
     * - Hari tambahan kurang dari jumlah hari di bulan tanggal selesai lama: harian (hari x harga harian).
     * - Selain itu bulanan: dihitung per bulan mulai dari tanggal selesai lama (bulan x 25 x harga harian).
     *
     * @return array{old_end_date: string, new_end_date: string, extension_days: int, rental_type_label: string, months: int|null, daily_rate: int, additional_cost: int}
     */
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

    /**
     * Filter sewa milik penyewa tertentu (lewat kendaraannya).
     *
     * @param  Builder<Rental>  $query
     */
    #[Scope]
    protected function ownedBy(Builder $query, User $tenant): void
    {
        $query->whereHas('vehicle', fn (Builder $vehicles) => $vehicles->where('tenant_id', $tenant->id));
    }

    /**
     * Filter sewa yang bentrok dengan tanggal $start sampai sebelum $end.
     * Dua periode bentrok bila: start < end_lain DAN end > start_lain.
     *
     * @param  Builder<Rental>  $query
     */
    #[Scope]
    protected function overlapping(Builder $query, mixed $start, mixed $end): void
    {
        $query->where('start_date', '<', Carbon::parse($end)->startOfDay())
            ->where('end_date', '>', Carbon::parse($start)->startOfDay());
    }

    /**
     * Filter sewa yang berjalan pada tanggal tertentu (start_date <= tanggal < end_date).
     *
     * @param  Builder<Rental>  $query
     */
    #[Scope]
    protected function activeOn(Builder $query, mixed $date): void
    {
        $day = Carbon::parse($date)->startOfDay();

        $query->where('start_date', '<=', $day)
            ->where('end_date', '>', $day);
    }
}
