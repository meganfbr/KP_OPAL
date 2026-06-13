<?php

namespace App\Filament\Resources\PCInventoryResource\Pages;

use App\Filament\Resources\PCInventoryResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use App\Services\InventoryPcIdService;

class CreatePCInventory extends CreateRecord
{
    protected static string $resource = PCInventoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $period = PCInventoryResource::getActivePeriod();

        /*
         * Bulan dan tahun tidak diisi dari form,
         * tapi mengikuti periode tabel yang sedang aktif.
         */
        $data['bulan'] = $period['bulan'];
        $data['tahun'] = $period['tahun'];
        
        // Auto-generate sequence untuk ID (menggunakan kode_inventaris)
        $nextId = InventoryPcIdService::generateNextId($period['bulan'], $period['tahun']);
        $data['kode_inventaris'] = InventoryPcIdService::format($nextId);

        /*
         * PC baru belum diplot ke mana pun.
         * Maka asal, lokasi, dan NoPC tetap null.
         * Di tampilan akan muncul "-".
         */
        $data['asal_id'] = null;
        $data['lokasi_id'] = null;
        $data['laboratorium_id'] = null;
        $data['no_pc'] = null;

        /*
         * Konsep baru tidak memakai pc_details sebagai detail spesifikasi.
         */
        $data['inventoriable_id'] = null;
        $data['inventoriable_type'] = null;

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $components = $data['components'] ?? [];

        unset($data['components']);

        $record = static::getModel()::create($data);

        PCInventoryResource::syncPcComponents($record, $components);

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}