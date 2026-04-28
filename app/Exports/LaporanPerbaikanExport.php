<?php

namespace App\Exports;

use App\Models\LaporanPerbaikan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class LaporanPerbaikanExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $query;
    protected $rowNumber = 1;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function collection()
    {
        return $this->query->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal Pengajuan',
            'No PC',
            'Laboratorium',
            'Komponen Rusak',
            'Keterangan Per Komponen',
            'Prioritas',
            'Status',
            'Keterangan Tambahan',
        ];
    }

    public function map($laporan): array
    {
        $komponen = collect($laporan->komponen_rusak);
        
        $namaKomponen = $komponen->map(function($item) {
            return is_array($item) ? $item['komponen'] : $item;
        })->join(', ');

        $detailKeterangan = $komponen->map(function($item) {
            return is_array($item) && !empty($item['keterangan']) 
                ? "{$item['komponen']}: {$item['keterangan']}" 
                : null;
        })->filter()->join('; ');

        return [
            $this->rowNumber++,
            $laporan->tanggal_pengajuan->format('d/m/Y'),
            $laporan->no_pc,
            $laporan->ruang_lab,
            $namaKomponen,
            $detailKeterangan ?: '-',
            $laporan->prioritas,
            $laporan->status,
            $laporan->keterangan ?: '-',
        ];
    }
}
