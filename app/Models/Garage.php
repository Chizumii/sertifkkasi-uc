<?php

namespace App\Models;

use App\Enums\GarageStatus;
use App\Enums\GarageType;
use App\Enums\RentalType;
use Database\Factories\GarageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Garasi yang disewakan. Kolom id berisi kode garasi, misalnya S001 (sepeda), C001 (mobil), E001 (mobil listrik).
 */
#[Fillable(['id', 'type', 'daily_rate', 'kwh_rate', 'status'])]
class Garage extends Model
{
    /** @use HasFactory<GarageFactory> */
    use HasFactory;

    /**
     * Harga sewa bulanan = 25 x harga harian.
     */
    public const int MONTHLY_PRICE_MULTIPLIER = 25;

    /**
     * Nomor kode garasi paling besar per tipe (S999, C999, E999).
     */
    public const int MAX_NUMBER = 999;

    /**
     * Primary key berupa kode garasi (teks), bukan angka berurutan.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    protected $keyType = 'string';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => GarageType::class,
            'status' => GarageStatus::class,
            'daily_rate' => 'integer',
            'kwh_rate' => 'integer',
        ];
    }

    /**
     * Semua sewa di garasi ini.
     *
     * @return HasMany<Rental, $this>
     */
    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class, 'garage_code');
    }

    /**
     * Harga sewa satu bulan.
     */
    public function monthlyPrice(): int
    {
        return $this->daily_rate * self::MONTHLY_PRICE_MULTIPLIER;
    }

    /**
     * Biaya sewa garasi: bulanan = harga bulanan, harian = jumlah hari x harga harian.
     */
    public function rentalCost(int $days, RentalType $type): int
    {
        if ($type === RentalType::Monthly) {
            return $this->monthlyPrice();
        }

        return $days * $this->daily_rate;
    }

    /**
     * Cek apakah garasi kosong pada tanggal $start sampai sebelum $end.
     * $exceptRentalId dipakai saat perpanjangan, supaya sewa yang sedang diperpanjang tidak dihitung bentrok.
     */
    public function isFree(mixed $start, mixed $end, ?int $exceptRentalId = null): bool
    {
        $query = $this->rentals()->overlapping($start, $end);

        if ($exceptRentalId !== null) {
            $query->where('id', '!=', $exceptRentalId);
        }

        return $query->doesntExist();
    }

    /**
     * Nomor kode tertinggi yang sudah dipakai untuk satu tipe. Contoh: ada C001-C009 maka hasilnya 9.
     */
    public static function highestNumber(GarageType $type): int
    {
        $highestCode = self::query()->where('type', $type)->max('id');

        if ($highestCode === null) {
            return 0;
        }

        return (int) substr($highestCode, 1);
    }

    /**
     * Menyusun kode garasi dari tipe dan nomor. Contoh: (Electric, 6) menjadi E006.
     */
    public static function makeCode(GarageType $type, int $number): string
    {
        return $type->codePrefix().str_pad((string) $number, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Filter garasi berdasarkan tipe.
     *
     * @param  Builder<Garage>  $query
     */
    #[Scope]
    protected function ofType(Builder $query, GarageType $type): void
    {
        $query->where('type', $type);
    }
}
