<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RekapInventarisExport implements WithMultipleSheets
{
    protected $periodeId;

    public function __construct(int $periodeId)
    {
        $this->periodeId = $periodeId;
    }

    public function sheets(): array
    {
        return [
            new RekapPcSheet($this->periodeId),
            new RekapNonPcSheet($this->periodeId),
        ];
    }
}
