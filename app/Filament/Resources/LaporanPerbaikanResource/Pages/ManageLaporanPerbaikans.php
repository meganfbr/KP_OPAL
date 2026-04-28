<?php

namespace App\Filament\Resources\LaporanPerbaikanResource\Pages;

use App\Filament\Resources\LaporanPerbaikanResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageLaporanPerbaikans extends ManageRecords
{
    protected static string $resource = LaporanPerbaikanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getMaxContentWidth(): string
    {
        return 'full';
    }
}
