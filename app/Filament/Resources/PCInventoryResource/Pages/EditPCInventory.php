<?php

namespace App\Filament\Resources\PCInventoryResource\Pages;

use App\Filament\Resources\PCInventoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditPCInventory extends EditRecord
{
    protected static string $resource = PCInventoryResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['components'] = PCInventoryResource::getComponentFormData($this->record);

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $components = $data['components'] ?? [];

        unset($data['components']);

        /*
         * Asal, Lokasi, dan NoPC tidak diedit manual dari form edit.
         * Perubahan lokasi hanya lewat fitur Select / Pindahkan.
         */
        unset($data['asal_id']);
        unset($data['lokasi_id']);
        unset($data['laboratorium_id']);
        unset($data['no_pc']);

        $record->update($data);

        PCInventoryResource::syncPcComponents($record, $components);

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}