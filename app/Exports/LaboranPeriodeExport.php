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
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaboranPeriodeExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    protected int $bulan;
    protected int $tahun;
    protected ?string $statusPeriode;
    protected int $rowNumber = 0;

    public function __construct(int $bulan, int $tahun, ?string $statusPeriode = null)
    {
        $this->bulan = $bulan;
        $this->tahun = $tahun;
        $this->statusPeriode = $statusPeriode;
    }

    /**
     * Determine the period status of a laboran for the selected month/year.
     */
    protected function getStatusPeriode(User $user): string
    {
        $awalBulan = Carbon::create($this->tahun, $this->bulan, 1)->startOfDay();
        $akhirBulan = $awalBulan->copy()->endOfMonth()->endOfDay();

        // Check if contract ends in this exact month/year
        if (
            $user->tanggal_keluar !== null
            && $user->tanggal_keluar->year === $this->tahun
            && $user->tanggal_keluar->month === $this->bulan
        ) {
            return 'Kontrak Berakhir Bulan Ini';
        }

        // Check if active: tanggal_masuk <= akhir_bulan AND (tanggal_keluar null OR >= awal_bulan) AND is_active
        $masukValid = $user->tanggal_masuk === null || $user->tanggal_masuk <= $akhirBulan;
        $keluarValid = $user->tanggal_keluar === null || $user->tanggal_keluar >= $awalBulan;

        if ($masukValid && $keluarValid && $user->is_active) {
            return 'Aktif';
        }

        return 'Tidak Aktif';
    }

    public function collection(): Collection
    {
        $awalBulan = Carbon::create($this->tahun, $this->bulan, 1)->startOfDay();
        $akhirBulan = $awalBulan->copy()->endOfMonth()->endOfDay();

        $query = User::query()->with('roles');

        // Get all users, then apply period status filter in-memory
        // because the status logic involves complex conditions
        $users = $query
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        // Add computed status_periode attribute
        $users = $users->map(function ($user) {
            $user->status_periode_computed = $this->getStatusPeriode($user);
            return $user;
        });

        // Filter by status periode if selected
        if ($this->statusPeriode && $this->statusPeriode !== 'semua') {
            $filterLabel = match ($this->statusPeriode) {
                'aktif' => 'Aktif',
                'tidak_aktif' => 'Tidak Aktif',
                'kontrak_berakhir' => 'Kontrak Berakhir Bulan Ini',
                default => null,
            };

            if ($filterLabel) {
                $users = $users->filter(fn($user) => $user->status_periode_computed === $filterLabel);
            }
        }

        return $users->values();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama',
            'NPP/NIM',
            'Role',
            'Tanggal Masuk',
            'Tanggal Keluar',
            'Status Periode',
            'Status Akun',
        ];
    }

    public function map($user): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $user->name,
            $user->npp ?? '-',
            $user->roles->pluck('name')->implode(', '),
            $user->tanggal_masuk ? $user->tanggal_masuk->format('d/m/Y') : '-',
            $user->tanggal_keluar ? $user->tanggal_keluar->format('d/m/Y') : '-',
            $user->status_periode_computed,
            $user->is_active ? 'Aktif' : 'Nonaktif',
        ];
    }

    public function title(): string
    {
        $namaBulan = Carbon::create($this->tahun, $this->bulan, 1)->translatedFormat('F');
        return "Data Laboran {$namaBulan} {$this->tahun}";
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
