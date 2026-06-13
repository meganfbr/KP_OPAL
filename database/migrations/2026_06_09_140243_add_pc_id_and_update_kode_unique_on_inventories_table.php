<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inventories')) {
            return;
        }

        /*
         * Hapus unique constraint global pada kode_inventaris agar bisa
         * menampung angka berulang antar bulan (001 di bulan Mei, 001 di bulan Juni)
         */
        try {
            Schema::table('inventories', function (Blueprint $table) {
                // mysql will not throw if we use catching.
                $table->dropUnique('inventories_kode_inventaris_unique');
            });
        } catch (Throwable $e) {}
        try {
            Schema::table('inventories', function (Blueprint $table) {
                $table->dropUnique(['kode_inventaris']);
            });
        } catch (Throwable $e) {}

        /*
         * Hapus unique lama pada kode_unique karena sekarang kode_unique
         * boleh kosong dan bisa diisi manual oleh user sebagai BIUM.
         */
        try {
            Schema::table('inventories', function (Blueprint $table) {
                $table->dropUnique(['kode_unique']);
            });
        } catch (Throwable $e) {}
        try {
            Schema::table('inventories', function (Blueprint $table) {
                $table->dropUnique('inventories_period_kode_unique_unique');
            });
        } catch (Throwable $e) {}

        /*
         * Pindahkan kode_unique lama yang masih berupa angka
         * ke kode_inventaris. Contoh kode_unique 001 menjadi kode_inventaris 001.
         */
        DB::table('inventories')
            ->whereNull('inventoriable_type')
            ->whereNotNull('kode_unique')
            ->orderBy('id')
            ->chunkById(100, function ($inventories) {
                foreach ($inventories as $inventory) {
                    $kodeUnique = (string) $inventory->kode_unique;

                    if (preg_match('/^[0-9]+$/', $kodeUnique)) {
                        DB::table('inventories')
                            ->where('id', $inventory->id)
                            ->update([
                                'kode_inventaris' => str_pad($kodeUnique, 3, '0', STR_PAD_LEFT),
                                'kode_unique' => null,
                            ]);
                    }
                }
            });

        /*
         * Jika masih ada data PC yang kode_inventaris-nya kosong,
         * isi otomatis berdasarkan urutan id per periode.
         */
        $periods = DB::table('inventories')
            ->whereNull('inventoriable_type')
            ->whereNotNull('bulan')
            ->whereNotNull('tahun')
            ->select('bulan', 'tahun')
            ->groupBy('bulan', 'tahun')
            ->get();

        foreach ($periods as $period) {
            $inventories = DB::table('inventories')
                ->whereNull('inventoriable_type')
                ->where('bulan', $period->bulan)
                ->where('tahun', $period->tahun)
                ->orderBy('id')
                ->get();

            $number = 1;

            foreach ($inventories as $inventory) {
                if (empty($inventory->kode_inventaris)) {
                    DB::table('inventories')
                        ->where('id', $inventory->id)
                        ->update([
                            'kode_inventaris' => str_pad((string) $number, 3, '0', STR_PAD_LEFT),
                        ]);
                } else {
                    $number = (int) $inventory->kode_inventaris;
                }
                $number++;
            }
        }

        try {
            Schema::table('inventories', function (Blueprint $table) {
                $table->unique(['bulan', 'tahun', 'kode_inventaris', 'lokasi_id'], 'inventories_period_kode_inv_unique');
            });
        } catch (Throwable $e) {}
    }

    public function down(): void
    {
        // No down migration as we can't reliably reconstruct the global unique index
    }
};