<?php

namespace App\Models;

use App\Enums\RentalType;
use App\Enums\VehicleType;
use Database\Factories\RentalFactory;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Transaksi sewa garasi.
 *
 * Data kendaraan dan harga disimpan sebagai snapshot saat sewa dibuat.
 * Kolom end_date bersifat eksklusif (sewa 3 hari mulai tanggal 1 berakhir tanggal 4).
 */
#[Fillable([
    'user_id',
    'garage_id',
    'vehicle_id',
    'vehicle_type',
    'plate_number',
    'brand_model',
    'battery_capacity_kwh',
    'current_battery_kwh',
    'rental_type',
    'days',
    'start_date',
    'end_date',
    'daily_price',
    'kwh_price',
    'rental_cost',
    'charging_kwh',
    'charging_cost',
    'total_cost',
    'payment_proof',
])]
class Rental extends Model
{
    /** @use HasFactory<RentalFactory> */
    use HasFactory;

    /**
     * Mendapatkan atribut yang perlu di-cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'vehicle_type' => VehicleType::class,
            'rental_type' => RentalType::class,
            'days' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'battery_capacity_kwh' => 'decimal:2',
            'current_battery_kwh' => 'decimal:2',
            'charging_kwh' => 'decimal:2',
            'daily_price' => 'decimal:2',
            'kwh_price' => 'decimal:2',
            'rental_cost' => 'decimal:2',
            'charging_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
        ];
    }

    /**
     * Penyewa yang melakukan sewa.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Garasi yang disewa.
     *
     * @return BelongsTo<Garage, $this>
     */
    public function garage(): BelongsTo
    {
        return $this->belongsTo(Garage::class);
    }

    /**
     * Kendaraan asal snapshot; bisa null bila kendaraan sudah dihapus.
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
     * Membatasi query pada sewa yang bentrok dengan rentang tanggal [start, end).
     *
     * Dua rentang bentrok bila start < end_lain DAN end > start_lain.
     * Pembanding dikirim sebagai Carbon awal hari agar diformat sama dengan cara cast date menyimpan
     * kolom (SQLite menyimpan "Y-m-d 00:00:00", MySQL kolom DATE), sehingga hasilnya sama di kedua database.
     *
     * @param  Builder<Rental>  $query
     */
    #[Scope]
    protected function overlapping(Builder $query, DateTimeInterface|string $start, DateTimeInterface|string $end): void
    {
        $query->where('start_date', '<', Carbon::parse($end)->startOfDay())
            ->where('end_date', '>', Carbon::parse($start)->startOfDay());
    }

    /**
     * Membatasi query pada sewa yang aktif pada tanggal tertentu (start_date <= tanggal < end_date).
     *
     * @param  Builder<Rental>  $query
     */
    #[Scope]
    protected function activeOn(Builder $query, DateTimeInterface|string $date): void
    {
        $day = Carbon::parse($date)->startOfDay();

        $query->where('start_date', '<=', $day)
            ->where('end_date', '>', $day);
    }
}
