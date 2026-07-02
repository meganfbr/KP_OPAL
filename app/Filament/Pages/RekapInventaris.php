<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use App\Models\RekapInventarisPc;
use App\Models\RekapInventarisSpec;
use App\Models\RekapInventarisPeriode;
use App\Models\RekapInventarisSpecDetail;
use App\Models\RekapInventarisNonPc;
use App\Models\Laboratorium;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Form;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RekapInventarisExport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Filament\Resources\LaporanPerbaikanResource;

class RekapInventaris extends Page implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;

    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';
    protected static string $view = 'filament.pages.rekap-inventaris';
    protected static ?string $title = 'Rekap Inventaris';
    protected static ?string $slug = 'rekap-inventaris';
    protected static ?string $navigationGroup = 'Inventaris';

    public ?int $periodeId = null;
    public ?int $bulan = null;
    public ?int $tahun = null;
    public ?int $laboratoriumId = null;
    public ?string $laboratoriumNama = null;

    public ?array $data = [];
    
    public function getMaxContentWidth(): string
    {
        return 'full';
    }

    public function mount(): void
    {
        $now = Carbon::now();
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super_admin');

        // 1. Tentukan Laboratorium
        if ($isSuperAdmin) {
            $this->laboratoriumId = (int) request()->query('lab', Laboratorium::first()?->id);
        } else {
            // Get all lab IDs for this laboran based on their roles
            $laboranRoles = $user->roles->filter(fn ($r) => str_starts_with($r->name, 'Laboran_'));
            $authorizedLabIds = [];
            foreach ($laboranRoles as $role) {
                $labSlug = str_replace('Laboran_', '', $role->name);
                $labInfo = Laboratorium::where('ruang', 'LAB ' . strtoupper($labSlug))->first();
                if ($labInfo) {
                    $authorizedLabIds[] = $labInfo->id;
                }
            }

            $requestedLab = (int) request()->query('lab');
            if ($requestedLab && in_array($requestedLab, $authorizedLabIds)) {
                $this->laboratoriumId = $requestedLab;
            } elseif (count($authorizedLabIds) > 0) {
                $this->laboratoriumId = $authorizedLabIds[0];
            }
        }

        // Update nama lab
        $labInfo = Laboratorium::find($this->laboratoriumId);
        $this->laboratoriumNama = $labInfo?->ruang;

        // 2. Tentukan Periode
        $this->bulan = (int) request()->query('bulan', $now->month);
        $this->tahun = (int) request()->query('tahun', $now->year);

        if (request()->query('periode_id')) {
            $this->periodeId = (int) request()->query('periode_id');
            $periode = RekapInventarisPeriode::find($this->periodeId);
            if ($periode) {
                $this->bulan = $periode->bulan;
                $this->tahun = $periode->tahun;
                // Super admin: update lab context berdasarkan periode yang dipilih
                if ($isSuperAdmin && $periode->laboratorium_id) {
                    $this->laboratoriumId = $periode->laboratorium_id;
                    $this->laboratoriumNama = $periode->laboratorium?->ruang;
                }
            }
        } else {
            if ($isSuperAdmin) {
                // Super admin: tidak auto-create, cari berdasarkan bulan/tahun yang dipilih
                $periode = RekapInventarisPeriode::where('laboratorium_id', $this->laboratoriumId)
                    ->where('bulan', $this->bulan)
                    ->where('tahun', $this->tahun)
                    ->first();

                if ($periode) {
                    $this->periodeId = $periode->id;
                    $this->bulan = $periode->bulan;
                    $this->tahun = $periode->tahun;
                } else {
                    $this->periodeId = null;
                }
            } else {
                $this->syncPeriodeForCurrentLab();
            }
        }

        $this->form->fill([
            'lab_id' => $this->laboratoriumId,
        ]);
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        if ($user->hasRole('super_admin')) return true;
        return $user->roles->pluck('name')->contains(fn ($n) => str_starts_with($n, 'Laboran_'));
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Grid::make(1)
                    ->schema([
                        Select::make('lab_id')
                            ->label('Pilih Ruangan')
                            ->options(function () {
                                $user = auth()->user();
                                if ($user->hasRole('super_admin')) {
                                    return Laboratorium::orderBy('ruang')->pluck('ruang', 'id');
                                }
                                
                                $laboranRoles = $user->roles->filter(fn ($r) => str_starts_with($r->name, 'Laboran_'));
                                $authorizedLabNames = [];
                                foreach ($laboranRoles as $role) {
                                    $labSlug = str_replace('Laboran_', '', $role->name);
                                    $authorizedLabNames[] = 'LAB ' . strtoupper($labSlug);
                                }
                                
                                return Laboratorium::whereIn('ruang', $authorizedLabNames)->orderBy('ruang')->pluck('ruang', 'id');
                            })
                            ->searchable()
                            ->required()
                            ->live()
                            ->hidden(function () {
                                $user = auth()->user();
                                if ($user->hasRole('super_admin')) {
                                    return false;
                                }
                                
                                $laboranRoles = $user->roles->filter(fn ($r) => str_starts_with($r->name, 'Laboran_'));
                                return $laboranRoles->count() <= 1;
                            })
                            ->afterStateUpdated(function ($state) {
                                $this->laboratoriumId = $state;
                                $labInfo = Laboratorium::find($this->laboratoriumId);
                                $this->laboratoriumNama = $labInfo?->ruang;
                                $this->syncPeriodeForCurrentLab();
                            }),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('pilih_periode')
                ->label('Pilih Periode')
                ->icon('heroicon-o-calendar-days')
                ->form([
                    Select::make('bulan')
                        ->label('Bulan')
                        ->options([
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                        ])
                        ->default(fn () => $this->bulan)
                        ->required(),
                    Select::make('tahun')
                        ->label('Tahun')
                        ->options(function() {
                            $years = [];
                            for ($y = 2024; $y <= Carbon::now()->year + 2; $y++) {
                                $years[$y] = $y;
                            }
                            return $years;
                        })
                        ->default(fn () => $this->tahun)
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->bulan = (int) $data['bulan'];
                    $this->tahun = (int) $data['tahun'];
                    $this->syncPeriodeForCurrentLab();
                }),

            \Filament\Actions\ActionGroup::make([
                Action::make('export_pdf')
                    ->label('Download PDF')
                    ->icon('heroicon-o-document-text')
                    ->action(fn () => $this->downloadPdf()),
                Action::make('export_excel')
                    ->label('Download Excel')
                    ->icon('heroicon-o-table-cells')
                    ->action(fn () => $this->downloadExcel()),
                Action::make('export_csv')
                    ->label('Download CSV')
                    ->icon('heroicon-o-document')
                    ->action(fn () => $this->downloadCsv()),
            ])
            ->label('Download Data')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('success')
            ->button(),

            Action::make('ajukanLaporanPdf')
                ->label('Ajukan Laporan')
                ->icon('heroicon-o-document-plus')
                ->color('info')
                ->visible(false)
                ->form([
                    \Filament\Forms\Components\Textarea::make('catatan')
                        ->label('Catatan Tambahan')
                        ->placeholder('Masukkan catatan jika ada...')
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    return $this->generateLaporanPdf($data['catatan'] ?? null);
                }),

            Action::make('goToLaporan')
                ->label('Laporan Pengajuan')
                ->icon('heroicon-o-wrench-screwdriver')
                ->url(LaporanPerbaikanResource::getUrl())
                ->color('primary')
                ->visible(fn() => auth()->user()->hasRole('super_admin')),

            Action::make('sinkronisasiPage')
                ->label('Sinkronisasi Data PC')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Sinkronisasi Data PC dari Master')
                ->modalDescription('Proses ini akan menarik data PC dari master Inventaris sesuai laboratorium yang dipilih. PC yang belum ada pada rekap bulan ini akan ditambahkan otomatis dengan kondisi "Baik".')
                ->modalSubmitActionLabel('Ya, Sinkronisasi')
                ->visible(fn() => $this->periodeId !== null && ! $this->isPeriodCompletelyEmpty($this->periodeId) && !auth()->user()->hasRole('super_admin'))
                ->action(function () {
                    $this->doSinkronisasi();
                }),

            Action::make('hapusIsiHalaman')
                ->label('Hapus Halaman')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->visible(fn() => $this->periodeId !== null && !auth()->user()->hasRole('super_admin'))
                ->requiresConfirmation()
                ->action(function () {
                    $this->deleteAllDataInPeriod($this->periodeId);
                    Notification::make()->title('Berhasil')->body("Data dihapus.")->success()->send();
                    $this->refresh();
                }),
        ];
    }

    public function downloadPdf()
    {
        $periode = RekapInventarisPeriode::with('laboratorium')->findOrFail($this->periodeId);
        $pcs = RekapInventarisPc::where('rekap_inventaris_periode_id', $this->periodeId)
            ->with(['spec.details', 'notes'])
            ->orderByRaw('CAST(SUBSTRING(no_pc, 2) AS UNSIGNED)')
            ->get();

        $nonpcs = RekapInventarisNonPc::where('rekap_inventaris_periode_id', $this->periodeId)
            ->orderBy('nama_barang')
            ->get();

        $pdf = Pdf::loadView('pdf.rekap-inventaris', [
            'periode' => $periode,
            'pcs' => $pcs,
            'nonpcs' => $nonpcs,
            'title' => 'Rekap Inventaris - ' . $periode->laboratorium?->ruang
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(fn () => print($pdf->output()), "Rekap_{$periode->nama_periode}_{$periode->laboratorium?->ruang}.pdf");
    }

    public function generateLaporanPdf(?string $catatan)
    {
        $periode = RekapInventarisPeriode::with('laboratorium')->findOrFail($this->periodeId);
        $pcs = RekapInventarisPc::where('rekap_inventaris_periode_id', $this->periodeId)
            ->with(['spec.details'])
            ->orderByRaw('CAST(SUBSTRING(no_pc, 2) AS UNSIGNED)')
            ->get();

        $problematic_pcs = [];
        $summary_counts = [];

        foreach ($pcs as $pc) {
            if (!$pc->spec || !$pc->spec->details) continue;

            $broken_components = [];
            foreach ($pc->spec->details as $detail) {
                if (in_array(strtolower(trim($detail->kondisi)), ['rusak', 'kurang baik'])) {
                    $broken_components[] = $detail->komponen . ' (' . strtolower($detail->kondisi) . ')';
                    
                    $komp_name = $detail->komponen;
                    if (!isset($summary_counts[$komp_name])) {
                        $summary_counts[$komp_name] = 0;
                    }
                    $summary_counts[$komp_name]++;
                }
            }

            if (count($broken_components) > 0) {
                $problematic_pcs[] = "- PC " . $pc->no_pc . ": " . implode(', ', $broken_components);
            }
        }

        if (empty($problematic_pcs)) {
            Notification::make()->title('Info')->body('Tidak ada PC dengan komponen rusak/kurang baik di periode ini.')->info()->send();
            return;
        }

        $uraian = implode("\n", $problematic_pcs);
        
        $perbaikan_list = [];
        foreach ($summary_counts as $komp => $qty) {
            $perbaikan_list[] = "- Penggantian $komp ($qty unit)";
        }
        $tindakan_perbaikan = implode("\n", $perbaikan_list);

        $pdf = Pdf::loadView('pdf.laporan-pengajuan', [
            'nomor' => 'F.LAB.KOM-UDINUS-SH-03-02',
            'revisi' => '0',
            'tanggal_berlaku' => '19 September 2022',
            'ketidaksesuaian' => 'Kerusakan Hardware/Software Inventaris',
            'lab' => $periode->laboratorium?->ruang ?? 'Semua Laboratorium',
            'tanggal' => date('d F Y'),
            'uraian' => $uraian,
            'tindakan_langsung' => $catatan ?: 'Melaporkan kerusakan inventaris ke Super Admin.',
            'tindakan_perbaikan' => $tindakan_perbaikan,
            'pelapor' => auth()->user()->name,
            'jabatan_pelapor' => 'Laboran',
            'admin' => '............................',
            'jabatan_admin' => 'Super Admin',
        ])->setPaper('a4', 'portrait');

        $filename = "PTPP_" . str_replace(' ', '_', $periode->laboratorium?->ruang ?? 'Lab') . "_" . date('Ymd_Hi') . ".pdf";

        return response()->streamDownload(fn () => print($pdf->output()), $filename);
    }

    public function downloadExcel()
    {
        $periode = RekapInventarisPeriode::with('laboratorium')->findOrFail($this->periodeId);
        return Excel::download(new RekapInventarisExport($this->periodeId), "Rekap_{$periode->nama_periode}_{$periode->laboratorium?->ruang}.xlsx");
    }

    public function downloadCsv()
    {
        $periode = RekapInventarisPeriode::with('laboratorium')->findOrFail($this->periodeId);
        return Excel::download(new RekapInventarisExport($this->periodeId), "Rekap_{$periode->nama_periode}_{$periode->laboratorium?->ruang}.csv", \Maatwebsite\Excel\Excel::CSV);
    }

    public function refresh()
    {
        $this->redirect(static::getUrl(['periode_id' => $this->periodeId]));
    }

    public function buatPeriode()
    {
        $periode = $this->getOrCreatePeriode($this->bulan, $this->tahun, $this->laboratoriumId);
        $this->periodeId = $periode->id;
        Notification::make()->title('Berhasil')->body('Periode berhasil dibuat.')->success()->send();
        $this->redirect(static::getUrl(['lab' => $this->laboratoriumId, 'bulan' => $this->bulan, 'tahun' => $this->tahun]));
    }

    public function getPeriodeLabelProperty(): string
    {
        return $this->getNamaPeriode($this->bulan, $this->tahun);
    }

    protected function syncPeriodeForCurrentLab(): void
    {
        if (! $this->laboratoriumId) {
            $this->periodeId = null;

            return;
        }

        if (auth()->user()->hasRole('super_admin')) {
            $periode = RekapInventarisPeriode::where('laboratorium_id', $this->laboratoriumId)
                ->where('bulan', $this->bulan)
                ->where('tahun', $this->tahun)
                ->first();

            $this->periodeId = $periode?->id;

            return;
        }

        $periode = $this->getOrCreatePeriode($this->bulan, $this->tahun, $this->laboratoriumId);
        $this->periodeId = $periode->id;
    }

    protected function getOrCreatePeriode(int $bulan, int $tahun, ?int $labId): RekapInventarisPeriode
    {
        return RekapInventarisPeriode::firstOrCreate(
            ['bulan' => $bulan, 'tahun' => $tahun, 'laboratorium_id' => $labId],
            ['nama_periode' => $this->getNamaPeriode($bulan, $tahun)]
        );
    }

    protected function getPreviousPeriod(RekapInventarisPeriode $currentPeriod): ?RekapInventarisPeriode
    {
        return RekapInventarisPeriode::query()
            ->where('laboratorium_id', $currentPeriod->laboratorium_id)
            ->where(function ($query) use ($currentPeriod) {
                $query->where('tahun', '<', $currentPeriod->tahun)
                    ->orWhere(fn ($q) => $q->where('tahun', $currentPeriod->tahun)->where('bulan', '<', $currentPeriod->bulan));
            })
            ->orderByDesc('tahun')->orderByDesc('bulan')->first();
    }

    public function isPeriodCompletelyEmpty(int $periodeId): bool
    {
        return RekapInventarisPc::where('rekap_inventaris_periode_id', $periodeId)->count() === 0
            && RekapInventarisSpec::where('rekap_inventaris_periode_id', $periodeId)->count() === 0
            && RekapInventarisNonPc::where('rekap_inventaris_periode_id', $periodeId)->count() === 0;
    }

    protected function copyAllDataFromPeriod(int $fromPeriodeId, int $toPeriodeId): void
    {
        DB::transaction(function () use ($fromPeriodeId, $toPeriodeId) {
            $specMap = [];
            $oldSpecs = RekapInventarisSpec::where('rekap_inventaris_periode_id', $fromPeriodeId)->with('details')->get();
            foreach ($oldSpecs as $oldSpec) {
                $newSpec = RekapInventarisSpec::create([
                    'rekap_inventaris_periode_id' => $toPeriodeId,
                    'kode_spek' => $oldSpec->kode_spek,
                    'urutan_kode' => $oldSpec->urutan_kode,
                    'fingerprint' => md5($toPeriodeId . '|' . $oldSpec->fingerprint),
                    'kondisi_pc' => $oldSpec->kondisi_pc,
                ]);
                foreach ($oldSpec->details as $detail) {
                    RekapInventarisSpecDetail::create([
                        'rekap_inventaris_spec_id' => $newSpec->id,
                        'komponen' => $detail->komponen,
                        'detail' => $detail->detail,
                        'kondisi' => $detail->kondisi,
                        'catatan_kondisi' => $detail->catatan_kondisi,
                        'urutan' => $detail->urutan,
                    ]);
                }
                $specMap[$oldSpec->id] = $newSpec->id;
            }
            $oldPcs = RekapInventarisPc::where('rekap_inventaris_periode_id', $fromPeriodeId)->get();
            foreach ($oldPcs as $oldPc) {
                RekapInventarisPc::create([
                    'rekap_inventaris_periode_id' => $toPeriodeId,
                    'rekap_inventaris_spec_id' => $specMap[$oldPc->rekap_inventaris_spec_id] ?? null,
                    'no_pc' => $oldPc->no_pc,
                    'lokasi' => $oldPc->lokasi,
                    'kondisi' => $oldPc->kondisi,
                ]);
            }

            // Copy Non-PC data
            $oldNonPcs = RekapInventarisNonPc::where('rekap_inventaris_periode_id', $fromPeriodeId)->get();
            foreach ($oldNonPcs as $oldNonPc) {
                RekapInventarisNonPc::create([
                    'rekap_inventaris_periode_id' => $toPeriodeId,
                    'nama_barang' => $oldNonPc->nama_barang,
                    'merk_model' => $oldNonPc->merk_model,
                    'jumlah' => $oldNonPc->jumlah,
                    'kondisi' => $oldNonPc->kondisi,
                    'keterangan' => $oldNonPc->keterangan,
                ]);
            }
        });
    }

    protected function deleteAllDataInPeriod(int $periodeId): void
    {
        DB::transaction(function () use ($periodeId) {
            RekapInventarisPc::where('rekap_inventaris_periode_id', $periodeId)->delete();
            RekapInventarisSpec::where('rekap_inventaris_periode_id', $periodeId)->delete();
            RekapInventarisNonPc::where('rekap_inventaris_periode_id', $periodeId)->delete();
        });
    }

    protected function getNamaPeriode(int $bulan, int $tahun): string
    {
        $namaBulan = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
        return ($namaBulan[$bulan] ?? 'Bulan Tidak Valid') . ' ' . $tahun;
    }

    public function copyBulanSebelumnyaAction(): Action
    {
        return Action::make('copyBulanSebelumnya')
            ->label('Copy Bulan Lalu')
            ->icon('heroicon-o-document-duplicate')
            ->color('warning')
            ->visible(fn() => !auth()->user()->hasRole('super_admin'))
            ->requiresConfirmation()
            ->action(function () {
                $currentPeriod = RekapInventarisPeriode::findOrFail($this->periodeId);
                if (! $this->isPeriodCompletelyEmpty($currentPeriod->id)) {
                    Notification::make()->title('Gagal')->body('Periode ini sudah memiliki data.')->danger()->send();
                    return;
                }
                $previousPeriod = $this->getPreviousPeriod($currentPeriod);
                if (!$previousPeriod) {
                    Notification::make()->title('Gagal')->body('Periode sebelumnya tidak ditemukan.')->warning()->send();
                    return;
                }
                $this->copyAllDataFromPeriod($previousPeriod->id, $currentPeriod->id);
                Notification::make()->title('Berhasil')->body("Data disalin dari {$previousPeriod->nama_periode}.")->success()->send();
                $this->refresh();
            });
    }

    public function sinkronisasiAction(): Action
    {
        return Action::make('sinkronisasi')
            ->label('Sinkronisasi Data PC')
            ->icon('heroicon-o-arrow-path')
            ->color('success')
            ->visible(fn() => !auth()->user()->hasRole('super_admin'))
            ->requiresConfirmation()
            ->modalHeading('Sinkronisasi Data PC dari Master')
            ->modalDescription('Proses ini akan menarik data PC dari master Inventaris sesuai laboratorium yang dipilih. PC yang belum ada pada rekap bulan ini akan ditambahkan otomatis dengan kondisi "Baik".')
            ->modalSubmitActionLabel('Ya, Sinkronisasi')
            ->action(function () {
                $this->doSinkronisasi();
            });
    }

    protected function doSinkronisasi(): void
    {
        $labId = $this->laboratoriumId;
        if (!$labId) {
            $periodeInfo = RekapInventarisPeriode::find($this->periodeId);
            $labId = $periodeInfo?->laboratorium_id;
        }
        
        if (!$labId) {
            Notification::make()->title('Gagal')->body('Laboratorium tidak ditemukan.')->danger()->send();
            return;
        }
        
        $masterPcs = \App\Models\Inventory::whereNull('inventoriable_type')
            ->where('lokasi_id', $labId)
            ->where('bulan', $this->bulan)
            ->where('tahun', $this->tahun)
            ->whereNotNull('no_pc')
            ->with('pcComponents')
            ->get();
            
        if ($masterPcs->isEmpty()) {
            Notification::make()->title('Info')->body('Tidak ada data PC di master Inventaris untuk laboratorium ini.')->info()->send();
            return;
        }

        $service = resolve(\App\Services\RekapInventarisSpecService::class);
        $countAdded = 0;

        foreach ($masterPcs as $master) {
            $exists = RekapInventarisPc::where('rekap_inventaris_periode_id', $this->periodeId)
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

                RekapInventarisPc::create([
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
            Notification::make()->title('Berhasil')->body("Tersinkronisasi {$countAdded} PC baru.")->success()->send();
            $this->refresh();
        } else {
            Notification::make()->title('Info')->body('Semua PC sudah tersinkronisasi, tidak ada data baru.')->info()->send();
        }
    }
}