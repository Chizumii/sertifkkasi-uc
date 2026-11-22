<?php

namespace App\Models;

use App\Enums\GarageStatus;
use App\Enums\GarageType;
use App\Enums\RentalType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Model ini buat data garasi yang disewakan (tabel garages).
// Kolom id isinya kode garasi, misalnya S001 (sepeda), C001 (mobil), E001 (mobil listrik).
#[Fillable(['id', 'type', 'daily_rate', 'kwh_rate', 'status'])]
class Garage extends Model
{
    use HasFactory;

    // Angka pengali harga bulanan: harga sebulan = 25 x harga harian.
    public const int MONTHLY_PRICE_MULTIPLIER = 25;

    // Nomor garasi paling besar untuk tiap jenis, jadi paling banyak sampai S999, C999, E999.
    public const int MAX_NUMBER = 999;

    // Id garasi itu kode teks (misalnya C001), bukan angka yang nambah otomatis.
    public $incrementing = false;

    // Tipe id garasi adalah teks.
    protected $keyType = 'string';

    // Function ini buat ngubah kolom database ke tipe yang pas (enum, tanggal, angka) waktu datanya dibaca.
    protected function casts(): array
    {
        return [
            'type' => GarageType::class,
            'status' => GarageStatus::class,
            'daily_rate' => 'integer',
            'kwh_rate' => 'integer',
        ];
    }

    // Function ini buat ngambil semua data sewa di garasi ini.
    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class, 'garage_code');
    }

    // Function ini buat ngitung harga sewa sebulan (25 x harga harian).
    public function monthlyPrice(): int
    {
        return $this->daily_rate * self::MONTHLY_PRICE_MULTIPLIER;
    }

    // Function ini buat ngitung biaya sewa garasi.
    // Kalau bulanan pakai harga sebulan, kalau harian = jumlah hari x harga harian.
    public function rentalCost(int $days, RentalType $type): int
    {
        if ($type === RentalType::Monthly) {
            return $this->monthlyPrice();
        }

        return $days * $this->daily_rate;
    }

    // Function ini buat ngecek garasi kosong atau nggak dari tanggal $start sampai sebelum $end.
    // $exceptRentalId dipakai waktu perpanjangan, supaya sewa yang lagi diperpanjang nggak dianggap bentrok sama dirinya sendiri.
    public function isFree(mixed $start, mixed $end, ?int $exceptRentalId = null): bool
    {
        $query = $this->rentals()->overlapping($start, $end);

        if ($exceptRentalId !== null) {
            $query->where('id', '!=', $exceptRentalId);
        }

        return $query->doesntExist();
    }

    // Function ini buat nyari nomor garasi terbesar yang sudah dipakai untuk satu jenis. Contoh: ada C001-C009, hasilnya 9.
    public static function highestNumber(GarageType $type): int
    {
        $highestCode = self::query()->where('type', $type)->max('id');

        if ($highestCode === null) {
            return 0;
        }

        return (int) substr($highestCode, 1);
    }

    // Function ini buat nyusun kode garasi dari jenis dan nomornya. Contoh: mobil listrik nomor 6 jadi E006.
    public static function makeCode(GarageType $type, int $number): string
    {
        return $type->codePrefix().str_pad((string) $number, 3, '0', STR_PAD_LEFT);
    }

    // Function ini buat nyaring garasi supaya yang muncul cuma satu jenis saja.
    #[Scope]
    protected function ofType(Builder $query, GarageType $type): void
    {
        $query->where('type', $type);
    }
}
