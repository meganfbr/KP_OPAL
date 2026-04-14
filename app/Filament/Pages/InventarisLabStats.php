<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Inventory;
use App\Models\Laboratorium;
use Illuminate\Support\Facades\Auth;

class InventarisLabStats extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationGroup = 'Inventaris';
    protected static ?string $navigationLabel = 'Tampilan Inventaris Lab';
    protected static ?string $title = 'Rekapitulasi Inventaris Per Lab';

    protected static string $view = 'filament.pages.inventaris-lab-stats';

    public function getViewData(): array
    {
        // Jika ada permission check, bisa tambahkan di canAccess()
        $labs = Laboratorium::with('software')->get();
        // Load semua inventory berserta detailnya
        $inventories = Inventory::with(['inventoriable'])->get();

        $data = [];

        foreach ($labs as $lab) {
            $labInvs = $inventories->where('laboratorium_id', $lab->id);
            
            // Hitung PC
            $pcCount = $labInvs->where('inventoriable_type', 'App\Models\PCDetail')->count();
            
            // Kelompokkan Non-PC
            $nonPcs = [];
            foreach ($labInvs->where('inventoriable_type', 'App\Models\NonPCDetail') as $inv) {
                // $inv->inventoriable bisa null jika relasi terhapus, pastikan fallback
                $nama = $inv->inventoriable->nama ?? $inv->nama_barang ?? 'Non-PC Item';
                $model = $inv->inventoriable->model ?? '-';
                $key = $nama . '|' . $model;
                
                if (!isset($nonPcs[$key])) {
                    $nonPcs[$key] = [
                        'nama' => $nama,
                        'versi' => 'Model: ' . $model,
                        'qty' => 0
                    ];
                }
                $nonPcs[$key]['qty']++;
            }

            // Kelompokkan Software dari Inventory table
            $softwares = [];
            foreach ($labInvs->where('inventoriable_type', 'App\Models\SoftwareDetail') as $inv) {
                $nama = $inv->inventoriable->nama ?? $inv->nama_barang ?? 'Software Item';
                $versi = $inv->inventoriable->versi ?? '-';
                $key = $nama . '|' . $versi;
                
                if (!isset($softwares[$key])) {
                    $softwares[$key] = [
                        'nama' => $nama,
                        'versi' => 'Versi: ' . $versi,
                        'qty' => 0
                    ];
                }
                $softwares[$key]['qty']++;
            }

            // Gabungkan juga dari relasi lab_software (pivot) jika digunakan
            if ($lab->software) {
                foreach ($lab->software as $sw) {
                    $nama = $sw->nama ?? 'Unknown Software';
                    $versi = $sw->pivot->version ?? $sw->versi ?? '-';
                    $key = $nama . '|' . $versi;
                    
                    if (!isset($softwares[$key])) {
                        $softwares[$key] = [
                            'nama' => $nama,
                            'versi' => 'Versi: ' . $versi,
                            'qty' => 'Terinstal (Pivot)'
                        ];
                    }
                }
            }

            $data[] = [
                'ruang' => $lab->ruang,
                'pc_count' => $pcCount,
                'non_pcs' => array_values($nonPcs),
                'softwares' => array_values($softwares),
            ];
        }

        return ['labData' => $data];
    }
}
