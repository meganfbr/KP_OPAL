<?php

namespace App\Exports;

use App\Models\RekapInventarisPc;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class RekapInventarisExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $periodeId;

    public function __construct(int $periodeId)
    {
        $this->periodeId = $periodeId;
    }

    public function collection()
    {
        return RekapInventarisPc::query()
            ->where('rekap_inventaris_periode_id', $this->periodeId)
            ->with(['spec.details'])
            ->orderByRaw('CAST(SUBSTRING(no_pc, 2) AS UNSIGNED)')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'No PC',
            'Kode Spek',
            'Spesifikasi Detail',
            'Lokasi',
            'Kondisi PC',
        ];
    }

    public function map($pc): array
    {
        static $no = 1;
        
        $specText = "";
        if ($pc->spec) {
            foreach ($pc->spec->details as $detail) {
                $specText .= "{$detail->komponen}: {$detail->detail} ({$detail->kondisi})\n";
            }
        }

        return [
            $no++,
            $pc->no_pc,
            $pc->spec?->kode_spek ?? '-',
            trim($specText),
            $pc->lokasi,
            $pc->kondisi,
        ];
    }
}
