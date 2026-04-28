<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RekapInventarisNonPc extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function periode(): BelongsTo
    {
        return $this->belongsTo(RekapInventarisPeriode::class, 'rekap_inventaris_periode_id');
    }
}
