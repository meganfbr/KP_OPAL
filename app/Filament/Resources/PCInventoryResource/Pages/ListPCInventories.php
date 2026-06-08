<?php

namespace App\Filament\Resources\PCInventoryResource\Pages;

use App\Filament\Resources\PCInventoryResource;
use App\Services\InventoryPcPeriodService;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;

class ListPCInventories extends ListRecords
{
    protected static string $resource = PCInventoryResource::class;

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

            Actions\CreateAction::make()
                ->label('Tambah PC')
                ->visible(fn (): bool => PCInventoryResource::canCreate()),
        ];
    }
}