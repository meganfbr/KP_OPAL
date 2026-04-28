<?php

namespace App\Exports;

use App\Models\RekapInventarisNonPc;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class RekapNonPcSheet implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithTitle
{
    protected $periodeId;
    protected $rowNumber = 1;

    public function __construct(int $periodeId)
    {
        $this->periodeId = $periodeId;
    }

    public function title(): string
    {
        return 'Inventaris Non-PC';
    }

    public function collection()
    {
        return RekapInventarisNonPc::query()
            ->where('rekap_inventaris_periode_id', $this->periodeId)
            ->orderBy('nama_barang')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Barang',
            'Merk/Model',
            'Jumlah',
            'Kondisi',
            'Keterangan',
        ];
    }

    public function map($nonpc): array
    {
        return [
            $this->rowNumber++,
            $nonpc->nama_barang,
            $nonpc->merk_model,
            $nonpc->jumlah,
            $nonpc->kondisi,
            $nonpc->keterangan ?: '-',
        ];
    }
}
