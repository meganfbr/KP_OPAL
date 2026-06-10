<?php

namespace App\Services;

use App\Models\Inventory;
use Illuminate\Support\Facades\DB;

class InventoryPcIdService
{
    public static function generateNextId(int $bulan, int $tahun): int
    {
        $max = Inventory::query()
            ->whereNull('inventoriable_type')
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->max('pc_id');

        return ((int) $max) + 1;
    }

    public static function resequence(int $bulan, int $tahun): void
    {
        DB::transaction(function () use ($bulan, $tahun) {
            $inventories = Inventory::query()
                ->whereNull('inventoriable_type')
                ->where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->orderBy('pc_id')
                ->orderBy('id')
                ->get();

            $number = 1;

            foreach ($inventories as $inventory) {
                $inventory->updateQuietly([
                    'pc_id' => $number,
                ]);

                $number++;
            }
        });
    }

    public static function format(?int $pcId): string
    {
        return $pcId
            ? str_pad((string) $pcId, 3, '0', STR_PAD_LEFT)
            : '-';
    }
}