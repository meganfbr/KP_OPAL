<?php

namespace App\Services;

use App\Models\Inventory;
use Illuminate\Support\Facades\DB;

class InventoryPcIdService
{
    public static function generateNextId(int $bulan, int $tahun): int
    {
        $inventories = Inventory::query()
            ->whereNull('inventoriable_type')
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->get(['kode_inventaris']);

        $max = 0;
        foreach ($inventories as $inventory) {
            if (!empty($inventory->kode_inventaris) && is_numeric($inventory->kode_inventaris)) {
                $num = (int) $inventory->kode_inventaris;
                if ($num > $max) {
                    $max = $num;
                }
            }
        }

        return $max + 1;
    }

    public static function resequence(int $bulan, int $tahun): void
    {
        DB::transaction(function () use ($bulan, $tahun) {
            $inventories = Inventory::query()
                ->whereNull('inventoriable_type')
                ->where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->orderBy('id')
                ->get();

            $number = 1;

            foreach ($inventories as $inventory) {
                $inventory->updateQuietly([
                    'kode_inventaris' => str_pad((string) $number, 3, '0', STR_PAD_LEFT),
                ]);

                $number++;
            }
        });
    }

    public static function format(?int $id): string
    {
        return $id
            ? str_pad((string) $id, 3, '0', STR_PAD_LEFT)
            : '-';
    }
}