<?php

namespace App\Services;

use App\Models\Inventory;
use Illuminate\Support\Facades\DB;

class InventoryPcPeriodService
{
    public static function ensureCurrentPeriodExists(): void
    {
        self::ensurePeriodExists((int) now()->month, (int) now()->year);
    }

    public static function ensurePeriodExists(int $bulan, int $tahun): void
    {
        $exists = Inventory::query()
            ->whereNull('inventoriable_type')
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->exists();

        if ($exists) {
            return;
        }

        $previous = self::latestPreviousPeriod($bulan, $tahun);

        if (! $previous) {
            return;
        }

        DB::transaction(function () use ($bulan, $tahun, $previous) {
            $sourceInventories = Inventory::query()
                ->whereNull('inventoriable_type')
                ->where('bulan', $previous->bulan)
                ->where('tahun', $previous->tahun)
                ->with(['pcDetail', 'pcComponents'])
                ->get();

            foreach ($sourceInventories as $source) {
                $copy = $source->replicate(['id', 'created_at', 'updated_at']);
                $copy->bulan = $bulan;
                $copy->tahun = $tahun;
                $copy->save();

               if ($source->pcDetail) {
                    $copy->pcDetail()->updateOrCreate(
                        ['inventory_id' => $copy->id],
                        ['posisi' => $source->pcDetail->posisi]
                    );
                }

                foreach ($source->pcComponents as $component) {
                    $copy->pcComponents()->updateOrCreate(
                        [
                            'komponen' => $component->komponen,
                        ],
                        [
                            'urutan' => $component->urutan,
                            'motherboard_id' => $component->motherboard_id,
                            'processor_id' => $component->processor_id,
                            'penyimpanan_id' => $component->penyimpanan_id,
                            'vga_id' => $component->vga_id,
                            'ram_id' => $component->ram_id,
                            'dvd_id' => $component->dvd_id,
                            'keyboard_id' => $component->keyboard_id,
                            'mouse_id' => $component->mouse_id,
                            'monitor_id' => $component->monitor_id,
                            'kondisi' => $component->kondisi,
                            'keterangan' => $component->keterangan,
                        ]
                    );
                }
            }
        });

        HardwareUsageCounter::recalculate($bulan, $tahun);
    }

    protected static function latestPreviousPeriod(int $bulan, int $tahun): ?object
    {
        return DB::table('inventories')
            ->whereNull('inventoriable_type')
            ->whereNotNull('bulan')
            ->whereNotNull('tahun')
            ->where(function ($query) use ($bulan, $tahun) {
                $query->where('tahun', '<', $tahun)
                    ->orWhere(function ($query) use ($bulan, $tahun) {
                        $query->where('tahun', $tahun)
                            ->where('bulan', '<', $bulan);
                    });
            })
            ->select('bulan', 'tahun')
            ->groupBy('bulan', 'tahun')
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->first();
    }
}