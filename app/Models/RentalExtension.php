<?php

namespace App\Models;

use Database\Factories\RentalExtensionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Catatan perpanjangan sewa oleh admin: tanggal selesai lama dan baru, biaya tambahan, dan bukti bayar.
 */
#[Fillable(['admin_id', 'rental_id', 'previous_end_date', 'new_end_date', 'remaining_cost', 'payment_proof'])]
class RentalExtension extends Model
{
    /** @use HasFactory<RentalExtensionFactory> */
    use HasFactory;

    /**
     * Tabel rental_extensions hanya punya created_at.
     */
    public const UPDATED_AT = null;

    /**
     * Mendapatkan atribut yang perlu di-cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'previous_end_date' => 'date',
            'new_end_date' => 'date',
            'remaining_cost' => 'integer',
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

    /**
     * Admin yang mencatat perpanjangan.
     *
     * @return BelongsTo<User, $this>
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
