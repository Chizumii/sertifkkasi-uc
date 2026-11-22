<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Model ini buat data perpanjangan sewa yang dicatat admin (tabel rental_extensions):
// tanggal selesai lama dan baru, biaya tambahan, dan bukti bayarnya.
#[Fillable(['admin_id', 'rental_id', 'previous_end_date', 'new_end_date', 'remaining_cost', 'payment_proof'])]
class RentalExtension extends Model
{
    use HasFactory;

    // Tabel rental_extensions cuma punya kolom created_at, nggak ada updated_at.
    public const UPDATED_AT = null;

    // Function ini buat ngubah kolom database ke tipe yang pas (enum, tanggal, angka) waktu datanya dibaca.
    protected function casts(): array
    {
        return [
            'previous_end_date' => 'date',
            'new_end_date' => 'date',
            'remaining_cost' => 'integer',
        ];
    }

    // Function ini buat ngambil data sewa yang diperpanjang.
    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    // Function ini buat ngambil admin yang mencatat perpanjangan ini.
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
