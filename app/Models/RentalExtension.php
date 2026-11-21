<?php

namespace App\Models;

use Database\Factories\RentalExtensionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Catatan perpanjangan sewa: perubahan tanggal selesai, biaya tambahan, dan bukti bayar.
 */
#[Fillable(['rental_id', 'old_end_date', 'new_end_date', 'additional_cost', 'payment_proof'])]
class RentalExtension extends Model
{
    /** @use HasFactory<RentalExtensionFactory> */
    use HasFactory;

    /**
     * Mendapatkan atribut yang perlu di-cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'old_end_date' => 'date',
            'new_end_date' => 'date',
            'additional_cost' => 'decimal:2',
        ];
    }

    /**
     * Sewa yang diperpanjang.
     *
     * @return BelongsTo<Rental, $this>
     */
    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }
}
