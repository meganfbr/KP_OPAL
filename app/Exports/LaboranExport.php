<?php

namespace App\Exports;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaboranExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected array $filters;
    protected int $rowNumber = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection(): Collection
    {
        $query = User::query()->with('roles');

        // Filter Status (is_active)
        $status = $this->filters['status']['value'] ?? null;
        if ($status === 'aktif') {
            $query->where('is_active', true);
        } elseif ($status === 'nonaktif') {
            $query->where('is_active', false);
        }

        // Filter Status Kontrak
        $kontrak = $this->filters['status_kontrak']['value'] ?? null;
        if ($kontrak === 'berakhir_bulan_ini') {
            $query->whereNotNull('tanggal_keluar')
                ->whereYear('tanggal_keluar', Carbon::now()->year)
                ->whereMonth('tanggal_keluar', Carbon::now()->month);
        } elseif ($kontrak === 'berakhir_tahun_ini') {
            $query->whereNotNull('tanggal_keluar')
                ->whereYear('tanggal_keluar', Carbon::now()->year);
        }

        // Filter Role
        $roles = $this->filters['roles']['values'] ?? null;
        if (!empty($roles)) {
            $query->whereHas('roles', function ($q) use ($roles) {
                $q->whereIn('id', $roles);
            });
        }

        // Filter Tanggal Masuk (dari - sampai)
        $masukDari = $this->filters['tanggal_masuk']['tanggal_masuk_dari'] ?? null;
        $masukSampai = $this->filters['tanggal_masuk']['tanggal_masuk_sampai'] ?? null;
        if ($masukDari) {
            $query->whereDate('tanggal_masuk', '>=', $masukDari);
        }
        if ($masukSampai) {
            $query->whereDate('tanggal_masuk', '<=', $masukSampai);
        }

        // Filter Tanggal Keluar (dari - sampai)
        $keluarDari = $this->filters['tanggal_keluar']['tanggal_keluar_dari'] ?? null;
        $keluarSampai = $this->filters['tanggal_keluar']['tanggal_keluar_sampai'] ?? null;
        if ($keluarDari) {
            $query->whereDate('tanggal_keluar', '>=', $keluarDari);
        }
        if ($keluarSampai) {
            $query->whereDate('tanggal_keluar', '<=', $keluarSampai);
        }

        return $query
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama',
            'Email',
            'NPP/NIM',
            'Role',
            'No HP',
            'Tanggal Masuk',
            'Tanggal Keluar',
            'Status',
        ];
    }

    public function map($user): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $user->name,
            $user->email,
            $user->npp,
            $user->roles->pluck('name')->implode(', '),
            $user->no_phone ?? '-',
            $user->tanggal_masuk ? $user->tanggal_masuk->format('d/m/Y') : '-',
            $user->tanggal_keluar ? $user->tanggal_keluar->format('d/m/Y') : '-',
            $user->is_active ? 'Aktif' : 'Nonaktif',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Header row: bold with background color
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'],
                ],
            ],
        ];
    }
}
