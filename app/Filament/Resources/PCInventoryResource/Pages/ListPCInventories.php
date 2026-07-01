<?php

namespace App\Filament\Resources\PCInventoryResource\Pages;

use App\Filament\Resources\PCInventoryResource;
use App\Models\Inventory;
use App\Models\InventoryPcComponent;
use App\Models\InventoryPcDetail;
use App\Services\InventoryPcPeriodService;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;

class ListPCInventories extends ListRecords
{
    protected static string $resource = PCInventoryResource::class;

    public function getSubheading(): ?string
    {
        $period = PCInventoryResource::getActivePeriod();
        $bulanStr = PCInventoryResource::monthOptions()[$period['bulan']] ?? '';
        
        return "Periode Aktif: {$bulanStr} {$period['tahun']}";
    }

    public function mount(): void
    {
        parent::mount();

        $period = PCInventoryResource::getActivePeriod();

        if ($period['bulan'] === (int) now()->month && $period['tahun'] === (int) now()->year) {
            InventoryPcPeriodService::ensureCurrentPeriodExists();
        } else {
            InventoryPcPeriodService::ensurePeriodExists($period['bulan'], $period['tahun']);
        }
    }

    protected function getHeaderActions(): array
    {
        $period = PCInventoryResource::getActivePeriod();

        return [
            Actions\Action::make('pilih_periode')
                ->label('Pilih Periode')
                ->icon('heroicon-o-calendar-days')
                ->form([
                    Select::make('bulan')
                        ->label('Bulan')
                        ->options(PCInventoryResource::monthOptions())
                        ->default($period['bulan'])
                        ->required(),

                    Select::make('tahun')
                        ->label('Tahun')
                        ->options(PCInventoryResource::yearOptions())
                        ->default($period['tahun'])
                        ->required(),
                ])
                ->action(function (array $data) {
                    return redirect(PCInventoryResource::getUrl('index', [
                        'bulan' => $data['bulan'],
                        'tahun' => $data['tahun'],
                    ]));
                }),

            Actions\Action::make('copyBulanSebelumnya')
                ->label('Copy Bulan Lalu')
                ->icon('heroicon-o-document-duplicate')
                ->color('warning')
                ->visible(fn (): bool => auth()->user() && auth()->user()->hasAnyRole(['super_admin', 'Super Admin']))
                ->requiresConfirmation()
                ->action(function () use ($period) {
                    $activeBulan = (int) $period['bulan'];
                    $activeTahun = (int) $period['tahun'];

                    $existingDataCount = Inventory::whereNull('inventoriable_type')
                        ->where('bulan', $activeBulan)
                        ->where('tahun', $activeTahun)
                        ->count();

                    if ($existingDataCount > 0) {
                        Notification::make()
                            ->title('Gagal')
                            ->body('Data Inventaris PC untuk periode ini sudah tersedia.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $previousDate = Carbon::create($activeTahun, $activeBulan, 1)->subMonth();
                    $prevBulan = $previousDate->month;
                    $prevTahun = $previousDate->year;

                    $previousData = Inventory::whereNull('inventoriable_type')
                        ->where('bulan', $prevBulan)
                        ->where('tahun', $prevTahun)
                        ->with(['pcDetail', 'pcComponents'])
                        ->get();

                    if ($previousData->isEmpty()) {
                        Notification::make()
                            ->title('Gagal')
                            ->body("Data bulan lalu ({$prevBulan}-{$prevTahun}) tidak ditemukan.")
                            ->warning()
                            ->send();
                        return;
                    }

                    DB::transaction(function () use ($previousData, $activeBulan, $activeTahun) {
                        Inventory::withoutEvents(function () use ($previousData, $activeBulan, $activeTahun) {
                            foreach ($previousData as $oldPc) {
                                $newPc = Inventory::create([
                                    'kode_inventaris' => $oldPc->kode_inventaris,
                                    'no_pc' => $oldPc->no_pc,
                                    'kode_unique' => $oldPc->kode_unique,
                                    'asal_id' => $oldPc->asal_id,
                                    'lokasi_id' => $oldPc->lokasi_id,
                                    'petugas_id' => $oldPc->petugas_id,
                                    'laboratorium_id' => $oldPc->laboratorium_id,
                                    'nama_barang' => $oldPc->nama_barang,
                                    'kondisi' => $oldPc->kondisi,
                                    'status' => $oldPc->status,
                                    'tanggal_pengadaan' => $oldPc->tanggal_pengadaan,
                                    'bulan' => $activeBulan,
                                    'tahun' => $activeTahun,
                                    'inventoriable_type' => null,
                                    'inventoriable_id' => null,
                                ]);

                                if ($oldPc->pcDetail) {
                                    InventoryPcDetail::create([
                                        'inventory_id' => $newPc->id,
                                        'posisi' => $oldPc->pcDetail->posisi,
                                    ]);
                                }

                                foreach ($oldPc->pcComponents as $oldComp) {
                                    InventoryPcComponent::create([
                                        'inventory_id' => $newPc->id,
                                        'komponen' => $oldComp->komponen,
                                        'motherboard_id' => $oldComp->motherboard_id,
                                        'processor_id' => $oldComp->processor_id,
                                        'penyimpanan_id' => $oldComp->penyimpanan_id,
                                        'vga_id' => $oldComp->vga_id,
                                        'ram_id' => $oldComp->ram_id,
                                        'dvd_id' => $oldComp->dvd_id,
                                        'keyboard_id' => $oldComp->keyboard_id,
                                        'mouse_id' => $oldComp->mouse_id,
                                        'monitor_id' => $oldComp->monitor_id,
                                        'kondisi' => $oldComp->kondisi,
                                        'keterangan' => $oldComp->keterangan,
                                        'urutan' => $oldComp->urutan,
                                    ]);
                                }
                            }
                        });
                    });

                    Notification::make()
                        ->title('Berhasil')
                        ->body('Data PC bulan lalu berhasil disalin ke periode aktif.')
                        ->success()
                        ->send();

                    return redirect(PCInventoryResource::getUrl('index', [
                        'bulan' => $activeBulan,
                        'tahun' => $activeTahun,
                    ]));
                }),

            Actions\CreateAction::make()
                ->label('Tambah PC')
                ->visible(fn (): bool => PCInventoryResource::canCreate()),
        ];
    }
}