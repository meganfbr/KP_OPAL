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
                    $copy->pcDetail()->create([
                        'posisi' => $source->pcDetail->posisi,
                    ]);
                }

                foreach ($source->pcComponents as $component) {
                    $newComponent = $component->replicate(['id', 'inventory_id', 'created_at', 'updated_at']);
                    $newComponent->inventory_id = $copy->id;
                    $newComponent->save();
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