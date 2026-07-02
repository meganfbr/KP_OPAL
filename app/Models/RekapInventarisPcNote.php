<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RekapInventarisPcNote extends Model
{
    protected $table = 'rekap_inventaris_pc_notes';

    protected $fillable = [
        'rekap_inventaris_pc_id',
        'komponen',
        'catatan_kondisi',
    ];

    public function pc(): BelongsTo
    {
        return $this->belongsTo(RekapInventarisPc::class, 'rekap_inventaris_pc_id');
    }
}
