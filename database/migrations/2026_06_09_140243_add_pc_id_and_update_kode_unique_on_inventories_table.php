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

        Schema::table('inventories', function (Blueprint $table) {
            if (! Schema::hasColumn('inventories', 'pc_id')) {
                $table->unsignedInteger('pc_id')->nullable()->after('id');
            }
        });

        /*
         * Hapus unique lama pada kode_unique karena sekarang kode_unique
         * boleh kosong dan bisa diisi manual oleh user.
         */
        try {
            Schema::table('inventories', function (Blueprint $table) {
                $table->dropUnique(['kode_unique']);
            });
        } catch (Throwable $e) {
            //
        }

        try {
            Schema::table('inventories', function (Blueprint $table) {
                $table->dropUnique('inventories_period_kode_unique_unique');
            });
        } catch (Throwable $e) {
            //
        }

        /*
         * Pindahkan kode_unique lama yang masih berupa angka
         * ke pc_id. Contoh kode_unique 001 menjadi pc_id 1.
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
                                'pc_id' => (int) $kodeUnique,
                                'kode_unique' => null,
                            ]);
                    }
                }
            });

        /*
         * Jika masih ada data PC yang pc_id-nya kosong,
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
                ->orderBy('pc_id')
                ->orderBy('id')
                ->get();

            $number = 1;

            foreach ($inventories as $inventory) {
                DB::table('inventories')
                    ->where('id', $inventory->id)
                    ->update([
                        'pc_id' => $number,
                    ]);

                $number++;
            }
        }

        try {
            Schema::table('inventories', function (Blueprint $table) {
                $table->unique(['bulan', 'tahun', 'pc_id'], 'inventories_period_pc_id_unique');
            });
        } catch (Throwable $e) {
            //
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('inventories')) {
            return;
        }

        try {
            Schema::table('inventories', function (Blueprint $table) {
                $table->dropUnique('inventories_period_pc_id_unique');
            });
        } catch (Throwable $e) {
            //
        }

        Schema::table('inventories', function (Blueprint $table) {
            if (Schema::hasColumn('inventories', 'pc_id')) {
                $table->dropColumn('pc_id');
            }
        });
    }
};