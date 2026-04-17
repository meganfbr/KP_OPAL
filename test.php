<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

DB::transaction(function () {
    // 1. Pastikan klafisikasi lab ada
    $klasifikasiId = DB::table('klasifikasi_labs')->insertGetId([
        'kode_kategori' => 'REG',
        'nama_kategori' => 'Reguler',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // 2. Buat laboratorium
    $labs = ['D2A', 'D2B', 'D2C', 'D2D', 'D2E', 'D2F', 'D2G', 'D2H', 'D2I', 'D2J', 'D2K', 'D3L', 'D3M', 'D3N'];
    foreach ($labs as $lab) {
        if (!DB::table('laboratoria')->where('ruang', 'LAB ' . $lab)->exists()) {
            DB::table('laboratoria')->insert([
                'ruang' => 'LAB ' . $lab,
                'kategori_id' => $klasifikasiId,
                'kapasitas' => 40,
                'pc_siap' => 40,
                'pc_backup' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
});

echo "Restore script completed.\n";
