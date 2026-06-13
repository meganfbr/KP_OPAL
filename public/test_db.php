<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

header('Content-Type: application/json');

// create a fixed width table to dump
$invs = \App\Models\Inventory::whereNull('inventoriable_type')->limit(5)->select('id', 'no_pc', 'kode_inventaris', 'kode_unique')->get();
printf("%-5s %-10s %-20s %-20s\n", "ID", "NO PC", "KODE INVENTARIS", "KODE UNIQUE");
foreach($invs as $inv) {
    printf("%-5s %-10s %-20s %-20s\n", $inv->id, $inv->no_pc, $inv->kode_inventaris ?? 'NULL', $inv->kode_unique ?? 'NULL');
}
