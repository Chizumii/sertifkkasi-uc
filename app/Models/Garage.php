<?php

namespace App\Models;

use App\Enums\GarageStatus;
use App\Enums\GarageType;
use Database\Factories\GarageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Garasi yang disewakan, dengan kode S001 (sepeda), C001 (mobil), atau E001 (mobil listrik).
 */
#[Fillable(['code', 'type', 'daily_price', 'kwh_price', 'status'])]
class Garage extends Model
{
    /** @use HasFactory<GarageFactory> */
    use HasFactory;

    /**
     * Kelipatan harga harian untuk menentukan harga sewa bulanan.
     */
    public const int MONTHLY_PRICE_MULTIPLIER = 25;

    /**
     * Mendapatkan atribut yang perlu di-cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => GarageType::class,
            'status' => GarageStatus::class,
            'daily_price' => 'decimal:2',
            'kwh_price' => 'decimal:2',
        ];
    }

    /**
     * Riwayat sewa pada garasi ini.
     *
     * @return HasMany<Rental, $this>
     */
    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    /**
     * Harga sewa bulanan, yaitu 25 x harga harian.
     */
    public function monthlyPrice(): string
    {
        return number_format((float) $this->daily_price * self::MONTHLY_PRICE_MULTIPLIER, 2, '.', '');
    }

    /**
     * Membatasi query hanya pada garasi dengan tipe tertentu.
     *
     * @param  Builder<Garage>  $query
     */
    #[Scope]
    protected function ofType(Builder $query, GarageType $type): void
    {
        $query->where('type', $type);
    }
}
