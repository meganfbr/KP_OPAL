<?php

namespace App\Filament\Widgets;

use App\Models\Inventory;
use App\Models\Laboratorium;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Gate;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $totalUser = User::count();
        $totalRuangan = Laboratorium::count();

        // Hitung jumlah PC (Standard: inventoriable_type is null)
        $pcCount = Inventory::whereNull('inventoriable_type')->count();

        // Hitung komponen rusak dari master komponen
        $komponenRusakCount = \App\Models\InventoryPcComponent::whereIn('kondisi', ['Rusak', 'Rusak Berat', 'Kurang Baik', 'Rusak Ringan'])->count();

        // Hitung jumlah PC di Gudang
        $gudangIds = Laboratorium::where('ruang', 'LIKE', '%Gudang%')->orWhere('ruang', 'GD')->pluck('id');
        $pcGudangCount = Inventory::whereNull('inventoriable_type')
            ->whereIn('lokasi_id', $gudangIds)
            ->count();

        return [
            Stat::make('Total Inventaris PC', $pcCount)
                ->description('Jumlah seluruh PC terdaftar')
                ->descriptionIcon('heroicon-m-computer-desktop')
                ->color('primary'),

            Stat::make('Komponen Rusak', $komponenRusakCount)
                ->description('Jumlah perangkat keras rusak')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('danger'),

            Stat::make('Jumlah User', $totalUser)
                ->description('Total pengguna/laboran terdaftar')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('Total Ruangan', $totalRuangan)
                ->description('Total data laboratorium')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('secondary'),

            Stat::make('PC di Gudang', $pcGudangCount)
                ->description('Total PC tersimpan di gudang')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('warning'),
        ];
    }

    // Fungsi untuk memeriksa apakah widget ini dapat ditampilkan
    public static function canView(): bool
    {
        $user = auth()->user();
        return $user && $user->hasRole('super_admin');
    }
}
