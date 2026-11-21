<?php

namespace App\Models;

use App\Enums\RentalStatus;
use App\Enums\RentalType;
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
 * Transaksi sewa garasi. Penyewa diambil lewat kendaraan (vehicles.tenant_id).
 *
 * Kolom end_date bersifat eksklusif (sewa 3 hari mulai tanggal 1 berakhir tanggal 4).
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
     * Mendapatkan atribut yang perlu di-cast.
     *
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
     * Membatasi query pada sewa milik penyewa tertentu (lewat kendaraannya).
     *
     * @param  Builder<Rental>  $query
     */
    #[Scope]
    protected function ownedBy(Builder $query, User $tenant): void
    {
        $query->whereHas('vehicle', fn (Builder $vehicles) => $vehicles->where('tenant_id', $tenant->id));
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
