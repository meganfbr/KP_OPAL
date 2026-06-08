<?php

namespace App\Livewire\RekapInventaris;

use App\Models\RekapInventarisPc;
use Filament\Forms\Get;
use Filament\Tables;
use Filament\Tables\Table;
use Livewire\Component;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Notifications\Notification;

class PcTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public int $periodeId;
    public int $bulan;
    public int $tahun;
    public ?int $laboratoriumId = null;

    public function mount(int $periodeId, int $bulan, int $tahun, ?int $laboratoriumId = null): void
    {
        $this->periodeId = $periodeId;
        $this->bulan = $bulan;
        $this->tahun = $tahun;
        $this->laboratoriumId = $laboratoriumId;
    }

    public function updated($propertyName)
    {
        // Reset form state when needed
        if (str_starts_with($propertyName, 'tableFilters')) {
            $this->resetTable();
        }
    }

    public function resetTable()
    {
        $this->resetPage();
    }

    public function closeModal()
    {
        $this->dispatch('close-modal');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                RekapInventarisPc::query()
                    ->where('rekap_inventaris_periode_id', $this->periodeId)
                    ->with('spec.details')
                    ->orderByRaw('CAST(SUBSTRING(no_pc, 2) AS UNSIGNED)')
            )
            ->heading('Rekap PC')
            ->headerActions([
                Tables\Actions\Action::make('sinkronisasi')
                    ->label('Sinkronisasi Data PC')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Sinkronisasi Data PC dari Master')
                    ->modalDescription('Proses ini akan menarik data PC dari master Inventaris sesuai laboratorium yang dipilih. PC yang belum ada pada rekap bulan ini akan ditambahkan otomatis dengan kondisi "Baik".')
                    ->modalSubmitActionLabel('Ya, Sinkronisasi')
                    ->action(function () {
                        $labId = $this->laboratoriumId;
                        if (!$labId) {
                            $periodeInfo = \App\Models\RekapInventarisPeriode::find($this->periodeId);
                            $labId = $periodeInfo?->laboratorium_id;
                        }
                        
                        if (!$labId) {
                            \Filament\Notifications\Notification::make()->title('Gagal')->body('Laboratorium tidak ditemukan.')->danger()->send();
                            return;
                        }
                        
                        $masterPcs = \App\Models\Inventory::whereNull('inventoriable_type')
                            ->where('lokasi_id', $labId)
                            ->whereNotNull('no_pc')
                            ->with('pcComponents')
                            ->get();
                            
                        if ($masterPcs->isEmpty()) {
                            \Filament\Notifications\Notification::make()->title('Info')->body('Tidak ada data PC di master Inventaris untuk laboratorium ini.')->info()->send();
                            return;
                        }

                        $service = resolve(\App\Services\RekapInventarisSpecService::class);
                        $countAdded = 0;

                        foreach ($masterPcs as $master) {
                            $exists = \App\Models\RekapInventarisPc::where('rekap_inventaris_periode_id', $this->periodeId)
                                ->where('no_pc', $master->no_pc)
                                ->exists();

                            if (!$exists) {
                                $details = [];
                                $mapComp = [
                                    'Motherboard' => 1, 'Processor' => 2, 'Hardisk' => 3, 'VGA' => 4,
                                    'RAM' => 5, 'DVD' => 6, 'Keyboard' => 7, 'Mouse' => 8, 'Monitor' => 9
                                ];

                                foreach ($master->pcComponents as $comp) {
                                    $index = $mapComp[$comp->komponen] ?? null;
                                    if ($index) {
                                        $details[$index] = [
                                            'detail' => $comp->detail_merk ?? '-',
                                            'kondisi' => 'Baik',
                                            'catatan_kondisi' => '',
                                        ];
                                    }
                                }
                                
                                $spec = $service->findOrCreate($this->periodeId, $details, 'Baik');

                                \App\Models\RekapInventarisPc::create([
                                    'rekap_inventaris_periode_id' => $this->periodeId,
                                    'rekap_inventaris_spec_id' => $spec->id,
                                    'no_pc' => $master->no_pc,
                                    'lokasi' => $master->pcDetail->posisi ?? 'Client',
                                    'kondisi' => 'Baik',
                                ]);
                                $countAdded++;
                            }
                        }

                        if ($countAdded > 0) {
                            $service->syncPeriodSpecOrder($this->periodeId);
                            \Filament\Notifications\Notification::make()->title('Berhasil')->body("Tersinkronisasi {$countAdded} PC baru.")->success()->send();
                        } else {
                            \Filament\Notifications\Notification::make()->title('Info')->body('Semua PC sudah tersinkronisasi, tidak ada data baru.')->info()->send();
                        }
                    }),
            ])
            ->columns([
                TextColumn::make('no_pc')
                    ->label('No PC')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('periode.laboratorium.ruang')
                    ->label('Ruang Laboratorium')
                    ->state(function (RekapInventarisPc $record) {
                        return \App\Models\Laboratorium::find($this->laboratoriumId ?? $record->periode->laboratorium_id)?->ruang ?? '-';
                    })
                    ->badge()
                    ->color('info'),

                TextColumn::make('kondisi')
                    ->label('Kondisi PC')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Baik' => 'success',
                        'Rusak', 'Rusak Berat' => 'danger',
                        'Kurang Baik', 'Rusak Ringan' => 'warning',
                        'Dalam Perbaikan' => 'info',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): ?string => $state !== 'Baik' ? 'heroicon-m-exclamation-circle' : null),

                TextColumn::make('keterangan_kerusakan')
                    ->label('Keterangan Kerusakan')
                    ->state(function (\App\Models\RekapInventarisPc $record) {
                        $issues = collect($record->spec?->details ?? [])
                            ->filter(fn($detail) => !in_array($detail->kondisi, ['Baik', null, '']))
                            ->map(fn($detail) => "{$detail->komponen}: " . (!empty($detail->catatan_kondisi) ? $detail->catatan_kondisi : $detail->kondisi));
                        return $issues->isEmpty() ? '-' : $issues;
                    })
                    ->listWithLineBreaks()
                    ->bulleted()
                    ->color('danger')
                    ->extraAttributes(['class' => 'text-sm']),
            ])
            ->actions([

                Tables\Actions\EditAction::make()
                    ->modalWidth('7xl')
                    ->visible(!auth()->user()->hasRole('super_admin'))
                    ->fillForm(function (RekapInventarisPc $record): array {
                        $details = [];

                        foreach ($record->spec?->details ?? [] as $detail) {
                            $map = [
                                'Motherboard' => 1,
                                'Processor' => 2,
                                'Hardisk' => 3,
                                'VGA' => 4,
                                'RAM' => 5,
                                'DVD' => 6,
                                'Keyboard' => 7,
                                'Mouse' => 8,
                                'Monitor' => 9,
                            ];

                            $index = $map[$detail->komponen] ?? null;

                            if ($index) {
                                $details[$index] = [
                                    'detail' => $detail->detail,
                                    'kondisi' => $detail->kondisi,
                                    'catatan_kondisi' => $detail->catatan_kondisi,
                                ];
                            }
                        }

                        return [
                            'no_pc_preview' => $record->no_pc,
                            'lokasi' => $record->lokasi,
                            'kondisi' => $record->kondisi,
                            'spec_details' => $details,
                        ];
                    })
                    ->form($this->getPcFormSchema(isEdit: true))
                    ->using(function (RekapInventarisPc $record, array $data) {
                        $periodeId = $this->periodeId;
                        $service = resolve(\App\Services\RekapInventarisSpecService::class);

                        $incomingFingerprint = $service->fingerprintFromDetails(
                            $data['spec_details'] ?? [],
                            $data['kondisi']
                        );

                        if (
                            $record->spec &&
                            $record->spec->fingerprint === $incomingFingerprint
                        ) {
                            $record->update([
                                'lokasi' => $data['lokasi'] ?? $record->lokasi,
                                'kondisi' => $data['kondisi'],
                            ]);

                            $this->updateSpecDetails($record, $data['spec_details'] ?? []);
                        } else {
                            $spec = $service->findOrCreate(
                                $periodeId,
                                $data['spec_details'] ?? [],
                                $data['kondisi']
                            );

                            $record->update([
                                'rekap_inventaris_spec_id' => $spec->id,
                                'lokasi' => $data['lokasi'] ?? $record->lokasi,
                                'kondisi' => $data['kondisi'],
                            ]);
                        }

                        $service->syncPeriodSpecOrder($periodeId);

                        return $record->fresh();
                    }),

                Tables\Actions\DeleteAction::make()
                    ->visible(auth()->user()->hasRole('super_admin'))
                    ->after(function () {
                        $periodeId = $this->periodeId;
                        $service = resolve(\App\Services\RekapInventarisSpecService::class);
                        $service->syncPeriodSpecOrder($periodeId);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('laporkanPerbaikan')
                        ->label('Laporkan Perbaikan')
                        ->icon('heroicon-o-megaphone')
                        ->color('danger')
                        ->visible(!auth()->user()->hasRole('super_admin'))
                        ->modalHeading('Konfirmasi Laporan Perbaikan')
                        ->modalDescription(fn (\Illuminate\Database\Eloquent\Collection $records) => new \Illuminate\Support\HtmlString('Anda akan melaporkan PC berikut: <strong>' . $records->pluck('no_pc')->join(', ') . '</strong>. Apakah Anda yakin ingin melanjutkan?\\nLaporan PDF PTPP akan diunduh setelah konfirmasi.'))
                        ->modalSubmitActionLabel('Kirim & Unduh Laporan')
                        ->modalCancelActionLabel('Batal')
                        ->form([
                            \Filament\Forms\Components\TextInput::make('ketidaksesuaian')
                                ->label('Bentuk Ketidaksesuaian')
                                ->default('Kerusakan Hardware/Software Inventaris')
                                ->required(),
                            \Filament\Forms\Components\TextInput::make('tanggal_kejadian')
                                ->label('Tanggal & Waktu Kejadian')
                                ->placeholder('Contoh: 2 April 2024 Jam: 07.00 - 09.00')
                                ->required(),
                            \Filament\Forms\Components\Textarea::make('tindakan_langsung')
                                ->label('Tindakan Langsung')
                                ->default('Melaporkan kerusakan inventaris ke Super Admin.')
                                ->required(),
                        ])
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records, array $data) {
                            $count = 0;
                            $problematic_pcs = [];
                            $summary_counts = [];
                            $labName = 'Semua Laboratorium';
                            $tableData = [];

                            foreach ($records as $record) {
                                if ($record->kondisi === 'Baik') {
                                    $hasIssue = collect($record->spec?->details ?? [])
                                        ->contains(fn($detail) => !empty($detail->kondisi) && $detail->kondisi !== 'Baik');
                                    if (!$hasIssue) {
                                        continue; 
                                    }
                                }

                                $labId = $this->laboratoriumId ?? $record->periode->laboratorium_id;
                                $labName = \App\Models\Laboratorium::find($labId)?->ruang ?? 'Unknown';

                                $komponenRusak = [];
                                $broken_components_list = [];

                                foreach ($record->spec?->details ?? [] as $detail) {
                                    if (!empty($detail->kondisi) && $detail->kondisi !== 'Baik') {
                                        $komponenRusak[] = [
                                            'komponen' => $detail->komponen,
                                            'kondisi' => $detail->kondisi,
                                            'keterangan' => $detail->catatan_kondisi,
                                        ];
                                        
                                        $broken_components_list[] = $detail->komponen . ' (' . strtolower($detail->kondisi) . ')';
                                        
                                        $komp_name = $detail->komponen;
                                        if (!isset($summary_counts[$komp_name])) {
                                            $summary_counts[$komp_name] = 0;
                                        }
                                        $summary_counts[$komp_name]++;
                                    }
                                }

                                \App\Models\LaporanPerbaikan::create([
                                    'rekap_inventaris_pc_id' => $record->id,
                                    'laboratorium_id' => $labId,
                                    'no_pc' => $record->no_pc,
                                    'ruang_lab' => $labName,
                                    'prioritas' => 'Sedang',
                                    'komponen_rusak' => $komponenRusak,
                                    'keterangan' => $data['tindakan_langsung'],
                                    'status' => 'Pending',
                                    'tanggal_pengajuan' => now()->toDateString(),
                                    'user_id' => auth()->id(),
                                ]);
                                
                                if (count($broken_components_list) > 0) {
                                    $problematic_pcs[] = "- PC " . $record->no_pc . ": " . implode(', ', $broken_components_list);
                                    
                                    $inv = \App\Models\Inventory::where('no_pc', $record->no_pc)
                                        ->where('lokasi_id', $labId)
                                        ->first();
                                    $kodePc = $inv ? $inv->kode_unique : '-';

                                    $tableData[] = [
                                        'no_pc' => $record->no_pc,
                                        'kode_pc' => $kodePc,
                                        'komponen' => implode('<br>', $broken_components_list),
                                        'keterangan' => collect($komponenRusak)->map(fn($k) => ($k['keterangan'] ?: '-'))->implode('<br>'),
                                    ];
                                }
                                $count++;
                            }

                            if ($count === 0) {
                                \Filament\Notifications\Notification::make()
                                    ->title("Tidak ada PC bermasalah yang dapat dilaporkan")
                                    ->warning()
                                    ->send();
                                return;
                            }

                            $uraian = implode("\\n", $problematic_pcs);
                            $perbaikan_list = [];
                            foreach ($summary_counts as $komp => $qty) {
                                $perbaikan_list[] = "- Penggantian $komp ($qty unit)";
                            }
                            $tindakan_perbaikan = implode("\\n", $perbaikan_list);

                            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.laporan-pengajuan', [
                                'nomor' => 'F.LAB.KOM-UDINUS-SH-03-02',
                                'revisi' => '0',
                                'tanggal_berlaku' => '19 September 2022',
                                'ketidaksesuaian' => $data['ketidaksesuaian'],
                                'lab' => $labName,
                                'tanggal' => $data['tanggal_kejadian'],
                                'uraian' => $uraian,
                                'tableData' => $tableData,
                                'tindakan_langsung' => $data['tindakan_langsung'],
                                'tindakan_perbaikan' => $tindakan_perbaikan,
                                'pelapor' => auth()->user()->name,
                                'jabatan_pelapor' => 'Laboran',
                                'admin' => '............................',
                                'jabatan_admin' => 'Super Admin',
                            ])->setPaper('a4', 'portrait');

                            $filename = "PTPP_" . str_replace(' ', '_', $labName) . "_" . date('Ymd_Hi') . ".pdf";

                            return response()->streamDownload(fn () => print($pdf->output()), $filename);
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultPaginationPageOption(10)
            ->paginated([10, 25, 50]);
    }

    protected function getPcFormSchema(bool $isEdit = false): array
    {
        return [
            Grid::make(2)->schema([
                Placeholder::make('no_pc_preview')
                    ->label('No PC')
                    ->content(fn ($state) => $state ?: '-'),

                Select::make('kondisi')
                    ->label('Kondisi PC')
                    ->options([
                        'Baik' => 'Baik',
                        'Kurang Baik' => 'Kurang Baik',
                        'Rusak' => 'Rusak',
                        'Dalam Perbaikan' => 'Dalam Perbaikan',
                    ])
                    ->required(),
            ]),

            Section::make('Detail Spesifikasi')
                ->schema($this->getSpecDetailFormSchema())
                ->columns(1),
        ];
    }

    protected function getSpecDetailFormSchema(): array
    {
        // Map komponen to: [index, model class, label for field]
        $componentConfig = [
            1 => ['label' => 'Motherboard', 'model' => \App\Models\Motherboard::class],
            2 => ['label' => 'Processor',   'model' => \App\Models\Processor::class],
            3 => ['label' => 'Hardisk',     'model' => \App\Models\Penyimpanan::class],
            4 => ['label' => 'VGA',         'model' => \App\Models\VGA::class],
            5 => ['label' => 'RAM',         'model' => \App\Models\RAM::class],
            6 => ['label' => 'DVD',         'model' => \App\Models\DVD::class],
            7 => ['label' => 'Keyboard',    'model' => \App\Models\Keyboard::class],
            8 => ['label' => 'Mouse',       'model' => \App\Models\Mouse::class],
            9 => ['label' => 'Monitor',     'model' => \App\Models\Monitor::class],
        ];

        $schema = [];

        foreach ($componentConfig as $index => $config) {
            $label = $config['label'];
            $modelClass = $config['model'];

            // Build dropdown options: key = full_name string, value = full_name string
            // This keeps the 'detail' column as a string in the database (no extra FK needed)
            $options = $modelClass::all()->mapWithKeys(function ($item) {
                return [$item->full_name => $item->full_name];
            })->toArray();

            $schema[] = Section::make($label)
                ->schema([
                    Grid::make(3)->schema([
                        Select::make("spec_details.$index.detail")
                            ->label('Tipe')
                            ->options($options)
                            ->searchable()
                            ->nullable()
                            ->placeholder('Pilih tipe ' . $label),

                        Select::make("spec_details.$index.kondisi")
                            ->label('Kondisi')
                            ->options([
                                'Baik'       => 'Baik',
                                'Kurang Baik' => 'Kurang Baik',
                                'Rusak'      => 'Rusak',
                            ])
                            ->placeholder('-')
                            ->nullable()
                            ->live(),

                        TextInput::make("spec_details.$index.catatan_kondisi")
                            ->label('Keterangan'),
                    ]),
                ]);
        }

        return $schema;
    }


    protected function generateNextNoPc(): string
    {
        $existing = RekapInventarisPc::query()
            ->where('rekap_inventaris_periode_id', $this->periodeId)
            ->pluck('no_pc')
            ->toArray();

        $numbers = array_map(function ($no) {
            return $this->extractNoPcNumber($no);
        }, $existing);

        sort($numbers);

        $expected = 1;

        foreach ($numbers as $num) {
            if ($num !== $expected) {
                break;
            }
            $expected++;
        }

        return $this->formatNoPc($expected);
    }

    protected function extractNoPcNumber(string $noPc): int
    {
        return (int) preg_replace('/[^0-9]/', '', $noPc);
    }

    protected function formatNoPc(int $number): string
    {
        $prefix = 'B';
        
        if ($this->laboratoriumId) {
            $lab = \App\Models\Laboratorium::find($this->laboratoriumId);
            if ($lab) {
                $prefix = substr($lab->ruang, -1);
            }
        } else {
            // Fallback: try to get from period
            $periode = \App\Models\RekapInventarisPeriode::with('laboratorium')->find($this->periodeId);
            if ($periode && $periode->laboratorium) {
                $prefix = substr($periode->laboratorium->ruang, -1);
            }
        }

        return $prefix . str_pad((string) $number, 2, '0', STR_PAD_LEFT);
    }

    protected function updateSpecDetails(RekapInventarisPc $record, array $details): void
    {
        if (! $record->spec) {
            return;
        }

        $map = [
            1 => 'Motherboard',
            2 => 'Processor',
            3 => 'Hardisk',
            4 => 'VGA',
            5 => 'RAM',
            6 => 'DVD',
            7 => 'Keyboard',
            8 => 'Mouse',
            9 => 'Monitor',
        ];

        foreach ($map as $index => $komponen) {
            $detailRecord = $record->spec->details
                ->firstWhere('komponen', $komponen);

            if (! $detailRecord) {
                continue;
            }

            $detailRecord->update([
                'detail' => trim((string) ($details[$index]['detail'] ?? '')),
                'kondisi' => $details[$index]['kondisi'] ?? null,
                'catatan_kondisi' => trim((string) ($details[$index]['catatan_kondisi'] ?? '')),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.rekap-inventaris.pc-table');
    }
}