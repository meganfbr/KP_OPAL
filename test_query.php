<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$lab = \App\Models\Laboratorium::where('ruang', 'like', '%D2A%')->first();
$inv = \App\Models\Inventory::where('no_pc', 'A01')->where('lokasi_id', $lab?->id)->first();
echo "INVENTORY DB: " . ($inv->kode_unique ?? 'NULL') . "\n";
echo "INVENTORY PC_ID: " . ($inv->pc_id ?? 'NULL') . "\n";

$rekap = \App\Models\RekapInventarisPc::where('no_pc', 'A01')->first();
echo "REKAP DB ID: " . ($rekap->id ?? 'NULL') . "\n";
echo "REKAP DB KOloms: " . json_encode(array_keys($rekap ? $rekap->toArray() : [])) . "\n";

// check formatting in PCInventoryResource using InventoryPcIdService
echo "Format PC_ID 2: " . \App\Services\InventoryPcIdService::format(2) . "\n";
echo "Format PC_ID 2 with Lab D2A: " . \App\Services\InventoryPcIdService::format(2, $lab->id) . "\n";
