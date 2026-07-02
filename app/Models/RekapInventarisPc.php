<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class RekapInventarisPc extends Model
{
    use LogsActivity;

    protected $table = 'rekap_inventaris_pcs';

    protected $fillable = [
        'rekap_inventaris_periode_id',
        'rekap_inventaris_spec_id',
        'no_pc',
        'lokasi',
        'kondisi',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['no_pc', 'lokasi', 'kondisi', 'rekap_inventaris_spec_id'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Rekap PC telah di-{$eventName}")
            ->useLogName('rekap-inventaris');
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(RekapInventarisPeriode::class, 'rekap_inventaris_periode_id');
    }

    public function spec(): BelongsTo
    {
        return $this->belongsTo(RekapInventarisSpec::class, 'rekap_inventaris_spec_id');
    }

    /**
     * Catatan/keterangan per komponen yang spesifik untuk PC ini.
     * Dipisah dari spec agar tidak tercampur antar PC yang share spec yang sama.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(RekapInventarisPcNote::class, 'rekap_inventaris_pc_id');
    }

    /**
     * Ambil catatan untuk komponen tertentu. Return null jika tidak ada.
     */
    public function getNoteForKomponen(string $komponen): ?string
    {
        return $this->notes->firstWhere('komponen', $komponen)?->catatan_kondisi;
    }
}