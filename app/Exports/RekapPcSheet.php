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
            'No PC',
            'Ruang Laboratorium',
            'Kondisi PC',
            'Keterangan Kerusakan',
        ];
    }

    public function map($pc): array
    {
        $ruangLab = $pc->periode?->laboratorium?->ruang ?? '-';

        $issues = collect($pc->spec?->details ?? [])
            ->filter(fn($detail) => !in_array($detail->kondisi, ['Baik', null, '']))
            ->map(fn($detail) => "{$detail->komponen}: " . (!empty($detail->catatan_kondisi) ? $detail->catatan_kondisi : $detail->kondisi))
            ->implode(', ');

        return [
            $pc->no_pc,
            $ruangLab,
            $pc->kondisi,
            $issues ?: '-',
        ];
    }
}
