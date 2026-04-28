<?php

namespace App\Exports;

use App\Models\RekapInventarisPc;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class RekapPcSheet implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithTitle
{
    protected $periodeId;
    protected $rowNumber = 1;

    public function __construct(int $periodeId)
    {
        $this->periodeId = $periodeId;
    }

    public function title(): string
    {
        return 'Inventaris PC';
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
            'Monitor',
            'RAM',
            'Processor',
            'Motherboard',
            'Hardisk',
            'VGA',
            'DVD',
            'Keyboard',
            'Mouse',
            'Lokasi',
            'Kondisi PC',
        ];
    }

    public function map($pc): array
    {
        $details = $pc->spec?->details;
        $getKondisi = function($komponen) use ($details) {
            $found = $details?->firstWhere('komponen', $komponen);
            if (!$found) return '-';
            
            $text = $found->kondisi ?: '-';
            if (!empty($found->catatan_kondisi)) {
                $text .= " (" . $found->catatan_kondisi . ")";
            }
            return $text;
        };

        return [
            $this->rowNumber++,
            $pc->no_pc,
            $getKondisi('Monitor'),
            $getKondisi('RAM'),
            $getKondisi('Processor'),
            $getKondisi('Motherboard'),
            $getKondisi('Hardisk'),
            $getKondisi('VGA'),
            $getKondisi('DVD'),
            $getKondisi('Keyboard'),
            $getKondisi('Mouse'),
            $pc->lokasi,
            $pc->kondisi,
        ];
    }
}
