<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Exports\LaboranExport;
use App\Exports\LaboranPeriodeExport;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('downloadExcel')
                ->label('Download Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(fn () => $this->exportToExcel()),

            Actions\Action::make('downloadPeriode')
                ->label('Download Per Periode')
                ->icon('heroicon-o-calendar-days')
                ->color('info')
                ->form([
                    Select::make('bulan')
                        ->label('Bulan')
                        ->options([
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
                            4 => 'April', 5 => 'Mei', 6 => 'Juni',
                            7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                        ])
                        ->default(now()->month)
                        ->required(),
                    Select::make('tahun')
                        ->label('Tahun')
                        ->options(function () {
                            $tahunSekarang = (int) date('Y');
                            $options = [];
                            for ($y = $tahunSekarang - 3; $y <= $tahunSekarang + 2; $y++) {
                                $options[$y] = (string) $y;
                            }
                            return $options;
                        })
                        ->default(now()->year)
                        ->required(),
                    Select::make('status_periode')
                        ->label('Status Periode')
                        ->options([
                            'semua' => 'Semua',
                            'aktif' => 'Aktif',
                            'tidak_aktif' => 'Tidak Aktif',
                            'kontrak_berakhir' => 'Kontrak Berakhir Bulan Ini',
                        ])
                        ->default('semua')
                        ->required(),
                ])
                ->modalHeading('Download Data Laboran Per Periode')
                ->modalDescription('Pilih periode bulan/tahun dan status untuk di-export.')
                ->modalSubmitActionLabel('Download')
                ->action(function (array $data) {
                    $bulan = (int) $data['bulan'];
                    $tahun = (int) $data['tahun'];
                    $statusPeriode = $data['status_periode'];

                    $namaBulan = Carbon::create($tahun, $bulan, 1)->translatedFormat('F');
                    $statusLabel = match ($statusPeriode) {
                        'aktif' => '_Aktif',
                        'tidak_aktif' => '_TidakAktif',
                        'kontrak_berakhir' => '_KontrakBerakhir',
                        default => '',
                    };

                    $fileName = "Data_Laboran_{$namaBulan}_{$tahun}{$statusLabel}.xlsx";

                    return Excel::download(
                        new LaboranPeriodeExport($bulan, $tahun, $statusPeriode),
                        $fileName
                    );
                }),

            Actions\CreateAction::make(),
        ];
    }

    /**
     * Export data laboran ke Excel berdasarkan filter yang sedang aktif.
     */
    public function exportToExcel()
    {
        $filters = $this->tableFilters ?? [];

        $fileName = 'Data_Laboran_' . date('Y-m-d') . '.xlsx';

        return Excel::download(new LaboranExport($filters), $fileName);
    }

    /**
     * Override the table query to sort active users first,
     * then by tanggal_keluar (nulls last among active).
     */
    protected function getTableQuery(): ?Builder
    {
        return parent::getTableQuery()
            ->orderByDesc('is_active')
            ->orderByRaw('CASE WHEN tanggal_keluar IS NULL THEN 1 ELSE 0 END')
            ->orderBy('tanggal_keluar');
    }
}
