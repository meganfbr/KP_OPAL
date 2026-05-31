<?php

namespace App\Services;

use App\Models\DVD;
use App\Models\Keyboard;
use App\Models\Monitor;
use App\Models\Motherboard;
use App\Models\Mouse;
use App\Models\Penyimpanan;
use App\Models\Processor;
use App\Models\RAM;
use App\Models\VGA;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class HardwareUsageCounter
{
    public static function recalculate(?int $bulan = null, ?int $tahun = null): void
    {
        if (! $bulan || ! $tahun) {
            [$bulan, $tahun] = self::getLatestPeriod();
        }

        self::resetAllHardwareStock();

        self::updateStockFor(Motherboard::class, 'motherboard_id', $bulan, $tahun);
        self::updateStockFor(Processor::class, 'processor_id', $bulan, $tahun);
        self::updateStockFor(Penyimpanan::class, 'penyimpanan_id', $bulan, $tahun);
        self::updateStockFor(VGA::class, 'vga_id', $bulan, $tahun);
        self::updateStockFor(RAM::class, 'ram_id', $bulan, $tahun);
        self::updateStockFor(DVD::class, 'dvd_id', $bulan, $tahun);
        self::updateStockFor(Keyboard::class, 'keyboard_id', $bulan, $tahun);
        self::updateStockFor(Mouse::class, 'mouse_id', $bulan, $tahun);
        self::updateStockFor(Monitor::class, 'monitor_id', $bulan, $tahun);
    }

    public static function countUsage(Model $record, string $componentColumn, ?int $bulan = null, ?int $tahun = null): int
    {
        if (! $bulan || ! $tahun) {
            [$bulan, $tahun] = self::getLatestPeriod();
        }

        return (int) DB::table('inventory_pc_components')
            ->join('inventories', 'inventories.id', '=', 'inventory_pc_components.inventory_id')
            ->whereNull('inventories.inventoriable_type')
            ->where('inventories.bulan', $bulan)
            ->where('inventories.tahun', $tahun)
            ->where("inventory_pc_components.{$componentColumn}", $record->getKey())
            ->count();
    }

    protected static function getLatestPeriod(): array
    {
        $latest = DB::table('inventories')
            ->whereNull('inventoriable_type')
            ->whereNotNull('bulan')
            ->whereNotNull('tahun')
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->first(['bulan', 'tahun']);

        return $latest
            ? [(int) $latest->bulan, (int) $latest->tahun]
            : [(int) now()->month, (int) now()->year];
    }

    protected static function resetAllHardwareStock(): void
    {
        Motherboard::query()->update(['stok' => 0]);
        Processor::query()->update(['stok' => 0]);
        Penyimpanan::query()->update(['stok' => 0]);
        VGA::query()->update(['stok' => 0]);
        RAM::query()->update(['stok' => 0]);
        DVD::query()->update(['stok' => 0]);
        Keyboard::query()->update(['stok' => 0]);
        Mouse::query()->update(['stok' => 0]);
        Monitor::query()->update(['stok' => 0]);
    }

    protected static function updateStockFor(string $modelClass, string $componentColumn, int $bulan, int $tahun): void
    {
        $counts = DB::table('inventory_pc_components')
            ->join('inventories', 'inventories.id', '=', 'inventory_pc_components.inventory_id')
            ->whereNull('inventories.inventoriable_type')
            ->where('inventories.bulan', $bulan)
            ->where('inventories.tahun', $tahun)
            ->whereNotNull("inventory_pc_components.{$componentColumn}")
            ->select("inventory_pc_components.{$componentColumn} as hardware_id", DB::raw('COUNT(*) as total'))
            ->groupBy("inventory_pc_components.{$componentColumn}")
            ->pluck('total', 'hardware_id');

        foreach ($counts as $hardwareId => $total) {
            $modelClass::query()->whereKey($hardwareId)->update(['stok' => (int) $total]);
        }
    }
}