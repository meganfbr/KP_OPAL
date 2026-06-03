<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Inventory extends Model
{
    use HasFactory, HasActivityLog;

    protected $activityModul = 'Inventaris';

    protected $guarded = ['id'];

    public function laboratorium(): BelongsTo
    {
        return $this->belongsTo(Laboratorium::class, 'laboratorium_id');
    }

    public function asal(): BelongsTo
    {
        return $this->belongsTo(Laboratorium::class, 'asal_id');
    }

    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(Laboratorium::class, 'lokasi_id');
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    public function pcDetail(): HasOne
    {
        return $this->hasOne(InventoryPcDetail::class, 'inventory_id');
    }

    public function pcComponents(): HasMany
    {
        return $this->hasMany(InventoryPcComponent::class, 'inventory_id')->orderBy('urutan');
    }

    public function ensureDefaultPcComponents(): void
    {
        foreach (InventoryPcComponent::defaultComponents() as $component) {
            $this->pcComponents()->firstOrCreate(
                [
                    'komponen' => $component['komponen'],
                ],
                [
                    'urutan' => $component['urutan'],
                    'kondisi' => '-',
                    'keterangan' => 'Tidak tersedia',
                ]
            );
        }
    }

    protected static function generateKodeUnique(): string
    {
        $lastKode = self::query()
            ->whereNotNull('kode_unique')
            ->where('kode_unique', 'REGEXP', '^[0-9]+$')
            ->orderByRaw('CAST(kode_unique AS UNSIGNED) DESC')
            ->value('kode_unique');

        $nextNumber = $lastKode ? ((int) $lastKode + 1) : 1;

        return str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    protected static function booted(): void
    {
        static::creating(function (Inventory $inventory) {
            if (empty($inventory->kode_unique)) {
                $inventory->kode_unique = self::generateKodeUnique();
            }

            if (empty($inventory->status)) {
                $inventory->status = 'Aktif';
            }
        });

        static::updating(function (Inventory $inventory) {
            if ($inventory->isDirty('kode_unique') && $inventory->getOriginal('kode_unique')) {
                $inventory->kode_unique = $inventory->getOriginal('kode_unique');
            }
        });

        static::created(function (Inventory $inventory) {
            if (empty($inventory->inventoriable_type)) {
                $inventory->pcDetail()->firstOrCreate([], [
                    'posisi' => 'Client',
                ]);

                $inventory->ensureDefaultPcComponents();
            }
        });
    }
}