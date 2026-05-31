<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryPcComponent extends Model
{
    protected $fillable = [
        'inventory_id',
        'komponen',
        'motherboard_id',
        'processor_id',
        'penyimpanan_id',
        'vga_id',
        'ram_id',
        'dvd_id',
        'keyboard_id',
        'mouse_id',
        'monitor_id',
        'kondisi',
        'keterangan',
        'urutan',
    ];

    protected static function booted(): void
    {
        static::saving(function (InventoryPcComponent $component) {
            $component->clearUnusedHardwareRelations();

            if ($component->kondisi === '-' && blank($component->keterangan)) {
                $component->keterangan = 'Tidak tersedia';
            }
        });
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function motherboard(): BelongsTo
    {
        return $this->belongsTo(Motherboard::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(Processor::class);
    }

    public function penyimpanan(): BelongsTo
    {
        return $this->belongsTo(Penyimpanan::class);
    }

    public function vga(): BelongsTo
    {
        return $this->belongsTo(VGA::class, 'vga_id');
    }

    public function ram(): BelongsTo
    {
        return $this->belongsTo(RAM::class, 'ram_id');
    }

    public function dvd(): BelongsTo
    {
        return $this->belongsTo(DVD::class, 'dvd_id');
    }

    public function keyboard(): BelongsTo
    {
        return $this->belongsTo(Keyboard::class);
    }

    public function mouse(): BelongsTo
    {
        return $this->belongsTo(Mouse::class);
    }

    public function monitor(): BelongsTo
    {
        return $this->belongsTo(Monitor::class);
    }

    public function getDetailMerkAttribute(): string
    {
        return match ($this->komponen) {
            'Motherboard' => $this->motherboard?->merk ?? '-',
            'Processor' => $this->processor?->merk ?? '-',
            'Hardisk' => $this->penyimpanan?->merk ?? '-',
            'VGA' => $this->vga?->merk ?? '-',
            'RAM' => $this->ram?->merk ?? '-',
            'DVD' => $this->dvd?->merk ?? '-',
            'Keyboard' => $this->keyboard?->merk ?? '-',
            'Mouse' => $this->mouse?->merk ?? '-',
            'Monitor' => $this->monitor?->merk ?? '-',
            default => '-',
        };
    }

    public static function defaultComponents(): array
    {
        return [
            ['komponen' => 'Motherboard', 'urutan' => 1],
            ['komponen' => 'Processor', 'urutan' => 2],
            ['komponen' => 'Hardisk', 'urutan' => 3],
            ['komponen' => 'VGA', 'urutan' => 4],
            ['komponen' => 'RAM', 'urutan' => 5],
            ['komponen' => 'DVD', 'urutan' => 6],
            ['komponen' => 'Keyboard', 'urutan' => 7],
            ['komponen' => 'Mouse', 'urutan' => 8],
            ['komponen' => 'Monitor', 'urutan' => 9],
        ];
    }

    protected function clearUnusedHardwareRelations(): void
    {
        if ($this->komponen !== 'Motherboard') {
            $this->motherboard_id = null;
        }

        if ($this->komponen !== 'Processor') {
            $this->processor_id = null;
        }

        if ($this->komponen !== 'Hardisk') {
            $this->penyimpanan_id = null;
        }

        if ($this->komponen !== 'VGA') {
            $this->vga_id = null;
        }

        if ($this->komponen !== 'RAM') {
            $this->ram_id = null;
        }

        if ($this->komponen !== 'DVD') {
            $this->dvd_id = null;
        }

        if ($this->komponen !== 'Keyboard') {
            $this->keyboard_id = null;
        }

        if ($this->komponen !== 'Mouse') {
            $this->mouse_id = null;
        }

        if ($this->komponen !== 'Monitor') {
            $this->monitor_id = null;
        }
    }
}